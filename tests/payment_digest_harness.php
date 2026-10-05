<?php
/**
 * Fixture-band harness for `php artisan payment:digest` (project tooling).
 *
 * Nothing here touches the real payment log and no mail leaves the machine:
 * the command is pointed at a throwaway log directory through
 * config('logging.channels.payment.path'), the mail transport is forced to
 * 'log' before boot, and a synthetic log file is written in the exact byte
 * layout Laravel's daily driver produces - including multi-line stack traces,
 * which are what defeated the previous line-by-line reader.
 *
 * Scenarios:
 *   P1 level filtering     -> INFO ignored, WARNING/ERROR counted
 *   P2 gateway detection   -> leading token of the message, context override
 *   P3 kind classification -> every known failure phrase maps to its bucket,
 *                             and an unseen wording still lands somewhere
 *   P4 multi-line trace    -> context survives, error text is recovered
 *   P5 no context at all   -> "Pay.ir callback: missing token" still counts
 *   P6 window cutoff       -> --window=24 drops the previous day's records
 *   P7 window=72h          -> a third daily file is reached (the old parser
 *                             only ever read yesterday + today)
 *   P8 dedup               -> no resend without new failures, --force wins
 *   P9 mail rendering      -> subject + rendered view content
 *   P10 blade escaping     -> injected HTML in log data is escaped
 *   P11 enamad regression  -> enamad:digest still counts on the shared parser
 *   P12 empty result       -> no failures means no mail and a clean exit 0
 *   P13 --min-level        -> severity floor honoured, bad value exits 1
 *
 * Usage: php tests/payment_digest_harness.php
 * Exit code 0 = all scenarios passed.
 */

error_reporting(E_ALL);

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

$projectRoot = dirname(__DIR__);
chdir($projectRoot);

// the mail transport is chosen during boot, so the override must exist before
// the framework is created - this keeps every send inside storage/logs
putenv('MAIL_DRIVER=log');
$_ENV['MAIL_DRIVER'] = 'log';
$_SERVER['MAIL_DRIVER'] = 'log';

require $projectRoot . '/vendor/autoload.php';
$app = require $projectRoot . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$passes = 0;
$failures = 0;

// Cleanup runs from a shutdown function as well as at the end of the script:
// a fatal error in the code under test (or a hard kill) would otherwise leave
// fixture directories behind in the temp dir. Guarded so it stays idempotent.
$fixtureDir = null;
register_shutdown_function(function () use (&$fixtureDir) {
    if ($fixtureDir === null || !is_dir($fixtureDir)) {
        return;
    }
    foreach (glob($fixtureDir . '/*') ?: [] as $f) {
        @unlink($f);
    }
    @rmdir($fixtureDir);
});

function ok(string $msg): void
{
    global $passes;
    $passes++;
    echo "PASS $msg\n";
}

function bad(string $msg): void
{
    global $failures;
    $failures++;
    echo "FAIL $msg\n";
}

/** Run an artisan command in-process. Returns [exitCode, output]. */
function artisan(string $command, array $params = []): array
{
    global $kernel;
    $code = $kernel->call($command, $params);
    return [$code, (string) $kernel->output()];
}

// ---------------------------------------------------------------- fixtures
$fixtureDir = sys_get_temp_dir() . '/payment_digest_fixture_' . getmypid();
if (!is_dir($fixtureDir)) {
    mkdir($fixtureDir, 0777, true);
}

// the daily driver names files <channel>-Y-m-d.log, and the parser derives the
// day list from that base path, so the fixture must be written under the same
// dated names the command will look for
$todayName = $fixtureDir . '/payment-' . date('Y-m-d') . '.log';
$enamadTodayName = $fixtureDir . '/enamad-' . date('Y-m-d') . '.log';
$paymentLog = $fixtureDir . '/payment.log';   // configured base path
$enamadLog = $fixtureDir . '/enamad.log';

/** A daily-driver record: "[ts] env.LEVEL: message {json} ". */
function rec(string $ts, string $level, string $message, array $ctx = []): string
{
    $line = "[{$ts}] local.{$level}: {$message}";
    return $ctx === [] ? $line . " \n" : $line . ' ' . json_encode($ctx) . " \n";
}

/**
 * A record whose context contains a REAL newline (a stack trace).
 *
 * Monolog writes the context JSON with literal newlines inside string values,
 * so a record carrying a trace spills over several physical lines - verified
 * against the real payment log, where such records start with "#1 ..." on the
 * next line. Reproducing that here is the whole point: a naive line-by-line
 * reader leaves the JSON unterminated on the header line and loses the
 * context, which is exactly the bug this harness guards against.
 */
