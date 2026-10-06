<?php
/**
 * Enamad trust-seal footer harness.
 *
 * The seal used to exist as a partial that only the front site footer
 * included, and only as a fixed-position corner badge - which is the wrong
 * shape for a tenant's single-page portfolio, where it covers content and
 * ignores the theme. It is now one in-flow seal that every footer ships:
 *
 *   1. Every blade containing a <footer> block includes partials.enamad.
 *      A footer that forgot the include renders a page with no seal and no
 *      error, so nothing else would notice.
 *   2. The seal is in-flow (no position:fixed), links to the verify page,
 *      and carries enamad_code - Enamad resolves the seal from the code as
 *      well as the id, and omitting it returns a generic placeholder after
 *      a renewal.
 *   3. Nothing renders when the seal is off (status 0, no id, or
 *      enamad.footer.enabled = false).
 *   4. Nothing floats. The badge variant is gone, not merely unused: a
 *      fixed overlay on a single-page portfolio covers content, and a site
 *      owner who cannot place their own seal removes it.
 *   5. updater/ carries the partial AND every footer that includes it. The
 *      bundle replaces resources/views wholesale, so a footer shipped without
 *      its partial is a fatal on a live customer site, not a no-op.
 *
 * Usage: php tests/enamad_footer_harness.php
 * Exit code 0 = every check passed.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$root = dirname(__DIR__);
$passed = 0;
$failed = 0;

function ok(string $label): void
{
    global $passed;
    $passed++;
    echo "  PASS  $label\n";
}
function bad(string $label, string $detail = ''): void
{
    global $failed;
    $failed++;
    echo "  FAIL  $label" . ($detail !== '' ? " - $detail" : '') . "\n";
}
function check(bool $c, string $label, string $detail = ''): void
{
    $c ? ok($label) : bad($label, $detail);
}

// ---------------------------------------------------------------- 1. coverage
echo "== 1. every footer in the codebase renders the seal ==\n";
$footers = [];
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/resources/views'));
foreach ($rii as $file) {
    if ($file->isDir() || substr($file->getFilename(), -10) !== '.blade.php') {
        continue;
    }
    $src = (string) file_get_contents($file->getPathname());
    if (strpos($src, '<footer') === false) {
        continue;
    }
    $rel = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
    // The seal partial itself documents that it renders "inside the <footer>
    // block", so the scan matches its own comment. It is the provider, not a
    // consumer.
    if ($rel === 'resources/views/partials/enamad.blade.php') {
        continue;
    }
    $footers[] = $rel;
}
sort($footers);
check(count($footers) > 0, 'the scan actually found footers', count($footers) . ' found');
foreach ($footers as $rel) {
    $src = (string) file_get_contents($root . '/' . $rel);
    if (strpos($src, 'partials.enamad') === false) {
        bad("footer includes the seal: $rel");
        continue;
    }
    // Every footer, including the front site, now takes the same in-flow
    // shape. There is no floating variant left to opt out of, so a footer
    // that includes the partial at all is correct.
    ok("footer includes the seal: $rel");
}

// The tenant themes are the reason this exists: every one of them must carry
// it, or "add enamad to all templates" is only true for some of them.
$themes = glob($root . '/resources/views/user/profile1/theme*', GLOB_ONLYDIR);
$themeFooters = 0;
foreach ($themes as $dir) {
    $layout = $dir . '/layout.blade.php';
    if (!is_file($layout)) {
        continue;
    }
    $src = (string) file_get_contents($layout);
    if (strpos($src, '<footer') === false) {
        // theme3 ships no footer at all; recorded rather than silently skipped.
        echo "  NOTE  theme without any footer (seal cannot be placed): "
            . basename($dir) . "\n";
        continue;
    }
    if (strpos($src, 'partials.enamad') === false) {
        bad('theme footer includes the seal: ' . basename($dir));
        continue;
    }
    $themeFooters++;
    ok('theme footer includes the seal: ' . basename($dir));
}
// themes 1 and 2 share profile1/layout, so 9 theme directories carry their
// own footer block; theme3 ships none at all.
check($themeFooters >= 9, 'every tenant theme that has a footer carries the seal', "got {$themeFooters}");

// ------------------------------------------------------------ 2. the rendering
echo "\n== 2. the seal renders correctly ==\n";
$render = function (array $data): string {
    return (string) view('partials.enamad', $data)->render();
};

$html = $render([
    'enamad_status' => 1,
    'enamad_site_id' => 'SITE-1',
    'enamad_code' => 'CODE-1',
    'enamad_logo_type' => 'dark',
]);
check(strpos($html, 'enamad-footer-seal') !== false, 'the seal container is emitted');
check(strpos($html, 'position: fixed') === false, 'footer variant is in-flow, not a floating badge');
check(strpos($html, 'trustseal.enamad.ir/verify?id=SITE-1') !== false, 'seal links to the verify page');
check(strpos($html, 'logo.aspx?id=SITE-1&amp;Code=CODE-1&amp;type=dark') !== false,
    'logo URL carries the enamad code and logo type');
check(strpos($html, 'enamad-footer-seal-title') !== false, 'footer variant shows the caption');
check(strpos($html, '<script') === false, 'footer variant ships no widget script (the image is enough)');

// URL-encoding matters: a site id or code carrying & or space would otherwise
// break the query string.
$html = $render([
    'enamad_status' => 1,
    'enamad_site_id' => 'A B&C',
    'enamad_code' => 'D/E',
    'enamad_logo_type' => 'auto',
]);
check(strpos($html, 'id=A%20B%26C') !== false, 'site id is url-encoded');
check(strpos($html, 'Code=D%2FE') !== false, 'code is url-encoded');

// ------------------------------------------------------------- 3. nothing when off
echo "\n== 3. nothing renders when the seal is off ==\n";
check(trim($render([
    'enamad_status' => 0,
    'enamad_site_id' => 'SITE-1', 'enamad_code' => 'C', 'enamad_logo_type' => 'auto',
])) === '', 'status 0 renders nothing');
check(trim($render([
    'enamad_status' => 1,
    'enamad_site_id' => '', 'enamad_code' => '', 'enamad_logo_type' => 'auto',
])) === '', 'an enabled seal with no site id renders nothing');
config(['enamad.footer.enabled' => false]);
check(trim($render([
    'enamad_status' => 1,
    'enamad_site_id' => 'SITE-1', 'enamad_code' => '', 'enamad_logo_type' => 'auto',
])) === '', 'enamad.footer.enabled = false hides the seal everywhere');
config(['enamad.footer.enabled' => true]);

// ------------------------------------------------- 4. the float variant is intact
echo "\n== 4. the seal is footer-only, never a floating badge ==\n";
// Asserts absence, not preference. The badge variant was deleted, so there
// is nothing left that a caller could ask for - and the old argument name is
// passed on purpose to prove a stale caller cannot resurrect it.
$allRendered = '';
foreach ([
    ['camelCase', ['enamadStatus' => 1, 'enamadSiteId' => 'SITE-1', 'enamadCode' => 'CODE-1', 'enamadLogoType' => 'auto']],
    ['snake_case', ['enamad_status' => 1, 'enamad_site_id' => 'SITE-1', 'enamad_code' => 'CODE-1', 'enamad_logo_type' => 'auto']],
    ['stale variant arg', ['enamad_variant' => 'widget', 'enamadStatus' => 1, 'enamadSiteId' => 'SITE-1', 'enamadCode' => 'CODE-1', 'enamadLogoType' => 'auto']],
] as [$label, $data]) {
    $out = $render($data);
    $allRendered .= $out;
    $floats = strpos($out, 'position: fixed') !== false
        || strpos($out, 'position:fixed') !== false
        || strpos($out, 'enamad-badge-container') !== false;
    check(! $floats, "no floating badge ($label)");
    check(strpos($out, 'enamad-footer-seal') !== false, "the seal still renders ($label)");
}
check(strpos($allRendered, 'trustseal.js') === false, 'no trust-seal widget script anywhere');
check(strpos($allRendered, 'EnamadTrustSeal') === false, 'no trust-seal JS init anywhere');

// --------------------------------------------------------------- 5. updater bundle
echo "\n== 5. the updater bundle carries the partial and every footer ==\n";
$mirror = [
    'config/enamad.php',
    'resources/views/partials/enamad.blade.php',
];
foreach ($footers as $rel) {
    $mirror[] = $rel;
}
foreach ($mirror as $rel) {
    $appFile = $root . '/' . $rel;
    $updFile = $root . '/updater/' . $rel;
    if (!is_file($appFile)) {
        bad("app copy exists: $rel");
        continue;
    }
    if (!is_file($updFile)) {
        bad("updater ships it: $rel", 'MISSING - a customer update would delete it');
        continue;
    }
    hash_file('sha256', $appFile) === hash_file('sha256', $updFile)
        ? ok("byte-identical to app: $rel")
        : bad("byte-identical to app: $rel", 'DRIFTED');
}

// --------------------------------------------------------------- 6. negative control
echo "\n== 6. negative control - the coverage check can actually fail ==\n";
$tmp = sys_get_temp_dir() . '/enamad_footer_' . getmypid() . '.blade.php';
$victim = $root . '/resources/views/admin/partials/footer.blade.php';
$original = (string) file_get_contents($victim);
file_put_contents($tmp, $original);
try {
    $stripped = str_replace("@include('partials.enamad', [", '@include(\'partials.does_not_exist\', [', $original);
    file_put_contents($victim, $stripped);
    $scan = strpos((string) file_get_contents($victim), 'partials.enamad') === false;
    check($scan, 'a footer that stops including the seal is detected by the coverage check');
} finally {
    file_put_contents($victim, $original);
    @unlink($tmp);
}
check(strpos((string) file_get_contents($victim), 'partials.enamad') !== false,
    'the footer was restored after the negative control');

// ------------------------------------------- 7. the authenticated footers render
// The dashboard footers sit behind a session, so no HTTP probe can reach them
// without logging in. Rendering them through the real engine with a stub
// settings row is the next best thing, and it is the only check that would
// catch a missing variable (an undefined $userBs fatals the whole page) or a
// footer that includes the seal but renders it outside the <footer> block.
echo "\n== 7. the session-gated footers render the seal in place ==\n";
$stubSettings = function (array $overrides = []): stdClass {
    return (object) array_merge([
        'enamad_status'    => 1,
        'enamad_site_id'   => 'SITE-1',
        'enamad_code'      => 'CODE-1',
        'enamad_logo_type' => 'auto',
        'copyright_text'   => 'copyright',
        'language_id'      => 1,
    ], $overrides);
};
// The admin footer resolves the active language row from the session or the
// is_default flag before it can print anything.
\App\Models\Language::where('is_default', 1)->first()
    ?: \App\Models\Language::create(['code' => 'en', 'name' => 'English', 'is_default' => 1]);

foreach ([
    // $userBs = the tenant's own seal, $bs = the site copyright line.
    'user/partials/footer'  => ['userBs' => $stubSettings(), 'bs' => $stubSettings()],
    // $bs = the site's own seal.
    'admin/partials/footer' => ['bs' => $stubSettings()],
] as $view => $vars) {
    $rendered = '';
    try {
        $rendered = (string) view($view, $vars)->render();
    } catch (Throwable $e) {
        bad("{$view} renders", $e->getMessage());
        continue;
    }
    ok("{$view} renders");

    $inFooter = preg_match('/<footer\b[^>]*>(.*)<\/footer>/is', $rendered, $m)
        && strpos($m[1], 'id=SITE-1') !== false;
    check($inFooter, "{$view} puts the seal inside its own <footer>");
    check(strpos($rendered, 'enamad-footer-seal') !== false, "{$view} uses the in-flow seal, not the badge");
    check(strpos($rendered, 'enamad-badge-container') === false && strpos($rendered, 'trustseal.js') === false,
        "{$view} ships no floating widget script");
    check(strpos($rendered, 'نماد اعتماد الکترونیک') !== false, "{$view} shows the Persian caption");
}

// And with the seal off, the same footers must print no seal at all rather
// than an empty container.
foreach (['user/partials/footer' => ['userBs' => 'x', 'bs' => 'y'], 'admin/partials/footer' => ['bs' => 'x']] as $view => $which) {
    $off = array_map(function () use ($stubSettings) {
        return $stubSettings(['enamad_status' => 0, 'enamad_site_id' => '']);
    }, $which);
    $rendered = (string) view($view, $off)->render();
    check(strpos($rendered, 'enamad-footer-seal') === false && strpos($rendered, 'trustseal.enamad.ir') === false,
        "{$view} renders no seal when it is turned off");
}

echo "\n" . str_repeat('=', 52) . "\n";
echo "ENAMAD FOOTER: $passed passed, $failed failed\n";
echo str_repeat('=', 52) . "\n";
exit($failed === 0 ? 0 : 1);