<?php
/**
 * Mock-band harness for `php artisan enamad:verify` (project tooling).
 *
 * A child process runs a stdlib TCP mock server on 127.0.0.1:8931 only;
 * api.enamad.ir is never contacted. Scenarios S1-S4, S6-S8 go to the
 * loopback mock; S5 stubs the Http facade so the real endpoint is proven
 * reachable-by-URL without a packet leaving the machine. The command runs
 * through the real console kernel, so exit codes, $this->info/error output
 * and the enamad log channel are exercised. The single admin BasicSetting
 * row is snapshotted and restored; the harness never inserts rows.
 *
 * Scenarios:
 *   S0 unconfigured       -> exit 0, "not configured"
 *   S1 HTTP 500           -> exit 1, error logged, row untouched
 *   S2 200 active         -> exit 0, enamad_status=1, expire_date persisted
 *   S3 200 suspended      -> exit 0, enamad_status=0
 *   S4 timeout (1s cfg)   -> exit 1, cURL 28 via the exception catch
 *   S5 network guard      -> real api.enamad.ir URL stubbed via Http::fake
 *   S6 200 + non-JSON body (HTML error page) -> exit 1, malformed, row safe
 *   S7 200 + JSON without status (but WITH expire_date) -> exit 1, row and
 *      expire_date untouched (no partial writes)
 *   S8 200 + empty body   -> exit 1, malformed
 *
 * Usage: php tests/enamad_verify_harness.php
 * Exit code 0 = all scenarios passed.
 */

