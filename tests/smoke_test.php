<?php
/**
 * smoke_test.php - one-command health check to run after every update.
 *
 * Three phases:
 *   1. Public profile : GET /proresume/Romario must render 200 with the
 *                       profile title and no Laravel error page (regression
 *                       for the subdirectory getUser() 404 bug).
 *   2. Watermark E2E  : login as the demo user, upload a fresh JPEG through
 *                       /user/watermark/update, verify DB row + stored file
 *                       + live public render (badge markup, text, image),
 *                       then restore the exact pre-test state.
 *   3. Enamad CLI     : the mock-band verifier harness (loopback only, no
 *                       real network) covering success, HTTP error, real
 *                       timeout and malformed payloads.
 *   4. Enamad web     : the same matrix driven through the real HTTP kernel
 *                       against POST /enamad/verify (and the admin manual
 *                       route), asserting the malformed-payload guard keeps
 *                       the stored row and the status cache untouched.
 *   5. Payment digest : fixture log files + log mail transport, asserting the
 *                       payment:digest parser, gateway/kind classification,
 *                       window boundaries and dedup behaviour.
 *   6. Updater bundle : simulates the updater's delete-then-replace against a
 *                       throwaway copy and proves the Enamad/payment digest
 *                       files actually land on a customer install.
 *   7. Persian (fa)    : the three translation namespaces are complete, the
 *                       per-language DB rows exist, and switching the front
 *                       end to fa renders dir="rtl" with Persian markup.
 *   8. Lang coverage   : every __('...')/@lang/trans() key used by any view
 *                       resolves against the locale that view actually runs
 *                       under (fa / admin_fa / user_fa). Catches keys that
 *                       never made it into the *_en.json files and so are
 *                       invisible to a parity check.
 *
 * Usage: php tests/smoke_test.php
 * Exit code 0 = every check passed. All test artifacts are cleaned up.
 */

error_reporting(E_ALL);

$passes = 0;
$failures = 0;

function ok(string $msg): void
{
    global $passes;
    $passes++;
    echo "PASS: $msg\n";
}

function bad(string $msg): void
{
    global $failures;
    $failures++;
    echo "FAIL: $msg\n";
}

$projectRoot = dirname(__DIR__);
chdir($projectRoot);

/**
 * Run a PHP snippet inside a fully booted framework and return only the
 * snippet's own output. `php -r` prints extension startup warnings (the
 * chronic Imagick one) to STDOUT before the script runs, so the boot emits
 * a sentinel and everything before it is discarded.
 */
function php_boot(string $code): string
{
    $boot = 'ob_start(); require "vendor/autoload.php"; $app = require "bootstrap/app.php";'
        . ' $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);'
        . ' $kernel->bootstrap(); ob_end_clean(); echo "<<<BOOT>>>";';
    $spec = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $proc = proc_open([PHP_BINARY, '-r', $boot . $code], $spec, $pipes, getcwd());
    if (!is_resource($proc)) {
        return '';
    }
    $out = (string) stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($proc);
    $pos = strpos($out, '<<<BOOT>>>');
    return $pos === false ? '' : substr($out, $pos + strlen('<<<BOOT>>>'));
}

/**
 * POST a form and return [httpCode, redirectTarget]. proc_open with an argv
 * array avoids shell quoting issues, and the response headers are dumped via
 * -D - and parsed in PHP: -w %{redirect_url} is unreliable across curl
 * builds (the native Windows build yields an empty capture, and %{redirect_url}
 * alone with an empty body even fails with exit 23), while the Location
 * header behaves the same everywhere.
 */