function traceRec(string $ts, string $level, string $message, string $error, string $trace): string
{
    $json = str_replace(['\\r', '\\n'], ["\r", "\n"], json_encode(['error' => $error, 'trace' => $trace]));
    return "[{$ts}] local.{$level}: {$message} {$json} \n";
}

$trace = "HandleExceptions->handleError(2, 'oops', 'file.php', 42)\n"
    . '#1 D:\\\\Program Files\\\\XAMPP\\\\htdocs\\\\proresume\\\\app\\\\Http\\\\Controllers\\\\Payment\\\\ZarinPalController.php(225): run(Array, Object(Array)) #2 {main}';

// Timestamps are anchored relative to now so the window assertions hold no
// matter when the harness runs.
$hoursAgo = fn (int $h): Carbon => Carbon::now()->subHours($h);

$lines = '';
// INFO = happy path, must never be counted
$lines .= rec($hoursAgo(1)->toDateTimeString(), 'INFO', 'ZarinPal payment initiation (admin)', ['order_id' => 'ORD-1']);
$lines .= rec($hoursAgo(1)->toDateTimeString(), 'INFO', 'ZarinPal payment verification (admin)', ['authority' => 'A1']);
$lines .= rec($hoursAgo(1)->toDateTimeString(), 'INFO', 'IDPay callback: duplicate/already processed', ['payment_id' => '9']);
// WARNING/ERROR = the matrix
$lines .= rec($hoursAgo(2)->toDateTimeString(), 'WARNING', 'ZarinPal callback: transaction not found', ['authority' => 'A-NOPE']);
$lines .= rec($hoursAgo(2)->toDateTimeString(), 'WARNING', 'Mellat callback: transaction not found (vendor)', ['refId' => null]);
$lines .= rec($hoursAgo(2)->toDateTimeString(), 'WARNING', 'Pay.ir callback: missing token');
$lines .= rec($hoursAgo(2)->toDateTimeString(), 'WARNING', 'Zinal callback: transaction not found', ['trackId' => 'T1']);
$lines .= rec($hoursAgo(2)->toDateTimeString(), 'WARNING', 'IDPay verify failed', ['payment_id' => '77']);
$lines .= rec($hoursAgo(2)->toDateTimeString(), 'WARNING', 'ZarinPal amount mismatch', ['authority' => 'A2']);
$lines .= rec($hoursAgo(2)->toDateTimeString(), 'WARNING', 'Mellat SOAP error (admin)');
$lines .= rec($hoursAgo(2)->toDateTimeString(), 'WARNING', 'NextPay send failed (admin, v2)');
$lines .= rec($hoursAgo(2)->toDateTimeString(), 'WARNING', 'Zinal refund failed (admin)', ['payment_id' => '88']);
$lines .= rec($hoursAgo(2)->toDateTimeString(), 'WARNING', 'ZarinPal payment cancelled/failed (admin)', ['authority' => 'A3']);
$lines .= rec($hoursAgo(2)->toDateTimeString(), 'ERROR', 'Verified payment without a checkout payload', ['gateway' => 'IDPay', 'transaction_id' => 'T9']);
$lines .= rec($hoursAgo(2)->toDateTimeString(), 'ERROR', 'Verified payment for a package that no longer exists', ['gateway' => 'Pay.ir', 'package_id' => 42]);
$lines .= rec($hoursAgo(2)->toDateTimeString(), 'ERROR', 'Verified payment outside a membership/extend checkout', ['gateway' => 'Mellat']);
$lines .= rec($hoursAgo(2)->toDateTimeString(), 'ERROR', 'SomeBrand quantum settlement drift');
// multi-line trace records
$lines .= traceRec($hoursAgo(3)->toDateTimeString(), 'ERROR', 'ZarinPal payment verification error (admin)', 'Undefined array key "address"', $trace);
$lines .= traceRec($hoursAgo(3)->toDateTimeString(), 'ERROR', 'IDPay refund error (admin)', 'cURL error 7: connection refused', $trace);

file_put_contents($todayName, $lines);

// previous days, for the window assertions
file_put_contents($fixtureDir . '/payment-' . $hoursAgo(30)->format('Y-m-d') . '.log',
    rec($hoursAgo(30)->toDateTimeString(), 'ERROR', 'ZarinPal callback: transaction not found', ['authority' => 'Y1'])
    . rec($hoursAgo(30)->toDateTimeString(), 'INFO', 'Mellat payment initiation (admin)', ['refId' => 'X']));