if (in_array('--server', $argv ?? [], true)) {
    // ---------------------------------------------------------------- child
    $srv = stream_socket_server('tcp://127.0.0.1:8931', $errno, $errstr);
    if (!$srv) {
        fwrite(STDERR, "mock server failed: $errstr\n");
        exit(2);
    }
    $deadline = microtime(true) + 120; // self-limit; parent terminates earlier
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

        @file_put_contents(sys_get_temp_dir() . '/mock_requests.log', $req . $body . "\n\n", FILE_APPEND);

        $mode = trim((string) @file_get_contents(sys_get_temp_dir() . '/mock_mode.txt'));

        if ($mode === 'timeout') {
            // hold the connection open silently past the client timeout,
            // then close without ever answering -> real cURL error 28
            usleep(3000000);
            fclose($conn);
            continue;
        }

        $payloads = [
            'http500'   => [500, 'Internal Server Error', '{"message":"mock upstream error"}'],
            'ok_active' => [200, 'OK', '{"status":"active","expire_date":"2030-01-01"}'],
            'ok_suspended' => [200, 'OK', '{"status":"suspended","expire_date":"2026-01-01"}'],
            // malformed family: HTTP 200 but unusable payloads
            'bad_json'  => [200, 'OK', '<html><body>Service Unavailable</body></html>'],
            'no_status' => [200, 'OK', '{"message":"ok","expire_date":"2030-01-01"}'],
            'empty_body' => [200, 'OK', ''],
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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

@unlink(sys_get_temp_dir() . '/mock_mode.txt');
@unlink(sys_get_temp_dir() . '/mock_requests.log');

// array command form: handles "D:\Program Files\..." spaces on Windows safely
$proc = proc_open(
    [PHP_BINARY, __FILE__, '--server'],
    [1 => ['file', sys_get_temp_dir() . '/mock_server.log', 'w'], 2 => ['file', sys_get_temp_dir() . '/mock_server.log', 'a']],
    $pipes
);
if (!is_resource($proc)) {
    fwrite(STDERR, "cannot start mock server\n");
    exit(2);
}
$up = false;
for ($i = 0; $i < 40; $i++) {
    $probe = @fsockopen('127.0.0.1', 8931, $pe, $ps, 0.2);
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

/** Run one command invocation. Returns [exitCode, output]. */
function runVerify(): array
{
    global $kernel;
    $code = $kernel->call('enamad:verify');
    return [$code, $kernel->output()];
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
    global $results;
    $t0 = microtime(true);
    try {
        [$ok, $detail] = $fn();
        $results[] = sprintf('%s %s (%s, %.2fs)', $ok ? 'PASS' : 'FAIL', $name, $detail, microtime(true) - $t0);
    } catch (Throwable $e) {
        $results[] = "FAIL $name (harness exception: " . $e->getMessage() . ')';
    }
}

/** Arm credentials + status=1, expire_date=NULL and run one invocation. */
function withRow(): array
{
    refreshRow()->update([
        'enamad_site_id' => 'SITE-TEST',
        'enamad_secret_key' => 'SECRET-TEST',
        'enamad_status' => 1,
        'enamad_expire_date' => null,
    ]);
    [$code, $out] = runVerify();
    return [$code, $out, refreshRow()];
}

try {
    // S0: unconfigured credentials -----------------------------------------
    scenario('S0 unconfigured', function () {
        refreshRow()->update(['enamad_site_id' => null, 'enamad_secret_key' => null]);
        [$code, $out] = runVerify();
        $ok = $code === 0 && strpos($out, 'not configured') !== false;
        return [$ok, "exit=$code"];
    });

    // shared config: point the command at the loopback mock server
    config([
        'enamad.api.base_url' => 'http://127.0.0.1:8931',
        'enamad.api.timeout' => 5,
    ]);

    // S1: HTTP 500 -> non-successful branch --------------------------------
    scenario('S1 http500', function () {
        file_put_contents(sys_get_temp_dir() . '/mock_mode.txt', 'http500');
        [$code, $out, $row] = withRow();
        $logged = strpos(logTail(), 'Scheduled Enamad verification failed') !== false;
        $ok = $code === 1 && $row->enamad_status == 1 && $logged
            && strpos($out, 'Verification failed') !== false;
        return [$ok, "exit=$code, rowUntouched=" . var_export($row->enamad_status == 1, true)];
    });

    // S2: 200 active -> status 1 + expire date persisted + caches cleared --
    scenario('S2 ok_active', function () {
        file_put_contents(sys_get_temp_dir() . '/mock_mode.txt', 'ok_active');
        Cache::put('enamad_status', 'stale', 60);
        [$code, $out, $row] = withRow();
        $ok = $code === 0 && $row->enamad_status == 1
            && $row->enamad_expire_date === '2030-01-01'
            && strpos($out, 'status=active') !== false
            && Cache::get('enamad_status') === null;
        return [$ok, "exit=$code, status={$row->enamad_status}, expire={$row->enamad_expire_date}"];
    });

    // S3: 200 suspended -> status 0 ----------------------------------------
    scenario('S3 ok_suspended', function () {
        file_put_contents(sys_get_temp_dir() . '/mock_mode.txt', 'ok_suspended');
        [$code, $out, $row] = withRow();
        $ok = $code === 0 && $row->enamad_status == 0
            && strpos($out, 'status=suspended') !== false;
        return [$ok, "exit=$code, statusAfter={$row->enamad_status}"];
    });

    // S4: timeout -> exception catch ----------------------------------------
    scenario('S4 timeout', function () {
        file_put_contents(sys_get_temp_dir() . '/mock_mode.txt', 'timeout');
        config(['enamad.api.timeout' => 1]); // client gives up after ~1s
        [$code, $out, $row] = withRow();
        $logged = strpos(logTail(), 'verification exception') !== false;
        $curl28 = strpos($out, 'cURL error 28') !== false || stripos($out, 'timed out') !== false;
        $ok = $code === 1 && $curl28 && $logged && $row->enamad_status == 1;
        return [$ok, "exit=$code, cURL28=" . var_export($curl28, true) . ", rowUntouched=" . var_export($row->enamad_status == 1, true)];
    });
    config(['enamad.api.timeout' => 5]);

    // S6: 200 + non-JSON body (HTML error page) -> malformed guard ---------
    scenario('S6 bad_json_html', function () {
        file_put_contents(sys_get_temp_dir() . '/mock_mode.txt', 'bad_json');
        [$code, $out, $row] = withRow();
        $logged = strpos(logTail(), 'malformed payload') !== false;
        $ok = $code === 1 && $row->enamad_status == 1 && $row->enamad_expire_date === null
            && $logged && strpos($out, 'malformed response payload') !== false;
        return [$ok, "exit=$code, rowUntouched=" . var_export($row->enamad_status == 1, true) . ", logged=" . var_export($logged, true)];
    });

    // S7: 200 + valid JSON without 'status' (has expire_date!) -> guard must
    // reject BEFORE any update, so expire_date must stay untouched too ------
    scenario('S7 json_no_status', function () {
        file_put_contents(sys_get_temp_dir() . '/mock_mode.txt', 'no_status');
        [$code, $out, $row] = withRow();
        $logged = strpos(logTail(), 'malformed payload') !== false;
        $ok = $code === 1 && $row->enamad_status == 1 && $row->enamad_expire_date === null
            && $logged && strpos($out, 'malformed response payload') !== false;
        return [$ok, "exit=$code, expireUntouched=" . var_export($row->enamad_expire_date === null, true)];
    });

    // S8: 200 + empty body -> malformed guard ------------------------------
    scenario('S8 empty_body', function () {
        file_put_contents(sys_get_temp_dir() . '/mock_mode.txt', 'empty_body');
        [$code, $out, $row] = withRow();
        $logged = strpos(logTail(), 'malformed payload') !== false;
        $ok = $code === 1 && $row->enamad_status == 1 && $row->enamad_expire_date === null
            && $logged && strpos($out, 'malformed response payload') !== false;
        return [$ok, "exit=$code, rowUntouched=" . var_export($row->enamad_status == 1, true)];
    });

    // S5: network guard - real endpoint, stubbed before any packet ---------
    scenario('S5 network guard', function () use ($realBaseUrl) {
        file_put_contents(sys_get_temp_dir() . '/mock_mode.txt', 'http500'); // unused; stub answers
        config(['enamad.api.base_url' => $realBaseUrl]); // real api.enamad.ir
        Http::fake(function ($request) {
            return Http::response('{"message":"NETWORK-GUARD-BLOCKED url=' . $request->url() . '"}', 503);
        });
        [$code, $out] = runVerify();
        $ok = $code === 1 && strpos($out, 'NETWORK-GUARD-BLOCKED') !== false
            && strpos($out, $realBaseUrl . '/verify') !== false;
        return [$ok, "exit=$code, realEndpointURL=" . $realBaseUrl . '/verify (stubbed)'];
    });
} finally {
    $restoreRow(); // restore the admin row no matter what
    proc_terminate($proc);
    proc_close($proc);
    @unlink(sys_get_temp_dir() . '/mock_mode.txt');
}

echo implode(PHP_EOL, $results) . PHP_EOL;

// request-content proof
$requests = (string) @file_get_contents(sys_get_temp_dir() . '/mock_requests.log');
$posts = substr_count($requests, 'POST /verify');
echo "mock server received $posts POST /verify request(s)" . PHP_EOL;
$fieldsOk = strpos($requests, 'site_id') !== false
    && strpos($requests, 'secret_key') !== false
    && strpos($requests, 'domain') !== false;
echo $fieldsOk
    ? 'PASS request payload carries site_id, secret_key and domain' . PHP_EOL
    : 'FAIL request payload missing expected fields' . PHP_EOL;

$fail = count(array_filter($results, fn ($r) => strpos($r, 'FAIL') === 0));
exit($fail === 0 && $fieldsOk ? 0 : 1);
