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

    // The seal partial and every footer that renders it. The bundle replaces
    // resources/views wholesale, so a footer shipped without the partial (or
    // the reverse) leaves the customer's site throwing "View [partials.enamad]
    // not found" on every page of that template.
    ['enamad seal partial', 'resources/views/partials/enamad.blade.php', 'enamad_variant'],
    ['front footer (floating widget)', 'resources/views/front/partials/footer.blade.php', 'partials.enamad'],
    ['admin footer', 'resources/views/admin/partials/footer.blade.php', 'partials.enamad'],
    ['tenant dashboard footer', 'resources/views/user/partials/footer.blade.php', 'partials.enamad'],
    ['tenant profile layout footer (default theme)', 'resources/views/user/profile/layout.blade.php', 'partials.enamad'],
    ['tenant profile1 base layout footer (themes 1-2)', 'resources/views/user/profile1/layout.blade.php', 'partials.enamad'],
    ['theme3 footer (the theme that shipped without one)', 'resources/views/user/profile1/theme3/layout.blade.php', 'partials.enamad'],
    ['theme4 footer', 'resources/views/user/profile1/theme4/layout.blade.php', 'partials.enamad'],
    ['theme5 footer', 'resources/views/user/profile1/theme5/layout.blade.php', 'partials.enamad'],
    ['theme6 footer', 'resources/views/user/profile1/theme6/layout.blade.php', 'partials.enamad'],
    ['theme7 footer', 'resources/views/user/profile1/theme7/layout.blade.php', 'partials.enamad'],
    ['theme8 footer', 'resources/views/user/profile1/theme8/layout.blade.php', 'partials.enamad'],
    ['theme9 footer', 'resources/views/user/profile1/theme9/layout.blade.php', 'partials.enamad'],
    ['theme10 footer', 'resources/views/user/profile1/theme10/layout.blade.php', 'partials.enamad'],
    ['theme11 footer', 'resources/views/user/profile1/theme11/layout.blade.php', 'partials.enamad'],
    ['theme12 footer', 'resources/views/user/profile1/theme12/layout.blade.php', 'partials.enamad'],

    // The watermark partial. Thirteen footers include it, so its absence
    // is not a missing feature but a blank page: every one of those
    // templates would fatal on "View [partials.watermark] not found".
    ['watermark partial', 'resources/views/partials/watermark.blade.php', 'watermark'],

    // All 24 Iranian gateway routes live in this file. Without it the
    // bundle ships a routes/web.php that reaches none of them.
    ['Iranian gateway routes', 'routes/payment_gateways.php', 'mellat/success'],
];

// The six Iranian gateways, each with a controller for the public checkout
// and one for the tenant's own purchase, plus the two settings controllers
// that own the admin/user update endpoints. Those two were present in the
// bundle but stale - they carried none of the update methods, so saving
// Mellat settings after an update would have hit a missing method.
$iranian = ['ZarinPal', 'Zibal', 'IdPay', 'NextPay', 'PayIr', 'Mellat'];
foreach (['app/Http/Controllers/Payment', 'app/Http/Controllers/User/Payment'] as $dir) {
    foreach ($iranian as $gateway) {
        $required[] = ["{$gateway} controller", "{$dir}/{$gateway}Controller.php", 'class ' . $gateway . 'Controller'];
    }
}
foreach ([
    ['admin gateway settings', 'app/Http/Controllers/Admin/GatewayController.php', 'function mellatUpdate'],
    ['user gateway settings', 'app/Http/Controllers/User/GatewayController.php', 'function mellatUpdate'],
    ['enamad HTTP controller', 'app/Http/Controllers/EnamadController.php', 'class EnamadController'],
    ['midtrans bank notify', 'app/Http/Controllers/MidtransBankNotifyController.php', 'class MidtransBankNotifyController'],
] as $row) {
    $required[] = $row;
}

// The seal partial is only renderable if every footer that includes it also
// lands, and vice versa: the replace is all-or-nothing per file, so a footer
// in the bundle whose partial is not would fatal on a live customer site.
$footerFiles = array_values(array_filter(
    array_column($required, 1),
    static fn(string $rel): bool => str_ends_with($rel, 'layout.blade.php')
        || in_array($rel, ['resources/views/front/partials/footer.blade.php', 'resources/views/admin/partials/footer.blade.php', 'resources/views/user/partials/footer.blade.php'], true)
));
$sealPartialSrc = (string) file_get_contents($root . '/resources/views/partials/enamad.blade.php');
foreach ($footerFiles as $rel) {
    $src = (string) file_get_contents($root . '/' . $rel);
    if (strpos($src, "partials.enamad") === false) {
        bad("footer lost its seal include: $rel - the partial would render nothing");
        continue;
    }
    if (strpos($src, "'enamad_variant' => 'footer'") === false
        && $rel !== 'resources/views/front/partials/footer.blade.php') {
        bad("footer does not request the footer variant: $rel");
        continue;
    }
    ok("footer requests the seal correctly: $rel");
}
unset($sealPartialSrc);