file_put_contents($fixtureDir . '/payment-' . $hoursAgo(50)->format('Y-m-d') . '.log',
    rec($hoursAgo(50)->toDateTimeString(), 'ERROR', 'Zibal verify error (vendor)'));

file_put_contents($enamadTodayName,
    rec($hoursAgo(2)->toDateTimeString(), 'ERROR', 'Scheduled Enamad verification failed', ['status_code' => 500, 'error' => 'boom'])
    . traceRec($hoursAgo(3)->toDateTimeString(), 'ERROR', 'Scheduled Enamad verification exception', 'cURL error 6: Could not resolve host', $trace));

// point the commands at the fixtures
config([
    'logging.channels.payment.path' => $paymentLog,
    'logging.channels.enamad.path' => $enamadLog,
    'payment.digest_recipient' => 'digest-owner@example.test',
]);
Cache::forget('payment:digest:sent');
Cache::forget('enamad:digest:sent');

function digestOutput(array $params): string
{
    [$code, $out] = artisan('payment:digest', $params);
    return $out;
}

/** Pull a "key: N" line out of the dry output. */
function countOf(string $out, string $key): ?int
{
    // \r? because Symfony's command output carries CRLF on Windows, which a
    // bare $ anchor would never match
    return preg_match('/^\s*' . preg_quote($key, '/') . ': (-?\d+)\s*$/m', str_replace("\r", '', $out), $m)
        ? (int) $m[1]
        : null;
}

/**
 * The log mail transport writes bodies quoted-printable encoded (=3D, soft
 * line breaks), so assertions run against the decoded text.
 */
function mailTail(string $path, int $from): string
{
    if (!is_file($path)) {
        return '';
    }
    return quoted_printable_decode((string) substr((string) file_get_contents($path), $from));
}

// ================================================================= scenarios
// The classification matrix runs at --window=24 so only TODAY's records are in
// scope; the window scenarios below then prove the older days behave.
$out = digestOutput(['--dry' => true, '--window' => 24]);

// P1 ---------------------------------------------------------------------
$total = (int) (preg_match('/Total failures: (\d+)/', $out, $m) ? $m[1] : -1);
$total === 16
    ? ok("P1 level filtering: 16 failures counted, the 3 INFO records ignored (total={$total})")
    : bad("P1 level filtering: expected 16, got {$total}\n{$out}");

// P2 ---------------------------------------------------------------------
$expectedGateways = ['ZarinPal' => 4, 'Mellat' => 3, 'Pay.ir' => 2, 'IDPay' => 3, 'Zinal' => 2, 'NextPay' => 1, 'SomeBrand' => 1];
$gatewayErrors = [];
foreach ($expectedGateways as $gw => $want) {
    $got = countOf($out, "gateway {$gw}");
    if ($got !== $want) {
        $gatewayErrors[] = "{$gw} want={$want} got=" . var_export($got, true);
    }
}
$gatewayErrors === []
    ? ok('P2 gateway detection: all 7 gateways detected, context "gateway" key wins')
    : bad('P2 gateway detection: ' . implode('; ', $gatewayErrors));

// P3 ---------------------------------------------------------------------
$expectedKinds = [
    'kind unknown_transaction' => 4,
    'kind verification_failed' => 1,
    'kind amount_mismatch' => 1,
    'kind gateway_error' => 1,
    'kind initiation_failed' => 1,
    'kind refund_failed' => 1,
    'kind cancelled' => 1,
    'kind grant_failed' => 3,
    'kind verification_error' => 1,
    'kind refund_error' => 1,
    'kind other_failure' => 1,
];
$kindErrors = [];
foreach ($expectedKinds as $kind => $want) {
    $got = countOf($out, $kind);
    if ($got !== $want) {
        $kindErrors[] = "'{$kind}' want={$want} got=" . var_export($got, true);
    }
}
$kindErrors === []
    ? ok('P3 kind classification: all 11 buckets correct, unseen wording -> other_failure')
    : bad('P3 kind classification: ' . implode('; ', $kindErrors));

