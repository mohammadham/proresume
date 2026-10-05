<?php
/**
 * Mock-band harness for the web path `POST /enamad/verify`
 * (EnamadController@verify) - project tooling.
 *
 * Companion to tests/enamad_verify_harness.php, which covers the artisan
 * command. This one drives the real HTTP kernel: every scenario builds a real
 * Illuminate\Http\Request and runs it through the full middleware stack,
 * route lookup and controller, then asserts on the JSON response, the
 * BasicSetting row and the enamad log channel.
 *
 * A child process runs a stdlib TCP mock server on 127.0.0.1:8932 only;
 * api.enamad.ir is never contacted. Scenario W7 stubs the Http facade
 * against the real endpoint URL so the URL is proven reachable-by-URL
 * without a packet leaving the machine.
 *
 * Scenarios:
 *   W0 unconfigured        -> 400, row untouched
 *   W1 HTTP 500            -> 400, upstream message, error logged, row safe
 *   W2 200 active          -> 200 success, enamad_status=1, expire persisted
 *   W3 200 suspended       -> 200 success, enamad_status=0
 *   W4 200 + HTML body     -> 502 malformed, row + cache untouched
 *   W5 200 + JSON w/o status (WITH expire_date) -> 502, no partial writes
 *   W6 200 + empty body    -> 502 malformed, row untouched
 *   W7 network guard       -> real api.enamad.ir URL stubbed via Http::fake
 *   W9 manual route        -> /enamad/verify/manual shares the same guard
 *   W9b manual success     -> 200 success on a good payload
 *   W10 no-credentials regression: status=1 survives W4/W5/W6 untouched
 *   W11 timeout (1s cfg)   -> 500, cURL 28 via the exception catch, row safe
 *
 * Usage: php tests/enamad_web_harness.php
 * Exit code 0 = all scenarios passed.
 */

if (in_array('--server', $argv ?? [], true)) {
    // ---------------------------------------------------------------- child
    $srv = stream_socket_server('tcp://127.0.0.1:8932', $errno, $errstr);
    if (!$srv) {
        fwrite(STDERR, "mock server failed: $errstr\n");
        exit(2);
    }
    $deadline = microtime(true) + 180; // self-limit; parent terminates earlier
    while (microtime(true) < $deadline) {
        $r = [$srv];
        $w = null;
        $x = null;
        if (stream_select($r, $w, $x, 0, 200000) === false || !$r) {
            continue;
        }
        $conn = stream_socket_accept($srv, 0);
        if (!$conn) {
            continue;
        }
        stream_set_timeout($conn, 8);

        $req = '';
        while (strpos($req, "\r\n\r\n") === false && !feof($conn)) {
            $chunk = fread($conn, 8192);
            if ($chunk === false || $chunk === '') {
                break;
            }
            $req .= $chunk;
        }
        // split headers from body: the body may already have arrived in the
        // same packets as the headers, so carry the leftover into $body and
        // only read the remaining Content-Length bytes
        $body = '';
        if (preg_match('/\r\n\r\n/', $req, $mm, PREG_OFFSET_CAPTURE)) {
            $splitAt = $mm[0][1] + 4;
            $body = substr($req, $splitAt);
            $req = substr($req, 0, $splitAt);
        }
        if (preg_match('/Content-Length:\s*(\d+)/i', $req, $m)) {
            $clen = (int) $m[1];
            while (strlen($body) < $clen && !feof($conn)) {
                $chunk = fread($conn, 8192);
                if ($chunk === false) {
                    break;
                }
                $body .= $chunk;
            }
        }

        @file_put_contents(sys_get_temp_dir() . '/mock_web_requests.log', $req . $body . "\n\n", FILE_APPEND);

        $mode = trim((string) @file_get_contents(sys_get_temp_dir() . '/mock_web_mode.txt'));

        if ($mode === 'timeout') {
            // hold the connection open silently past the client timeout,
            // then close without ever answering -> real cURL error 28
            usleep(3000000);
            fclose($conn);
            continue;
        }

        $payloads = [
            'http500'     => [500, 'Internal Server Error', '{"message":"mock upstream error"}'],
            'ok_active'   => [200, 'OK', '{"status":"active","expire_date":"2030-01-01"}'],
            'ok_suspended' => [200, 'OK', '{"status":"suspended","expire_date":"2026-01-01"}'],
            // malformed family: HTTP 200 but unusable payloads
            'bad_json'    => [200, 'OK', '<html><body>Service Unavailable</body></html>'],
            'no_status'   => [200, 'OK', '{"message":"ok","expire_date":"2030-01-01"}'],
            'empty_body'  => [200, 'OK', ''],
        ];
        if (!isset($payloads[$mode])) {
            fclose($conn);
            continue;
        }
        [$status, $reason, $payload] = $payloads[$mode];
        $resp = "HTTP/1.1 $status $reason\r\nContent-Type: application/json\r\n"
            . 'Content-Length: ' . strlen($payload) . "\r\nConnection: close\r\n\r\n" . $payload;
        fwrite($conn, $resp);
        fclose($conn);
    }
    exit(0);
}

