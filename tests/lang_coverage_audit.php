<?php
/**
 * Translation-coverage audit (runtime-accurate).
 *
 * The Persian harness proves fa.json/admin_fa.json/user_fa.json are complete
 * *relative to their English source*. It cannot prove the keys the views ask
 * for actually resolve - a view calling __('Watermark Badge Settings') has no
 * entry in admin_en.json either, so parity never notices and the key renders
 * in English on every language.
 *
 * This scans every __('...'), @lang and trans() call in resources/views,
 * works out the locale that view resolves against, and asks Laravel's real
 * translator. Going through the translator rather than reading the files means
 * JSON keys, the validation/auth/passwords/pagination PHP files and the
 * vendor's nested installer_messages.* are all judged by the same code path
 * the app uses.
 *
 * Usage: php tests/lang_coverage_audit.php
 * Exit code 0 = every scanned key resolves.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Lang;

/**
 * Namespace of a view, from its path.
 *
 * resources/views/user/ holds two things resolving against different locales:
 * the user dashboard (achievement/, appointment/, gallery/, vcard/, settings/
 * ... rendered by App\Http\Controllers\User\*, which runs UserLangLocale and
 * uses the "user_" prefixed locale) and the public profile themes (profile/,
 * profile1/, profile-common/ rendered by App\Http\Controllers\Front\*, which
 * runs SetLangMiddleware and uses the plain front locale). Classifying the
 * second group as "user" would report ~300 false gaps.
 */
const PUBLIC_PROFILE_DIRS = ['views/user/profile/', 'views/user/profile1/', 'views/user/profile-common/'];

function namespaceOf(string $file): string
{
    if (strpos($file, 'views/admin/') === 0) {
        return 'admin';
    }
    if (strpos($file, 'views/user-front/') === 0) {
        return 'user';
    }
    if (strpos($file, 'views/user/') === 0) {
        foreach (PUBLIC_PROFILE_DIRS as $dir) {
            if (strpos($file, $dir) === 0) {
                return 'front';
            }
        }
        return 'user';
    }
    return 'front';
}

function localeFor(string $ns): string
{
    return $ns === 'admin' ? 'admin_fa' : ($ns === 'user' ? 'user_fa' : 'fa');
}

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(resource_path('views'), FilesystemIterator::SKIP_DOTS)
);

$missing = ['front' => [], 'admin' => [], 'user' => []];
$malformed = ['front' => [], 'admin' => [], 'user' => []];
$numericKey = ['front' => [], 'admin' => [], 'user' => []];
$dynamic = [];
$total = 0;
$checked = 0;

// Laravel falls back to the key itself when a JSON key is absent, and to
// "validation.foo" style passthrough for missing PHP groups; compare against
// the key to detect that. A key that is already Persian and maps to itself is
// a legitimate translation (the Enamad partial passes Persian text), not a gap.
$hasPersianScript = static function (string $s): bool {
    return (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $s);
};
$resolves = static function (string $key, string $locale) use ($hasPersianScript): bool {
    if (!Lang::has($key, $locale)) {
        return false;
    }
    $value = Lang::get($key, [], $locale);

    return $value !== $key || $hasPersianScript($key);
};

foreach ($files as $f) {
    if (!str_ends_with($f->getFilename(), '.blade.php')) {
        continue;
    }
    $rel = str_replace('\\', '/', 'views/' . substr(
        $f->getPathname(),
        strlen(resource_path('views')) + 1
    ));
    $ns = namespaceOf($rel);
    $locale = localeFor($ns);
    $src = (string) file_get_contents($f->getPathname());

    preg_match_all("/__\(\s*'((?:[^'\\\\]|\\\\.)*)'/", $src, $m1);
    preg_match_all('/__\(\s*"((?:[^"\\\\]|\\\\.)*)"/', $src, $m2);
    preg_match_all("/@lang\(\s*'((?:[^'\\\\]|\\\\.)*)'/", $src, $m3);
    preg_match_all("/trans\(\s*'((?:[^'\\\\]|\\\\.)*)'/", $src, $m4);
    $keys = array_merge($m1[1], $m2[1], $m3[1], $m4[1]);

    if (preg_match_all("/__\(\s*(?!'|\")/", $src, $dyn)) {
        $dynamic[$rel] = count($dyn[0]);
    }

    foreach ($keys as $key) {
        $key = str_replace("\\'", "'", $key);
        if ($key === '' || strpos($key, '$') !== false) {
            continue; // dynamic expression, not statically resolvable
        }
        // A key containing a newline or a run of spaces is ugly and usually a
        // typo. Flag it only when it actually fails to resolve - a legacy key
        // like "Never  Activated" that is present in the *_en.json source is
        // correct, just untidy.
        if (preg_match('/\s{2,}|\n/', $key)) {
            $total++;
            if ($resolves($key, $locale)) {
                $checked++;
            } else {
                $malformed[$ns][$key][] = $rel;
            }
            continue;
        }
        // A purely numeric key becomes an integer array key after json_decode,
        // so __('404') never resolves. Report it as a defect, not a gap.
        if (ctype_digit($key)) {
            $numericKey[$ns][$key][] = $rel;
            $total++;
            continue;
        }
        $total++;
        if (!$resolves($key, $locale)) {
            $missing[$ns][$key][] = $rel;
        } else {
            $checked++;
        }
    }
}

echo 'Locale per namespace: front=fa  admin=admin_fa  user=user_fa' . "\n";
echo "Translation keys scanned: $total   resolved: $checked   unresolved: " . ($total - $checked) . "\n\n";

$bad = 0;
foreach (['front', 'admin', 'user'] as $ns) {
    $keys = array_keys($missing[$ns]);
    sort($keys);
    echo "== $ns (" . localeFor($ns) . '): ' . count($keys) . " unresolved key(s) ==\n";
    foreach ($keys as $k) {
        echo '   ' . $k . "\n";
        foreach (array_unique($missing[$ns][$k]) as $where) {
            echo '        ' . $where . "\n";
        }
    }
    $bad += count($keys);
    echo "\n";
}

foreach (['malformed' => $malformed, 'numeric' => $numericKey] as $label => $set) {
    $n = array_sum(array_map('count', $set));
    if ($n === 0) {
        continue;
    }
    echo "== malformed keys: $label ==\n";
    foreach ($set as $ns => $keys) {
        foreach ($keys as $k => $where) {
            echo '   [' . $ns . '] ' . json_encode($k, JSON_UNESCAPED_UNICODE) . "\n";
            foreach (array_unique($where) as $w) {
                echo '        ' . $w . "\n";
            }
        }
    }
    $bad += $n;
    echo "\n";
}

if ($dynamic) {
    echo '== dynamic __() lookups (not statically checkable) ==' . "\n";
    foreach ($dynamic as $where => $count) {
        echo "   $where ($count)\n";
    }
    echo "\n";
}

echo str_repeat('=', 52) . "\n";
echo 'LANG COVERAGE: ' . ($bad === 0
    ? 'OK - every scanned key resolves and no key is malformed'
    : "$bad problem(s): unresolved + malformed keys") . "\n";
echo str_repeat('=', 52) . "\n";
exit($bad === 0 ? 0 : 1);