// P4 ---------------------------------------------------------------------
// The discriminating assertions are NOT "the error text appears" - when the
// parser drops continuation lines the raw JSON leaks into the message and the
// error text is still visible by accident. What must hold is that the record
// was SPLIT correctly: no context JSON anywhere in the rendered output, and
// the error promoted into the detail column.
$leakedJson = strpos($out, '"trace"') !== false || strpos($out, '{"authority"') !== false;
$hasAddr = strpos($out, 'Undefined array key "address"') !== false;
$hasCurl = strpos($out, 'cURL error 7: connection refused') !== false;
$splitOk = strpos($out, 'ZarinPal payment verification error (admin): Undefined array key') !== false;
(!$leakedJson && $hasAddr && $hasCurl && $splitOk)
    ? ok('P4 multi-line trace: context decoded, no JSON leaked into the message, error promoted to detail')
    : bad('P4 multi-line trace: leakedJson=' . var_export($leakedJson, true)
        . ' address=' . var_export($hasAddr, true)
        . ' curl=' . var_export($hasCurl, true)
        . ' split=' . var_export($splitOk, true));

// P5 ---------------------------------------------------------------------
strpos($out, 'Pay.ir callback: missing token') !== false
    ? ok('P5 context-less record: "Pay.ir callback: missing token" still counted and labelled')
    : bad('P5 context-less record missing from digest');

// P6 ---------------------------------------------------------------------
$out48 = digestOutput(['--dry' => true, '--window' => 48]);
$t48 = (int) (preg_match('/Total failures: (\d+)/', $out48, $m) ? $m[1] : -1);
$t48 === 17
    ? ok("P6 window=48h: yesterday's 30h-old record is included (total={$t48})")
    : bad("P6 window=48h: expected 17, got {$t48}");

// P7 ---------------------------------------------------------------------
$out72 = digestOutput(['--dry' => true, '--window' => 72]);
$t72 = (int) (preg_match('/Total failures: (\d+)/', $out72, $m) ? $m[1] : -1);
$t72 === 18
    ? ok("P7 window=72h: third daily file reached (total={$t72}, 17 -> 18)")
    : bad("P7 window=72h: expected 18, got {$t72}");

// P8 ---------------------------------------------------------------------
[$c1, $o1] = artisan('payment:digest', ['--window' => 24, '--to' => 'digest-owner@example.test']);
[$c2, $o2] = artisan('payment:digest', ['--window' => 24, '--to' => 'digest-owner@example.test']);
[$c3, $o3] = artisan('payment:digest', ['--window' => 24, '--to' => 'digest-owner@example.test', '--force' => true]);
$sent1 = $c1 === 0 && strpos($o1, 'Payment digest sent to') !== false;
$skipped = $c2 === 0 && strpos($o2, 'No new payment failures') !== false;
$forced = $c3 === 0 && strpos($o3, 'Payment digest sent to') !== false;
($sent1 && $skipped && $forced)
    ? ok('P8 dedup: first run sends, second is suppressed, --force re-sends')
    : bad('P8 dedup: sent1=' . var_export($sent1, true) . ' skipped=' . var_export($skipped, true) . ' forced=' . var_export($forced, true));

// P9 ---------------------------------------------------------------------
$laravelLog = storage_path('logs/laravel.log');
$sizeBefore = is_file($laravelLog) ? filesize($laravelLog) : 0;
artisan('payment:digest', ['--window' => 24, '--to' => 'render-check@example.test', '--force' => true]);
$tail = mailTail($laravelLog, (int) $sizeBefore);
$hasSubject = strpos($tail, 'Payment gateway failures digest (16)') !== false;
$hasByGateway = strpos($tail, 'By gateway') !== false && strpos($tail, 'ZarinPal') !== false;
$hasByKind = strpos($tail, 'By failure kind') !== false && strpos($tail, 'grant_failed') !== false;
$hasTable = strpos($tail, 'Latest entries') !== false;
$hasDetail = strpos($tail, 'Undefined array key') !== false;
$mailLeak = strpos($tail, '"trace"') !== false;
($hasSubject && $hasByGateway && $hasByKind && $hasTable && $hasDetail && !$mailLeak)
    ? ok('P9 mail rendering: subject, gateway/kind breakdowns, entries table and trace detail present')
    : bad('P9 mail rendering incomplete: subject=' . var_export($hasSubject, true)
        . ' byGateway=' . var_export($hasByGateway, true)
        . ' byKind=' . var_export($hasByKind, true)
        . ' table=' . var_export($hasTable, true)
        . ' detail=' . var_export($hasDetail, true)
        . ' jsonLeak=' . var_export($mailLeak, true));