// ------------------------------------------------------------------- parent
error_reporting(E_ALL);

use App\Models\BasicSetting;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Bootstrap before the first DB read. The routing providers need a bound
// 'request' instance at registration time (UrlGenerator's constructor takes
// it), and handle() would only bind one later - so bind a throwaway first.
$app->instance('request', Request::create('http://localhost/', 'GET'));
$kernel->bootstrap();

@unlink(sys_get_temp_dir() . '/mock_web_mode.txt');
@unlink(sys_get_temp_dir() . '/mock_web_requests.log');

// array command form: handles "D:\Program Files\..." spaces on Windows safely
$proc = proc_open(
    [PHP_BINARY, __FILE__, '--server'],
    [1 => ['file', sys_get_temp_dir() . '/mock_web_server.log', 'w'], 2 => ['file', sys_get_temp_dir() . '/mock_web_server.log', 'a']],
    $pipes
);
if (!is_resource($proc)) {
    fwrite(STDERR, "cannot start mock server\n");
    exit(2);
}
$up = false;
for ($i = 0; $i < 40; $i++) {
    $probe = @fsockopen('127.0.0.1', 8932, $pe, $ps, 0.2);
    if ($probe) {
        fclose($probe);
        $up = true;
        break;
    }
    usleep(50000);
}
if (!$up) {
    proc_terminate($proc);
    fwrite(STDERR, "mock server never came up\n");
    exit(2);
}

// snapshot the single admin row; we only ever update() it, never insert
$row = BasicSetting::query()->first();
if (!$row) {
    proc_terminate($proc);
    fwrite(STDERR, "no basic_settings row found\n");
    exit(2);
}
$snap = $row->only(['enamad_status', 'enamad_code', 'enamad_site_id', 'enamad_secret_key', 'enamad_expire_date']);
$realBaseUrl = config('enamad.api.base_url');
$results = [];
$lastMessage = '';

// Register the restore on shutdown as well as in the finally block below.
// A fatal error, a Ctrl+C or a hard kill can skip finally; without this the
// single admin row would be left holding the harness' fake credentials. The
// guard makes the restore idempotent so the finally block can still call it.
$restored = false;
$restoreRow = function () use (&$restored, $snap) {
    if ($restored) {
        return;
    }
    $restored = true;
    try {
        $r = BasicSetting::query()->first();
        if ($r) {
            $r->update($snap);
        }
        Cache::forget('enamad_status');
        Cache::forget('enamad_verify');
    } catch (\Throwable $e) {
        fwrite(STDERR, "FATAL: could not restore basic_settings: " . $e->getMessage() . "\n");
    }
};
register_shutdown_function($restoreRow);

/**
 * Dispatch a real request through the HTTP kernel.
 * Returns [statusCode, decodedJsonArray, rawBody].
 */
