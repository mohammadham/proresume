<?php
/**
 * Closure check for the Enamad/payment digest feature inside updater/ (project
 * tooling).
 *
 * The updater does NOT merge files: upversion() calls delete_directory() on
 * app/, config/, resources/views/, routes/ and database/migrations/ and then
 * copies updater/<same path> over the top. Whatever is missing from the bundle
 * is therefore destroyed on the installing customer's site, and a half-shipped
 * feature is worse than an unshipped one - EnamadDigest without
 * FailureLogParser or config('enamad') is a fatal, not a no-op.
 *
 * This script simulates that replace against a throwaway copy of the bundle and
 * asserts that every class/config/view the shipped commands depend on actually
 * lands. It never touches the live application: the "customer install" is a
 * temp directory, so the running app's app/ and config/ are not modified.
 *
 * Usage: php tests/updater_bundle_check.php
 * Exit code 0 = the bundle is self-consistent.
 */

error_reporting(E_ALL);

$root = dirname(__DIR__);
chdir($root);

$passes = 0;
$failures = 0;

function ok(string $m): void
{
    global $passes;
    $passes++;
    echo "PASS $m\n";
}

function bad(string $m): void
{
    global $failures;
    $failures++;
    echo "FAIL $m\n";
}

// ------------------------------------------------- simulate the replace
$sandbox = sys_get_temp_dir() . '/updater_check_' . getmypid();
if (!is_dir($sandbox)) {
    mkdir($sandbox, 0777, true);
}
register_shutdown_function(function () use ($sandbox) {
    $rm = function (string $dir) use (&$rm) {
        if (!is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) as $f) {
            if ($f === '.' || $f === '..') {
                continue;
            }
            $p = $dir . '/' . $f;
            is_dir($p) ? $rm($p) : @unlink($p);
        }
        @rmdir($dir);
    };
    $rm($sandbox);
});

/** Mirror of UpdateController::recurse_copy(). */
function recurseCopy(string $src, string $dst): void
{
    @mkdir($dst, 0775, true);
    foreach (scandir($src) as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $from = $src . '/' . $entry;
        $to = $dst . '/' . $entry;
        is_dir($from) ? recurseCopy($from, $to) : copy($from, $to);
    }
}

// the five folders upversion() replaces
$replaced = ['app', 'config', 'resources/views', 'routes', 'database/migrations'];
foreach ($replaced as $path) {
    $target = $sandbox . '/' . $path;
    recurseCopy($root . '/updater/' . $path, $target);
}

// ------------------------------------------------------ what must land
// Each entry: [description, updater-relative path, required content marker]
$required = [
    ['VerifyEnamad command', 'app/Console/Commands/VerifyEnamad.php', 'class VerifyEnamad'],
    ['EnamadDigest command', 'app/Console/Commands/EnamadDigest.php', 'class EnamadDigest'],
    ['PaymentDigest command', 'app/Console/Commands/PaymentDigest.php', 'class PaymentDigest'],
    ['shared log parser (EnamadDigest + PaymentDigest)', 'app/Services/FailureLogParser.php', 'class FailureLogParser'],
    ['EnamadDigestMail mailable', 'app/Mail/EnamadDigestMail.php', 'class EnamadDigestMail'],
    ['PaymentDigestMail mailable', 'app/Mail/PaymentDigestMail.php', 'class PaymentDigestMail'],
    ['enamad config (both digests + verify read it)', 'config/enamad.php', "'digest_recipient'"],
    ['payment config (PaymentDigest reads it)', 'config/payment.php', "'digest_recipient'"],
    ['enamad digest mail view', 'resources/views/emails/enamad-digest.blade.php', 'verification failures digest'],
    ['payment digest mail view', 'resources/views/emails/payment-digest.blade.php', 'Payment gateway failures digest'],
    ['basic_settings enamad columns migration', 'database/migrations/2026_01_15_000007_add_enamad_fields_to_basic_settings_table.php', 'enamad_site_id'],
];

foreach ($required as [$desc, $rel, $marker]) {
    $file = $sandbox . '/' . $rel;
    if (!is_file($file)) {
        bad("MISSING after replace: $desc ($rel) - the customer's site would lose it");
        continue;
    }
    if (strpos((string) file_get_contents($file), $marker) === false) {
        bad("PRESENT but wrong content: $desc ($rel) lacks '$marker'");
        continue;
    }
    ok("lands intact: $desc");
}

// the scheduler must actually invoke the three commands, otherwise the bundle
// ships code that nothing ever runs
$kernel = $sandbox . '/app/Console/Kernel.php';
$kernelSrc = is_file($kernel) ? (string) file_get_contents($kernel) : '';
foreach (['enamad:verify', 'enamad:digest', 'payment:digest'] as $cmd) {
    strpos($kernelSrc, "'" . $cmd . "'") !== false
        ? ok("scheduled in the shipped Kernel: $cmd")
        : bad("NOT scheduled: $cmd - the feature would ship inert");
}

// cross-check: the updater copies must be byte-identical to the app copies,
// otherwise the two drift apart again on the next edit
foreach ($required as [$desc, $rel, $marker]) {
    if (strpos($rel, 'app/Console/Kernel.php') !== false) {
        continue;
    }
    $appFile = $root . '/' . $rel;
    $updFile = $root . '/updater/' . $rel;
    if (!is_file($appFile)) {
        continue;
    }
    hash_file('sha256', $appFile) === hash_file('sha256', $updFile)
        ? ok("byte-identical to app: $rel")
        : bad("DRIFTED from app: $rel");
}

echo "\nPASS: $passes  FAIL: $failures\n";
exit($failures === 0 ? 0 : 1);