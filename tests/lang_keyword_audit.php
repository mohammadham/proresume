<?php
/**
 * Audit of the tenant keyword dictionary lookups in the blades.
 *
 * The tenant site reads its labels out of user_languages.keywords (via
 * getUserLanguageKeywords()). That dictionary is keyed by IDENTIFIER
 * ("My_Resume"), but a number of blades index it by the English label instead
 * ("My Resume"). Those lookups can never resolve, so they normally stay hidden
 * behind a `$home_text->... ??` short-circuit - and the moment a tenant's
 * content row for the active language is missing (the normal state for a
 * language that was just added, e.g. Persian) PHP raises "Undefined array key"
 * and the tenant's home page 500s.
 *
 * Only UNGUARDED lookups matter, and the direction of `??` is the whole
 * story:
 *
 *   $keywords['x'] ?? 'fallback'    safe   - `??` short-circuits the subscript
 *   $home_text->y ?? $keywords['x'] FATAL  - the subscript is the fallback
 *                                             operand and is always evaluated
 *
 * Usage: php tests/lang_keyword_audit.php
 * Exit code 0 = no lookup can throw.
 */

$root = dirname(__DIR__);
$viewDirs = ['resources/views/user', 'resources/views/user-front'];
$map = require __DIR__ . '/lang_maps/fa_customer_keywords.php';

// The dictionary getUserLanguageKeywords() hands to the blades is the stored
// one plus a label alias for every identifier, so "My_Resume" is also reachable
// as "My Resume". Model that here, otherwise every correctly-working label-style
// lookup is reported as a defect.
$helperSrc = (string) file_get_contents($root . '/app/Http/Helpers/Helper.php');
$aliasesPublished = strpos($helperSrc, "str_replace('_', ' ', \$key)") !== false;
if (!$aliasesPublished) {
    fwrite(STDERR, "getUserLanguageKeywords() no longer publishes label aliases;\n"
        . "every \$keywords['My Resume']-style lookup in the tenant themes will 500\n");
    exit(1);
}

$runtimeKeys = [];
foreach ($map as $key => $value) {
    $runtimeKeys[$key] = $value;
    if (strpos($key, '_') !== false) {
        $runtimeKeys[str_replace('_', ' ', $key)] = $value;
    }
}

/**
 * A lookup is guarded when `??` FOLLOWS it, i.e. it is the left operand of the
 * null-coalescing operator. (The constant cannot be named PROTECTED - that is
 * a PHP reserved word and the file will not even parse.)
 */
const LOOKUP = '/\$keywords\s*\[\s*[\'"]([^\'"]+)[\'"]\s*\]\s*\?\?/';
const LOOKUP_ALL = '/\$keywords\s*\[\s*[\'"]([^\'"]+)[\'"]\s*\]/';

$files = 0;
$protectedCount = 0;
$okCount = 0;
$fatal = [];
$unguardedFiles = [];

foreach ($viewDirs as $dir) {
    $path = $root . '/' . $dir;
    if (!is_dir($path)) {
        continue;
    }
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
    foreach ($rii as $file) {
        if ($file->isDir() || substr($file->getFilename(), -10) !== '.blade.php') {
            continue;
        }
        $files++;
        $src = (string) file_get_contents($file->getPathname());

        preg_match_all(LOOKUP, $src, $pm);
        $guarded = $pm[1];

        if (!preg_match_all(LOOKUP_ALL, $src, $am)) {
            continue;
        }

        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
        foreach ($am[1] as $key) {
            if (in_array($key, $guarded, true)) {
                $protectedCount++;
                continue;
            }
            // Both the identifier and its published label alias resolve.
            if (array_key_exists($key, $runtimeKeys)) {
                $okCount++;
                $unguardedFiles[$relative] = ($unguardedFiles[$relative] ?? 0) + 1;
                continue;
            }
            $fatal[] = [
                'file' => $relative,
                'key' => $key,
                'identifier' => str_replace(' ', '_', $key),
                'inMap' => array_key_exists($key, $map),
            ];
        }
    }
}

echo "== tenant keyword dictionary audit ==\n";
echo "blade files scanned:          {$files}\n";
echo "lookups resolved by key:      {$okCount}\n";
echo "lookups guarded by '??':      {$protectedCount}\n";
echo "lookups that can throw:       " . count($fatal) . "\n";

if ($unguardedFiles) {
    echo "\nfiles holding an unguarded lookup (must all be tenant-composer views,\n";
    echo "which receive the aliased dictionary, not the empty user_keywords one):\n";
    ksort($unguardedFiles);
    foreach ($unguardedFiles as $file => $n) {
        $isTenant = (bool) preg_match('#^resources/views/(user/profile|user/profile1|user/profile-common|user-front)/#', $file);
        echo '  ' . ($isTenant ? 'tenant  ' : 'DASHBOARD') . " {$file} ({$n})\n";
    }
}

foreach ($fatal as $b) {
    printf(
        "  %s: \$keywords['%s']  -> no such key (identifier would be '%s': %s)\n",
        $b['file'],
        $b['key'],
        $b['identifier'],
        $b['inMap'] ? 'in map' : 'NOT in map'
    );
}

if ($fatal) {
    echo "\nFAIL: the listed lookups raise 'Undefined array key' whenever the\n";
    echo "tenant has no content row for the active language.\n";
    exit(1);
}

echo "\nOK: every unprotected \$keywords lookup uses a real dictionary key\n";
exit(0);