// P10 --------------------------------------------------------------------
$sizeBefore = filesize($laravelLog);
Mail::to('escape-check@example.test')->send(new App\Mail\PaymentDigestMail([
    'total' => 1,
    'window_hours' => 24,
    'by_gateway' => ['ZarinPal' => 1],
    'by_kind' => ['cancelled' => 1],
    'entries' => [[
        'time' => '2026-10-05 10:00:00',
        'level' => 'warning',
        'gateway' => 'ZarinPal',
        'kind' => 'cancelled',
        'message' => 'ZarinPal <b>failed</b>',
        'detail' => 'authority=<script>',
    ]],
    'generated_at' => '2026-10-05 10:00:00',
]));
$tail = mailTail($laravelLog, (int) $sizeBefore);
$escaped = strpos($tail, '&lt;script&gt;') !== false;
$raw = strpos($tail, '<script>') !== false;
$escapedBold = strpos($tail, '&lt;b&gt;failed&lt;/b&gt;') !== false;
($escaped && !$raw && $escapedBold)
    ? ok('P10 blade escaping: injected <script>/<b> in log data is escaped in the mail body')
    : bad('P10 blade escaping: escaped=' . var_export($escaped, true)
        . ' raw=' . var_export($raw, true) . ' bold=' . var_export($escapedBold, true));

// P11 --------------------------------------------------------------------
[$ce, $oe] = artisan('enamad:digest', ['--dry' => true, '--window' => 48]);
$eTotal = (int) (preg_match('/Total failures: (\d+)/', $oe, $m) ? $m[1] : -1);
$eDetail = strpos($oe, 'cURL error 6') !== false;
($ce === 0 && $eTotal === 2 && $eDetail)
    ? ok('P11 enamad regression: enamad:digest still counts 2 and now recovers the trace detail it used to lose')
    : bad("P11 enamad regression: exit={$ce} total={$eTotal} detail=" . var_export($eDetail, true));

// P12 --------------------------------------------------------------------
$emptyLog = $fixtureDir . '/empty-' . date('Y-m-d') . '.log';
file_put_contents($emptyLog, rec($hoursAgo(1)->toDateTimeString(), 'INFO', 'Mellat payment initiation (admin)'));
config(['logging.channels.payment.path' => $fixtureDir . '/empty.log']);
Cache::forget('payment:digest:sent');
$sizeBefore = filesize($laravelLog);
[$c12, $o12] = artisan('payment:digest', ['--window' => 24, '--to' => 'nobody@example.test']);
$tail = mailTail($laravelLog, (int) $sizeBefore);
$noMail = strpos($tail, 'Payment gateway failures digest') === false;
($c12 === 0 && strpos($o12, 'No payment failures') !== false && $noMail)
    ? ok('P12 empty result: INFO-only log produces a clean exit 0 and sends no mail')
    : bad("P12 empty result: exit={$c12} noMail=" . var_export($noMail, true) . "\n{$o12}");

// P13 --------------------------------------------------------------------
// --min-level is a delivered option, so it gets its own regression: the
// fixture holds 3 INFO + 13 WARNING + 6 ERROR records. P12 left the config
// pointing at the empty fixture, so restore the real one first.
config(['logging.channels.payment.path' => $paymentLog]);
$infoOut = digestOutput(['--dry' => true, '--window' => 24, '--min-level' => 'info']);
$errorOut = digestOutput(['--dry' => true, '--window' => 24, '--min-level' => 'error']);
$tInfo = (int) (preg_match('/Total failures: (\d+)/', $infoOut, $m) ? $m[1] : -1);
$tError = (int) (preg_match('/Total failures: (\d+)/', $errorOut, $m) ? $m[1] : -1);
$errorOnly = strpos($errorOut, 'unknown_transaction') === false;
[$cbad, $obad] = artisan('payment:digest', ['--dry' => true, '--min-level' => 'bogus']);
$rejected = $cbad === 1 && strpos($obad, 'Unknown --min-level') !== false;
($tInfo === 19 && $tError === 6 && $errorOnly && $rejected)
    ? ok('P13 --min-level: info=19 (INFO included), error=6 (WARNING excluded), invalid value exits 1')
    : bad("P13 --min-level: info={$tInfo} error={$tError} errorOnly=" . var_export($errorOnly, true)
        . ' rejected=' . var_export($rejected, true));

// --------------------------------------------------------------- cleanup
config(['logging.channels.payment.path' => storage_path('logs/payment.log'), 'logging.channels.enamad.path' => storage_path('logs/enamad.log')]);
Cache::forget('payment:digest:sent');
Cache::forget('enamad:digest:sent');

echo "\nPASS: $passes  FAIL: $failures\n";
exit($failures === 0 ? 0 : 1);