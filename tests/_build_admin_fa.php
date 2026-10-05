<?php
/**
 * One-shot generator: builds resources/lang/admin_fa.json from the
 * English->Persian maps in tests/lang_maps/admin_fa_{a,b,c}.php.
 *
 * Same shape and same guards as tests/_build_user_fa.php: the key set must
 * match admin_en.json exactly and every value must contain Persian script.
 *
 * Keys beyond admin_en.json's set come from one reviewed list,
 * lang_maps/admin_fa_view_gaps.php: strings the blades call with __('...') that
 * never made it into admin_en.json at all (payment-gateway settings, Enamad,
 * watermark, menu builder). Found by tests/lang_coverage_audit.php.
 *
 * Usage: php tests/_build_admin_fa.php
 */

$enPath = __DIR__ . '/../resources/lang/admin_en.json';
$outPath = __DIR__ . '/../resources/lang/admin_fa.json';

$en = json_decode(file_get_contents($enPath), true);
if (!is_array($en)) {
    fwrite(STDERR, "could not read $enPath\n");
    exit(1);
}

$t = [];
foreach (['a', 'b', 'c', 'd'] as $part) {
    $file = __DIR__ . '/lang_maps/admin_fa_' . $part . '.php';
    $map = require $file;
    foreach ($map as $k => $v) {
        if (isset($t[$k]) && $t[$k] !== $v) {
            fwrite(STDERR, "CONFLICT: key " . json_encode($k) . " mapped twice with different values\n");
            exit(1);
        }
        $t[$k] = $v;
    }
}

// Keys beyond admin_en.json, from the reviewed gap list.
$viewGaps = require __DIR__ . '/lang_maps/admin_fa_view_gaps.php';

$missing = array_diff(array_keys($en), array_keys($t));
$extra = array_diff(array_keys($t), array_keys($en));
if ($missing || $extra) {
    fwrite(STDERR, "KEY MISMATCH\n");
    if ($missing) {
        fwrite(STDERR, "missing from map:\n  " . implode("\n  ", $missing) . "\n");
    }
    if ($extra) {
        fwrite(STDERR, "not in admin_en.json:\n  " . implode("\n  ", $extra) . "\n");
    }
    exit(1);
}

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
        fwrite(STDERR, 'DUPLICATE: gap key ' . json_encode($k) . " is also in admin_en.json\n");
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
echo 'wrote admin_fa.json with ' . count($out) . " keys\n";