function postVerify(string $uri = '/enamad/verify'): array
{
    global $kernel, $app, $lastMessage;

    $request = Request::create('http://localhost' . $uri, 'POST', [], [], [], [
        'HTTP_HOST' => 'localhost',       // must satisfy Route::domain(env('WEBSITE_HOST'))
        'HTTP_ACCEPT' => 'application/json',
    ]);
    // routing resolves 'request' out of the container; bind the same instance
    $app->instance('request', $request);

    // A real request gets a fresh process, but in-process dispatch reuses the
    // same Route objects - and Route::$controller caches its instance. The
    // controller snapshots config('enamad') in its constructor, so a stale
    // instance would keep the base_url of whichever scenario ran first.
    // Flushing forces a re-resolve, matching per-request production behaviour.
    foreach ($app['router']->getRoutes() as $route) {
        if (method_exists($route, 'flushController')) {
            $route->flushController();
        }
    }

    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);

    $raw = (string) $response->getContent();
    $json = json_decode($raw, true);

    $lastMessage = is_array($json) ? (string) ($json['message'] ?? '') : substr($raw, 0, 200);

    return [$response->getStatusCode(), is_array($json) ? $json : [], $raw];
}

function refreshRow(): BasicSetting
{
    return BasicSetting::query()->first();
}

function logTail(): string
{
    // the 'enamad' channel uses the daily driver -> enamad-YYYY-MM-DD.log
    $files = glob(storage_path('logs/enamad-*.log')) ?: [];
    if (!$files) {
        return '';
    }
    usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));
    return (string) @file_get_contents($files[0]);
}

/** Each closure returns [ok(bool), detail(string)]. */
function scenario(string $name, callable $fn): void
{
    global $results, $lastMessage;
    $lastMessage = '';
    $t0 = microtime(true);
    try {
        [$ok, $detail] = $fn();
        // on failure always echo what the endpoint actually answered: a bare
        // status code is not enough to tell a rejected guard from a boot error
        $extra = (!$ok && $lastMessage !== '') ? ' | msg=' . $lastMessage : '';
        $results[] = sprintf('%s %s (%s%s, %.2fs)', $ok ? 'PASS' : 'FAIL', $name, $detail, $extra, microtime(true) - $t0);
    } catch (Throwable $e) {
        $results[] = "FAIL $name (harness exception: " . $e->getMessage() . ')';
    }
}

/** Arm credentials + status=1, expire_date=NULL, arm the cache, dispatch. */
function withRow(string $uri = '/enamad/verify'): array
{
    refreshRow()->update([
        'enamad_site_id' => 'SITE-TEST',
        'enamad_secret_key' => 'SECRET-TEST',
        'enamad_status' => 1,
        'enamad_expire_date' => null,
    ]);
    Cache::put('enamad_status', 'stale', 60);
    [$code, $json, $raw] = postVerify($uri);
    return [$code, $json, $raw, refreshRow(), Cache::get('enamad_status')];
}