function http_post(string $url, array $fields, array $files = [], string $cookieJar = ''): array
{
    $argv = ["curl", "-s", "-o", "/dev/null", "-D", "-"];
    if ($cookieJar !== '') {
        $argv = array_merge($argv, ["-b", $cookieJar, "-c", $cookieJar]);
    }
    foreach ($fields as $name => $value) {
        $argv[] = '-F';
        $argv[] = "$name=$value";
    }
    foreach ($files as $name => $path) {
        $argv[] = '-F';
        $argv[] = "$name=@$path;type=image/jpeg";
    }
    $argv[] = $url;
    $spec = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $proc = proc_open($argv, $spec, $pipes);
    if (!is_resource($proc)) {
        return [0, ''];
    }
    $headers = (string) stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($proc);

    $status = 0;
    $location = '';
    foreach (explode("\n", $headers) as $line) {
        $line = trim($line);
        if ($status === 0 && preg_match('#^HTTP/\S+\s+(\d{3})#', $line, $m)) {
            $status = (int) $m[1];
        } elseif (stripos($line, 'Location:') === 0) {
            $location = trim(substr($line, strlen('Location:')));
        }
    }
    return [$status, $location];
}

/** GET a URL, returning [httpCode, body]. */
function http_get(string $url, string $cookieJar = '', string $outFile = ''): array
{
    $out = $outFile !== '' ? $outFile : tempnam(sys_get_temp_dir(), 'smoke');
    $cmd = 'curl -s -o ' . escapeshellarg($out) . ' -w "%{http_code}"';
    if ($cookieJar !== '') {
        $cmd .= ' -b ' . escapeshellarg($cookieJar) . ' -c ' . escapeshellarg($cookieJar);
    }
    $cmd .= ' ' . escapeshellarg($url);
    $code = (string) shell_exec($cmd);
    $body = (string) @file_get_contents($out);
    if ($outFile === '') {
        @unlink($out);
    }
    return [(int) $code, $body];
}

/** Extract the first CSRF token from an HTML form. */
function csrf_token(string $html): string
{
    return preg_match('/name="_token" value="([^"]+)"/', $html, $m) ? $m[1] : '';
}

$base = getenv('SMOKE_BASE_URL') ?: 'http://localhost/proresume';
$profileUrl = $base . '/Romario';
$jpg = 'smoke_wm_test.jpg';

// ============================================================ phase 1
echo "== Phase 1: public profile render ==\n";
[$code, $body] = http_get($profileUrl);
$code === 200
    ? ok("GET $profileUrl -> 200")
    : bad("GET $profileUrl -> $code");
strpos($body, 'Romario') !== false
    ? ok('profile HTML identifies the user (Romario)')
    : bad('profile HTML does not identify the user');
(strpos($body, 'Whoops') === false && strpos($body, 'laravel log') === false)
    ? ok('no Laravel error page rendered')
    : bad('Laravel error page leaked to a public URL');

// ============================================================ phase 2
echo "== Phase 2: watermark upload E2E ==\n";

$stateJson = php_boot(
    '$row = Illuminate\Support\Facades\DB::table("user_basic_settings")'
    . '->where("user_id", 62)->orderBy("id")->first();'
    . ' echo json_encode($row ? ["id" => $row->id, "st" => $row->watermark_status,'
    . ' "tx" => $row->watermark_text, "url" => $row->watermark_url,'
    . ' "img" => $row->watermark_image] : null);'
);
$snap = $stateJson !== '' ? json_decode(trim($stateJson), true) : null;
if (is_array($snap)) {
    ok("pre-test row snapshot taken (id={$snap['id']})");
} else {
    bad('could not snapshot user_basic_settings row for user 62');
}

// create the test JPEG with GD
@unlink($jpg);
$made = php_boot(
    '$img = imagecreatetruecolor(80, 40);'
    . ' imagefilledrectangle($img, 0, 0, 80, 39, imagecolorallocate($img, 210, 225, 250));'
    . ' echo imagejpeg($img, ' . var_export($jpg, true) . ', 90) ? "ok" : "fail";'
);
trim($made) === 'ok' && is_file($jpg)
    ? ok("test JPEG created ($jpg)")
    : bad('could not create test JPEG');

