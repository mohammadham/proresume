<?php
/**
 * One-shot generator: builds resources/lang/user_fa.json from the
 * English->Persian maps in tests/lang_maps/user_fa_{a,b,c}.php.
 *
 * The maps are separate only to keep each file reviewable; this script merges
 * them and refuses to write anything unless the resulting key set exactly
 * matches user_en.json and every value actually contains Persian script. A
 * dropped or typo'd key silently falls back to English at runtime, which is
 * the failure this guard exists to prevent.
 *
 * Keys beyond user_en.json's set come from one reviewed list,
 * lang_maps/user_fa_view_gaps.php: strings the blades call with __('...') that
 * never made it into user_en.json at all. Found by
 * tests/lang_coverage_audit.php.
 *
 * Usage: php tests/_build_user_fa.php
 */

$enPath = __DIR__ . '/../resources/lang/user_en.json';
$outPath = __DIR__ . '/../resources/lang/user_fa.json';

$en = json_decode(file_get_contents($enPath), true);
if (!is_array($en)) {
    fwrite(STDERR, "could not read $enPath\n");
    exit(1);
}

$t = [];
foreach (['a', 'b', 'c'] as $part) {
    $file = __DIR__ . '/lang_maps/user_fa_' . $part . '.php';
    $map = require $file;
    foreach ($map as $k => $v) {
        if (isset($t[$k]) && $t[$k] !== $v) {
            fwrite(STDERR, "CONFLICT: key " . json_encode($k) . " mapped twice with different values\n");
            exit(1);
        }
        $t[$k] = $v;
    }
}

// Keys beyond user_en.json, from the reviewed gap list.
$viewGaps = require __DIR__ . '/lang_maps/user_fa_view_gaps.php';

$missing = array_diff(array_keys($en), array_keys($t));
$extra = array_diff(array_keys($t), array_keys($en));
if ($missing || $extra) {
    fwrite(STDERR, "KEY MISMATCH\n");
    if ($missing) {
        fwrite(STDERR, "missing from map:\n  " . implode("\n  ", $missing) . "\n");
    }
    if ($extra) {
        fwrite(STDERR, "not in user_en.json:\n  " . implode("\n  ", $extra) . "\n");
    }
    exit(1);
}

// A value with no Persian character would render as English on a Persian page.
$hasPersian = static function ($s) {
    return (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $s);
};
$untranslated = [];
foreach ($en as $k => $v) {
    if (!$hasPersian($t[$k])) {
        $untranslated[] = $k;
    }
}
foreach ($viewGaps as $k => $v) {
    if (isset($t[$k])) {
        fwrite(STDERR, 'DUPLICATE: gap key ' . json_encode($k) . " is also in user_en.json\n");
        exit(1);
    }
    if (!$hasPersian($v)) {
        $untranslated[] = $k;
    }
}
if ($untranslated) {
    fwrite(STDERR, "NO PERSIAN SCRIPT in:\n  " . implode("\n  ", $untranslated) . "\n");
    exit(1);
}

$out = [];
foreach ($en as $k => $v) {
    $out[$k] = $t[$k];
}
foreach ($viewGaps as $k => $v) {
    $out[$k] = $v;
}
file_put_contents(
    $outPath,
    json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n"
);
echo 'wrote user_fa.json with ' . count($out) . " keys\n";