// ---------------------------------------------------------------- closure
// Hand-listing the files above only covers what someone remembered to
// list. The bundle's own routes and blades can be asked directly: after
// the delete-then-replace, does everything they reference still exist?
// A route whose controller the bundle dropped, or a blade that includes a
// partial the bundle dropped, is a 500 on a live customer site.
echo "\n== closure: the bundle references only what it ships ==\n";

$updaterRoutes = glob($root . '/updater/routes/*.php') ?: [];
$controllerRefs = [];
foreach ($updaterRoutes as $routeFile) {
    $src = (string) file_get_contents($routeFile);
    if (preg_match_all("/['\"]([A-Za-z0-9_\\\\]+Controller)@([A-Za-z0-9_]+)['\"]/", $src, $m, PREG_SET_ORDER)) {
        foreach ($m as $hit) {
            $class = $hit[1];
            $pos = strpos($class, '\\');
            $ns = $pos !== false ? substr($class, 0, $pos) : '';
            $short = $pos !== false ? substr($class, $pos + 1) : $class;
            $controllerRefs[$ns . '\\' . $short] = $ns === '' ? $short . '.php' : $ns . '/' . $short . '.php';
        }
    }
}
$missingControllers = [];
foreach ($controllerRefs as $class => $rel) {
    if (!is_file($root . '/updater/app/Http/Controllers/' . $rel)) {
        $missingControllers[$class] = $rel;
    }
}
$missingControllers === []
    ? ok('every controller the bundle routes reference ships with the bundle (' . count($controllerRefs) . ' refs)')
    : bad('controllers the bundle routes reference but does not ship: ' . implode(', ', array_keys($missingControllers)));

// Views: @include / @includeIf / @extends, resolving dot notation to a path.
// A package view (ns::name) resolves through vendor/, not this tree.
$viewRoot = $root . '/updater/resources/views';
$viewRefs = [];
$viewIt = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewRoot));
foreach ($viewIt as $file) {
    if ($file->isDir() || substr($file->getFilename(), -10) !== '.blade.php') {
        continue;
    }
    $src = (string) file_get_contents($file->getPathname());
    if (preg_match_all("/@(?:include|includeIf|includeWhen|extends)\s*\(\s*'([^']+)'/", $src, $m, PREG_SET_ORDER)) {
        foreach ($m as $hit) {
            if (strpos($hit[1], '::') !== false) {
                continue;
            }
            $viewRefs[str_replace('.', '/', $hit[1]) . '.blade.php'] = true;
        }
    }
}
// Only a view the app ships and the bundle dropped is a gap. A view neither
// ships is a dead reference that predates this check.
$missingViews = [];
foreach (array_keys($viewRefs) as $rel) {
    if (!is_file($viewRoot . '/' . $rel) && is_file($root . '/resources/views/' . $rel)) {
        $missingViews[] = $rel;
    }
}
$missingViews === []
    ? ok('every view the bundle blades include ships with the bundle (' . count($viewRefs) . ' refs)')
    : bad('views the bundle blades include but do not ship: ' . implode(', ', $missingViews));

// Migrations are invisible to both guards above - nothing in a route or a
// blade names them - yet the replace wipes database/migrations/ wholesale.
// The API feature writes users.service_type/lat/lng and basic_settings.api_key,
// and ApiIntegrationController reads Province::all() and City::where(), so a
// bundle that never creates those columns leaves the feature broken on any
// install made from it. Compared by the table each migration creates, not by
// filename: the bundle legitimately carries the same tables under its own
// dates.
$tablesIn = function (string $dir): array {
    $out = [];
    foreach (glob($dir . '/*.php') ?: [] as $file) {
        $body = (string) file_get_contents($file);
        if (preg_match_all("/Schema::create\(\s*'([a-z_]+)'/", $body, $m)) {
            foreach ($m[1] as $table) {
                $out[$table] = true;
            }
        }
    }
    return $out;
};
$appTables = $tablesIn($root . '/database/migrations');
$bundleTables = $tablesIn($root . '/updater/database/migrations');
$uncreatableTables = array_diff_key($appTables, $bundleTables);
$uncreatableTables === []
    ? ok('every table the app migrates, the bundle can also create (' . count($appTables) . ' tables)')
    : bad('tables the app migrates but the bundle cannot create: ' . implode(', ', array_keys($uncreatableTables)));

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