// user login: fresh cookie jar, GET the form for _token, then POST /login
$jar = tempnam(sys_get_temp_dir(), 'smoke_cj');
[, $loginForm] = http_get($base . '/login', $jar);
$loginToken = csrf_token($loginForm);
[$loginCode, $loginRedirect] = http_post(
    $base . '/login',
    ['email' => 'romario@gmail.com', 'password' => '12345678', '_token' => $loginToken],
    [],
    $jar
);
$loginRedirect === $base . '/user/dashboard'
    ? ok('user login redirected to dashboard')
    : bad("user login failed (HTTP $loginCode, redirect: " . ($loginRedirect !== '' ? $loginRedirect : 'none') . ')');

// watermark settings form + CSRF token
[$formCode, $formHtml] = http_get($base . '/user/watermark', $jar);
$formToken = csrf_token($formHtml);
$formCode === 200 && strlen($formToken) === 40
    ? ok('watermark form reachable with CSRF token')
    : bad('watermark form/CSRF problem (HTTP ' . $formCode . ', token len ' . strlen($formToken) . ')');

// upload through the real endpoint; back() redirects may omit the
// subdirectory prefix, so only the path suffix is asserted
[$upCode, $upRedirect] = http_post(
    $base . '/user/watermark/update',
    ['_token' => $formToken, 'watermark_status' => '1', 'watermark_text' => 'SMOKEWM', 'watermark_url' => 'https://example.com'],
    ['watermark_image' => $jpg],
    $jar
);
$upPath = (string) parse_url($upRedirect, PHP_URL_PATH);
$upCode === 302 && substr($upPath, -15) === '/user/watermark'
    ? ok('watermark upload accepted (302 redirect to settings)')
    : bad("watermark upload rejected (HTTP $upCode, redirect: " . ($upRedirect !== '' ? $upRedirect : 'none') . ')');
unlink($jpg);

// DB row + stored file must reflect the upload
$uploaded = php_boot(
    '$row = Illuminate\Support\Facades\DB::table("user_basic_settings")'
    . '->where("user_id", 62)->orderBy("id")->first();'
    . ' echo $row->watermark_status . "|" . $row->watermark_text . "|" . $row->watermark_image;'
);
[$st, $tx, $img] = array_pad(explode('|', trim($uploaded)), 3, '');
$imgSafe = preg_match('/^[a-f0-9]{10,}\.(jpg|jpeg|png)$/', $img) ? $img : '';
if ($st === '1' && $tx === 'SMOKEWM' && $imgSafe !== ''
    && is_file("public/assets/front/img/user/watermark/$imgSafe")) {
    ok('DB row updated and image stored on disk');
} else {
    bad("DB/file state after upload wrong: $uploaded");
}

// the public profile must render the freshly uploaded watermark
[, $pubHtml] = http_get($profileUrl);
$imgSafe !== ''
    && strpos($pubHtml, 'SMOKEWM') !== false
    && strpos($pubHtml, "watermark/$imgSafe") !== false
    && strpos($pubHtml, 'watermark-badge-container') !== false
    ? ok('public profile renders the uploaded watermark (badge markup, text, image)')
    : bad('public profile misses the uploaded watermark');

// restore the pre-test row exactly and remove the uploaded file
php_boot(
    'Illuminate\Support\Facades\DB::table("user_basic_settings")'
    . '->where("id", ' . (int) $snap['id'] . ')->update(['
    . '"watermark_status" => ' . var_export((int) $snap['st'], true) . ','
    . ' "watermark_text" => ' . var_export($snap['tx'], true) . ','
    . ' "watermark_url" => ' . var_export($snap['url'], true) . ','
    . ' "watermark_image" => ' . var_export($snap['img'], true) . ']);'
    . ' echo "restored";'
);
if ($imgSafe !== '') {
    @unlink("public/assets/front/img/user/watermark/$imgSafe");
}
$restored = php_boot(
    '$row = Illuminate\Support\Facades\DB::table("user_basic_settings")'
    . '->where("id", ' . (int) $snap['id'] . ')->first();'
    . ' echo $row->watermark_status . "|" . var_export($row->watermark_text, true) . "|"'
    . ' . var_export($row->watermark_url, true) . "|" . var_export($row->watermark_image, true);'
);
$expected = ((int) $snap['st']) . '|' . var_export($snap['tx'], true) . '|' . var_export($snap['url'], true) . '|' . var_export($snap['img'], true);
trim($restored) === $expected
    ? ok('user_basic_settings row restored to the exact pre-test state')
    : bad("row restore mismatch: got [$restored], expected [$expected]");