try {
    // W0: unconfigured credentials ----------------------------------------
    scenario('W0 unconfigured', function () {
        refreshRow()->update(['enamad_site_id' => null, 'enamad_secret_key' => null]);
        [$code, $json] = postVerify();
        $row = refreshRow();
        $ok = $code === 400 && ($json['success'] ?? null) === false
            && strpos($json['message'] ?? '', 'تنظیم نشده') !== false
            && $row->enamad_status != 0;
        return [$ok, "http=$code, rowUntouched=" . var_export($row->enamad_status != 0, true)];
    });

    // shared config: point the controller at the loopback mock server. The
    // controller snapshots config('enamad') in its constructor, which the
    // router instantiates per request - so setting this before each dispatch
    // is enough and needs no .env edit or config:clear.
    config([
        'enamad.api.base_url' => 'http://127.0.0.1:8932',
        'enamad.api.timeout' => 5,
    ]);

    // W1: HTTP 500 -> non-successful branch -------------------------------
    scenario('W1 http500', function () {
        file_put_contents(sys_get_temp_dir() . '/mock_web_mode.txt', 'http500');
        [$code, $json, , $row, $cached] = withRow();
        $logged = strpos(logTail(), 'Enamad verification failed') !== false;
        $ok = $code === 400 && ($json['success'] ?? null) === false
            && ($json['message'] ?? '') === 'mock upstream error'
            && $row->enamad_status == 1 && $row->enamad_expire_date === null
            && $logged && $cached === 'stale';
        return [$ok, "http=$code, rowUntouched=" . var_export($row->enamad_status == 1, true)
            . ", cacheUntouched=" . var_export($cached === 'stale', true)];
    });

    // W2: 200 active -> 200 success, status 1, expire date persisted --------
    scenario('W2 ok_active', function () {
        file_put_contents(sys_get_temp_dir() . '/mock_web_mode.txt', 'ok_active');
        [$code, $json, , $row, $cached] = withRow();
        $ok = $code === 200 && ($json['success'] ?? null) === true
            && ($json['data']['status'] ?? '') === 'active'
            && ($json['data']['is_valid'] ?? null) === true
            && ($json['data']['expire_date'] ?? '') === '2030-01-01'
            && $row->enamad_status == 1
            && $row->enamad_expire_date === '2030-01-01'
            && $cached === null;
        return [$ok, "http=$code, status={$row->enamad_status}, expire={$row->enamad_expire_date}, cacheCleared=" . var_export($cached === null, true)];
    });

    // W3: 200 suspended -> 200 success, status 0 --------------------------
    scenario('W3 ok_suspended', function () {
        file_put_contents(sys_get_temp_dir() . '/mock_web_mode.txt', 'ok_suspended');
        [$code, $json, , $row] = withRow();
        $ok = $code === 200 && ($json['data']['status'] ?? '') === 'suspended'
            && ($json['data']['is_valid'] ?? null) === false
            && $row->enamad_status == 0;
        return [$ok, "http=$code, statusAfter={$row->enamad_status}"];
    });

    // W4: 200 + non-JSON body (HTML error page) -> malformed guard -------
    scenario('W4 bad_json_html', function () {
        file_put_contents(sys_get_temp_dir() . '/mock_web_mode.txt', 'bad_json');
        [$code, $json, , $row, $cached] = withRow();
        $logged = strpos(logTail(), 'Enamad verification returned a malformed payload') !== false;
        $ok = $code === 502 && ($json['success'] ?? null) === false
            && !isset($json['data'])                       // no verdict is invented
            && strpos($json['message'] ?? '', 'نامعتبر') !== false
            && $row->enamad_status == 1 && $row->enamad_expire_date === null
            && $logged && $cached === 'stale';
        return [$ok, "http=$code, rowUntouched=" . var_export($row->enamad_status == 1, true)
            . ", cacheUntouched=" . var_export($cached === 'stale', true) . ", logged=" . var_export($logged, true)];
    });

    // W5: 200 + valid JSON without 'status' (has expire_date!) -> the guard
    // must reject BEFORE any update, so expire_date must stay untouched too
    scenario('W5 json_no_status', function () {
        file_put_contents(sys_get_temp_dir() . '/mock_web_mode.txt', 'no_status');
        [$code, $json, , $row, $cached] = withRow();
        $logged = strpos(logTail(), 'Enamad verification returned a malformed payload') !== false;
        $ok = $code === 502 && ($json['success'] ?? null) === false
            && $row->enamad_status == 1 && $row->enamad_expire_date === null
            && $logged && $cached === 'stale';
        return [$ok, "http=$code, expireUntouched=" . var_export($row->enamad_expire_date === null, true)];
    });

    // W6: 200 + empty body -> malformed guard ----------------------------
    scenario('W6 empty_body', function () {
        file_put_contents(sys_get_temp_dir() . '/mock_web_mode.txt', 'empty_body');
        [$code, $json, , $row, $cached] = withRow();
        $logged = strpos(logTail(), 'Enamad verification returned a malformed payload') !== false;
        $ok = $code === 502 && ($json['success'] ?? null) === false
            && $row->enamad_status == 1 && $row->enamad_expire_date === null
            && $logged && $cached === 'stale';
        return [$ok, "http=$code, rowUntouched=" . var_export($row->enamad_status == 1, true)];
    });

    // W7: network guard - real endpoint, stubbed before any packet --------
    scenario('W7 network guard', function () use ($realBaseUrl) {
        file_put_contents(sys_get_temp_dir() . '/mock_web_mode.txt', 'http500'); // unused; stub answers
        config(['enamad.api.base_url' => $realBaseUrl]); // real api.enamad.ir
        Http::fake(function ($request) {
            return Http::response('{"message":"NETWORK-GUARD-BLOCKED url=' . $request->url() . '"}', 503);
        });
        [$code, $json, , $row] = withRow();
        $ok = $code === 400 && strpos($json['message'] ?? '', 'NETWORK-GUARD-BLOCKED') !== false
            && strpos($json['message'] ?? '', $realBaseUrl . '/verify') !== false
            && $row->enamad_status == 1;
        return [$ok, "http=$code, realEndpointURL={$realBaseUrl}/verify (stubbed), rowUntouched=" . var_export($row->enamad_status == 1, true)];
    });
    // Http::fake() ends in Facade::swap(), which puts the stubbed Factory into
    // the CONTAINER; clearResolvedInstances() alone would leave the fake armed
    // and answer every later scenario. Drop both the instance and the facade root.
    $app->forgetInstance(HttpFactory::class);
    Http::clearResolvedInstances();
    config(['enamad.api.base_url' => 'http://127.0.0.1:8932']);

    // W9: the manual admin button route shares verify() and its guard ----
    scenario('W9 manual route guard', function () {
        file_put_contents(sys_get_temp_dir() . '/mock_web_mode.txt', 'no_status');
        [$code, $json, , $row, $cached] = withRow('/enamad/verify/manual');
        $logged = strpos(logTail(), 'Enamad verification returned a malformed payload') !== false;
        $ok = $code === 502 && ($json['success'] ?? null) === false
            && $row->enamad_status == 1 && $row->enamad_expire_date === null
            && $logged && $cached === 'stale';
        return [$ok, "http=$code, rowUntouched=" . var_export($row->enamad_status == 1, true)];
    });

    // W9b: manual route still succeeds on a good payload -----------------
    scenario('W9b manual route success', function () {
        file_put_contents(sys_get_temp_dir() . '/mock_web_mode.txt', 'ok_active');
        [$code, $json, , $row, $cached] = withRow('/enamad/verify/manual');
        $ok = $code === 200 && ($json['success'] ?? null) === true
            && ($json['data']['status'] ?? '') === 'active'
            && $row->enamad_status == 1 && $row->enamad_expire_date === '2030-01-01'
            && $cached === null;
        return [$ok, "http=$code, status={$row->enamad_status}, expire={$row->enamad_expire_date}"];
    });

    // W10: after the whole malformed family, a known-good status survived -
    scenario('W10 bad payload never overwrote a good seal', function () {
        $row = refreshRow();
        $ok = $row->enamad_status == 1 && $row->enamad_expire_date === '2030-01-01';
        return [$ok, "status={$row->enamad_status}, expire={$row->enamad_expire_date}"];
    });

    // W11: timeout -> exception catch. Kept LAST on purpose: the mock server
    // is single-threaded and holds the socket open for 3s in timeout mode, so
    // any scenario after this one would just wait on the same listener.
    scenario('W11 timeout', function () {
        file_put_contents(sys_get_temp_dir() . '/mock_web_mode.txt', 'timeout');
        config(['enamad.api.timeout' => 1]); // client gives up after ~1s
        [$code, $json, , $row] = withRow();
        $logged = strpos(logTail(), 'Enamad verification exception') !== false;
        $curl28 = strpos($json['message'] ?? '', 'cURL error 28') !== false
            || stripos($json['message'] ?? '', 'timed out') !== false;
        $ok = $code === 500 && $curl28 && $logged && $row->enamad_status == 1;
        return [$ok, "http=$code, cURL28=" . var_export($curl28, true)
            . ", rowUntouched=" . var_export($row->enamad_status == 1, true)];
    });
} finally {
    $restoreRow(); // restore the admin row no matter what
    proc_terminate($proc);
    proc_close($proc);
    @unlink(sys_get_temp_dir() . '/mock_web_mode.txt');
}

echo implode(PHP_EOL, $results) . PHP_EOL;

// request-content proof: the web path posts the same fields as the command
$requests = (string) @file_get_contents(sys_get_temp_dir() . '/mock_web_requests.log');
$posts = substr_count($requests, 'POST /verify');
$fieldsOk = strpos($requests, 'site_id') !== false
    && strpos($requests, 'secret_key') !== false
    && strpos($requests, 'domain') !== false;
echo "mock server received $posts POST /verify request(s)" . PHP_EOL;
echo $fieldsOk
    ? 'PASS request payload carries site_id, secret_key and domain' . PHP_EOL
    : 'FAIL request payload missing expected fields' . PHP_EOL;

$fail = count(array_filter($results, fn ($r) => strpos($r, 'FAIL') === 0));
exit($fail === 0 && $fieldsOk ? 0 : 1);