// ============================================================ phase 3
echo "== Phase 3: Enamad verifier (mock-band harness) ==\n";
$harnessOut = [];
$lines = exec('php tests/enamad_verify_harness.php 2>&1', $harnessOut, $harnessExit);
foreach ($harnessOut as $line) {
    if (strpos($line, 'Imagick') !== false) {
        continue;
    }
    echo '    ' . $line . "\n";
}
$harnessExit === 0
    ? ok('enamad harness: all scenarios passed')
    : bad('enamad harness failed');

// ============================================================ phase 4
echo "== Phase 4: Enamad web endpoint (mock-band harness) ==\n";
$webOut = [];
$lines = exec('php tests/enamad_web_harness.php 2>&1', $webOut, $webExit);
foreach ($webOut as $line) {
    if (strpos($line, 'Imagick') !== false) {
        continue;
    }
    echo '    ' . $line . "\n";
}
$webExit === 0
    ? ok('enamad web harness: malformed-payload guard verified on /enamad/verify')
    : bad('enamad web harness failed');

// ============================================================ phase 5
echo "== Phase 5: payment:digest (fixture harness) ==\n";
$payOut = [];
$lines = exec('php tests/payment_digest_harness.php 2>&1', $payOut, $payExit);
foreach ($payOut as $line) {
    if (strpos($line, 'Imagick') !== false) {
        continue;
    }
    echo '    ' . $line . "\n";
}
$payExit === 0
    ? ok('payment digest harness: parsing, classification, window and dedup verified')
    : bad('payment digest harness failed');

// ============================================================ phase 6
echo "== Phase 6: updater bundle closure ==\n";
$updOut = [];
$lines = exec('php tests/updater_bundle_check.php 2>&1', $updOut, $updExit);
foreach ($updOut as $line) {
    if (strpos($line, 'Imagick') !== false) {
        continue;
    }
    echo '    ' . $line . "\n";
}
$updExit === 0
    ? ok('updater bundle ships the Enamad/payment digest feature intact')
    : bad('updater bundle is incomplete - customer sites would lose the feature');

// ============================================================ phase 7
echo "== Phase 7: Persian language (fa) ==\n";
$faOut = [];
$lines = exec('php tests/persian_lang_harness.php 2>&1', $faOut, $faExit);
foreach ($faOut as $line) {
    if (strpos($line, 'Imagick') !== false) {
        continue;
    }
    echo '    ' . $line . "\n";
}
$faExit === 0
    ? ok('persian harness: translation files, per-language rows and RTL render verified')
    : bad('persian harness failed');

// ============================================================ phase 8
echo "== Phase 8: translation key coverage ==\n";
$covOut = [];
$lines = exec('php tests/lang_coverage_audit.php 2>&1', $covOut, $covExit);
foreach ($covOut as $line) {
    if (strpos($line, 'Imagick') !== false) {
        continue;
    }
    echo '    ' . $line . "\n";
}
$covExit === 0
    ? ok('every __()/@lang/trans() key used by a view resolves in the Persian namespace')
    : bad('some translation keys used by the views do not resolve');

// ============================================================ phase 9
echo "== Phase 9: Enamad trust seal in every footer ==\n";
$sealOut = [];
$lines = exec('php tests/enamad_footer_harness.php 2>&1', $sealOut, $sealExit);
foreach ($sealOut as $line) {
    if (strpos($line, 'Imagick') !== false) {
        continue;
    }
    echo '    ' . $line . "\n";
}
$sealExit === 0
    ? ok('enamad footer harness: every footer renders the seal and the updater carries it')
    : bad('enamad footer harness failed');

// ============================================================ summary
echo "== Summary ==\n";
echo "PASS: $passes  FAIL: $failures\n";
@unlink($jar);
exit($failures === 0 ? 0 : 1);
