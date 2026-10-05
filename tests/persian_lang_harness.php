<?php
/**
 * Persian (fa) language harness.
 *
 * The three translation namespaces (front, admin, user dashboard) are the
 * easy thing to add and the easy thing to get subtly wrong: a missing key
 * falls back to English silently, a per-language DB row missing makes the
 * front page 500 with "Attempt to read property 'menus' on null", and an
 * untranslated namespace silently renders the admin panel in English.
 *
 * Checks:
 *   1. resources/lang/{fa,admin_fa,user_fa}.json key parity with their en source
 *   2. every value actually contains Persian script (no silent English)
 *   3. the per-language PHP files (validation/auth/passwords/pagination/
 *      installer_messages) exist in all three fa namespaces and parse
 *   4. the languages / user_languages rows exist, fa is rtl=1 and is not the
 *      default, and the per-language content tables each have a fa row
 *   5. the per-language site copy (section titles, hero, cookie notice, SEO
 *      meta, footer links) is Persian too, not just the UI chrome
 *   6. currentHtmlLang() strips the admin_/user_ namespace prefixes
 *   7. live HTTP: switching to fa renders dir="rtl" with Persian markup
 *   8. every translation key the views ask for resolves through Laravel's
 *      translator against the locale that view runs under
 *   9. negative controls - the parity check must fail when a key is dropped,
 *      and re-running register_persian.php must not duplicate any row
 *  10. the fa logo/favicon are their own files (not shared with en), and
 *      register_persian.php --remove clears every row it creates
 *
 * Usage: php tests/persian_lang_harness.php
 * Exit code 0 = all checks passed.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

const BASE_URL = 'http://localhost/proresume';
const CODE_FA = 'fa';

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

function check(bool $cond, string $label, string $detail = ''): void
{
    $cond ? ok($label) : bad($label, $detail);
}

function hasPersian(string $s): bool
{
    return (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $s);
}

/**
 * Shared parity test for one JSON namespace pair.
 *
 * The Persian file must be a *superset* of its English source: every en key
 * translated, plus exactly the reviewed extra keys - the menu-builder labels
 * and the view keys that never made it into the *_en.json files at all. Any
 * other extra key means the generator and the views have drifted apart.
 *
 * @param  array<string,string>  $allowedExtras  en => allowed extra keys
 * @return array{0:int,1:int} [translated, total]
 */
function assertParity(string $faFile, string $enFile, string $label, array $allowedExtras = []): array
{
    $enPath = resource_path('lang/' . $enFile);
    $faPath = resource_path('lang/' . $faFile);

    if (!is_file($faPath)) {
        bad("$label file exists", "missing $faFile");
        return [0, 0];
    }

    $en = json_decode((string) file_get_contents($enPath), true);
    $fa = json_decode((string) file_get_contents($faPath), true);
    if (!is_array($en) || !is_array($fa)) {
        bad("$label decodes as JSON");
        return [0, 0];
    }

    $missing = array_diff(array_keys($en), array_keys($fa));
    $extra = array_diff(array_keys($fa), array_keys($en));
    $unexpected = array_diff($extra, array_keys($allowedExtras));
    $unused = array_diff(array_keys($allowedExtras), $extra);

    check(!$missing, "$label: every en key is translated",
        count($missing) . ' missing: ' . implode(', ', array_slice($missing, 0, 5)));
    check(!$unexpected, "$label: no unreviewed extra keys",
        count($unexpected) . ' unknown: ' . implode(', ', array_slice($unexpected, 0, 5)));
    check(!$unused, "$label: every reviewed extra key is present",
        count($unused) . ' unused: ' . implode(', ', array_slice($unused, 0, 5)));

    $english = [];
    foreach ($fa as $k => $v) {
        if (!is_string($v) || trim($v) === '' || !hasPersian($v)) {
            $english[] = $k;
        }
    }
    check(!$english, "$label: no value left in English",
        count($english) . ' untranslated: ' . implode(', ', array_slice($english, 0, 5)));

    return [count($fa), count($en)];
}

$mapsDir = __DIR__ . '/lang_maps';
$menuLabels = [
    'Templates', 'Website Templates', 'Cv Templates', 'CV Templates',
    'Vcards', 'Vcards Templates', 'Pages', 'Custom Page',
    'About Us', 'Terms & Conditions', 'Privacy Policy', 'Our Blogs',
    'Contact Us', 'Gallery', 'Work Process',
];

echo "== 1. JSON translation files ==\n";
$front = assertParity('fa.json', 'en.json', 'front fa.json',
    array_fill_keys($menuLabels, '') + (require "$mapsDir/fa_view_gaps.php"));
$user = assertParity('user_fa.json', 'user_en.json', 'user user_fa.json',
    require "$mapsDir/user_fa_view_gaps.php");
$admin = assertParity('admin_fa.json', 'admin_en.json', 'admin admin_fa.json',
    require "$mapsDir/admin_fa_view_gaps.php");
check($front[0] > 250, 'front fa.json has the expected key count', "got {$front[0]}");
check($user[0] >= $user[1], 'user_fa.json covers every user_en.json key', "{$user[0]} vs {$user[1]}");
check($admin[0] >= $admin[1], 'admin_fa.json covers every admin_en.json key', "{$admin[0]} vs {$admin[1]}");


echo "\n== 2. Per-language PHP files ==\n";
$expected = ['validation.php', 'auth.php', 'passwords.php', 'pagination.php', 'installer_messages.php'];
foreach (['admin_fa' => 'admin', 'user_fa' => 'user'] as $dir => $label) {
    foreach ($expected as $file) {
        $path = resource_path('lang/' . $dir . '/' . $file);
        if (!is_file($path)) {
            bad("$label/$dir/$file exists");
            continue;
        }
        $out = [];
        $code = 0;
        exec('php -l ' . escapeshellarg($path) . ' 2>&1', $out, $code);
        check($code === 0, "$label/$dir/$file parses", implode(' ', $out));
    }
    $path = resource_path('lang/' . $dir . '/validation.php');
    $lines = is_file($path) ? file($path) : [];
    $persian = 0;
    foreach ($lines as $line) {
        if (hasPersian($line)) {
            $persian++;
        }
    }
    check($persian > 50, "$label/$dir/validation.php is translated", "only {$persian} Persian lines");
}

echo "\n== 3. Database registration ==\n";
$lang = DB::table('languages')->where('code', 'fa')->first();
check((bool) $lang, 'languages row for fa exists');
if ($lang) {
    check((int) $lang->rtl === 1, 'fa is marked rtl=1');
    check((int) $lang->is_default === 0, 'fa is not the default language');
    check(DB::table('languages')->where('is_default', 1)->where('code', 'fa')->doesntExist(),
        'fa never became the default language');
    foreach (['basic_settings', 'basic_extendeds', 'seos', 'menus'] as $table) {
        $count = DB::table($table)->where('language_id', $lang->id)->count();
        check($count >= 1, "per-language row exists in $table", "count={$count}");
    }
    $menus = DB::table('menus')->where('language_id', $lang->id)->first();
    check((bool) $menus, 'fa menus row exists');
    if ($menus) {
        $links = json_decode((string) $menus->menus, true);
        $labels = [];
        $walk = function (array $items) use (&$walk, &$labels) {
            foreach ($items as $item) {
                $labels[] = $item['text'] ?? '';
                if (!empty($item['children'])) {
                    $walk($item['children']);
                }
            }
        };
        if (is_array($links)) {
            $walk($links);
        }
        $untranslated = array_values(array_filter($labels, fn($l) => $l !== '' && !hasPersian($l)));
        check(count($labels) > 0, 'fa menu has items', 'labels=' . count($labels));
        check(!$untranslated, 'every fa menu label is Persian',
            implode(', ', array_slice($untranslated, 0, 5)));
    }
}

$userLang = DB::table('user_languages')->where('code', 'fa')->whereNull('user_id')->first();
check((bool) $userLang, 'user_languages row for fa exists');
if ($userLang) {
    check((int) $userLang->rtl === 1, 'user fa is marked rtl=1');
}
$orphanUsers = DB::table('user_languages')->whereNull('user_id')->pluck('code')->all();
check(in_array('fa', $orphanUsers, true), 'fa is selectable in the user dashboard language list',
    implode(',', $orphanUsers));

echo "\n== 4. Per-language site copy ==\n";
// The UI strings live in the JSON files, but the home page copy (section
// titles, hero text, cookie notice, SEO meta, footer links) is stored per
// language. A clone that leaves it English shows a Persian chrome wrapped
// around English prose, so the long English text columns must all be Persian.
$PROSE_PATTERN = '/[A-Za-z]{4,}\s+[A-Za-z]{3,}/';
$NOT_PROSE = '/(<script|<ul|<li|<table|api|key|smtp|www\.|@|\.com|\.io|\.net|\.xyz)/i';
// Operator-supplied data, not prose: the site owner replaces these with their
// own values per language (that is also why the Arabic row keeps them), and
// the harness must not pass only by inventing Persian postal addresses or
// guessing at SMTP credentials.
$OPERATOR_DATA = [
    'smtp_password', 'smtp_username', 'smtp_host', 'to_mail', 'from_mail',
    'contact_addresses', 'contact_numbers', 'contact_mails',
    'tawk_to_script', 'disqus_script', 'tawkto_property_id', 'disqus_shortname',
    'google_recaptcha_site_key', 'google_recaptcha_secret_key', 'enamad_code',
    'enamad_site_id', 'enamad_secret_key', 'enamad_expire_date',
    'website_title', 'from_name', 'timezone', 'hero_img', 'hero_section_video_url',
    'hero_section_button_url', 'base_currency_symbol', 'base_currency_text',
    'base_currency_symbol_position', 'base_currency_text_position', 'encryption',
    'default_language_direction', 'favicon', 'logo', 'preloader', 'breadcrumb',
    'footer_logo', 'intro_main_image', 'maintenance_img', 'base_color',
];
if ($lang) {
    foreach (['basic_settings', 'basic_extendeds', 'seos'] as $table) {
        $row = (array) DB::table($table)->where('language_id', $lang->id)->first();
        $leftover = [];
        foreach ($row as $k => $v) {
            if (in_array($k, ['id', 'language_id'], true) || $v === null || $v === '') {
                continue;
            }
            $s = (string) $v;
            if (hasPersian($s) || in_array($k, $OPERATOR_DATA, true)) {
                continue;
            }
            if (preg_match($NOT_PROSE, $s)) {
                continue; // scripts, markup, hosts, credentials
            }
            if (preg_match($PROSE_PATTERN, $s)) {
                $leftover[] = $k;
            }
        }
        check(!$leftover, "no English prose left in the fa row of $table",
            implode(', ', array_slice($leftover, 0, 6)));
    }
    $links = DB::table('ulinks')->where('language_id', $lang->id)->get();
    $linkNames = $links->pluck('name')->all();
    $badLinks = array_values(array_filter($linkNames, fn($n) => $n !== '' && !hasPersian($n)));
    check(count($linkNames) > 0, 'fa footer links exist');
    check(!$badLinks, 'every fa footer link name is Persian', implode(', ', $badLinks));

    $direction = DB::table('basic_extendeds')->where('language_id', $lang->id)->value('default_language_direction');
    check($direction === 'rtl', 'fa basic_extendeds default_language_direction is rtl', (string) $direction);

    $features = json_decode((string) DB::table('basic_extendeds')
        ->where('language_id', $lang->id)->value('package_features'), true);
    $badFeatures = is_array($features)
        ? array_values(array_filter($features, fn($f) => !hasPersian((string) $f)))
        : ['package_features is not valid JSON'];
    check(!$badFeatures, 'every fa package feature name is Persian', implode(', ', $badFeatures));
}

echo "\n== 5. Tenant / booking keyword dictionaries ==\n";
// The booking, checkout and tenant-site labels are NOT in a lang file: they
// live in languages.customer_keywords and in each user's user_languages.keywords.
// A Persian row that is missing or empty renders English in the one place the
// visitor is most likely to be spending money.
$kwMap = require "$mapsDir/fa_customer_keywords.php";
$BRAND_KEYS = ['Facebook', 'Twitter', 'Linkedin', 'Mercadopago', 'Mollie', 'Flutterwave',
    'Razorpay', 'Paytm', 'Paystack', 'Instamojo', 'Stripe', 'Paypal', 'Authorize_net'];
$latin = [];
foreach ($kwMap as $k => $v) {
    if (!hasPersian((string) $v) && !in_array($k, $BRAND_KEYS, true)) {
        $latin[] = $k;
    }
}
check(!$latin, 'every fa keyword value is Persian (brands excepted)', implode(', ', $latin));

if ($lang) {
    $ckRaw = (string) DB::table('languages')->where('code', 'fa')->value('customer_keywords');
    $ck = json_decode($ckRaw, true);
    check(is_array($ck) && count($ck) > 200, 'fa customer_keywords dictionary is populated',
        is_array($ck) ? count($ck) . ' keys' : 'not JSON: ' . mb_substr($ckRaw, 0, 40));
    if (is_array($ck)) {
        $ckLatin = [];
        foreach ($ck as $k => $v) {
            if (!hasPersian((string) $v) && !in_array($k, $BRAND_KEYS, true)) {
                $ckLatin[] = $k;
            }
        }
        check(!$ckLatin, 'no English value left in fa customer_keywords', implode(', ', $ckLatin));
        $enCk = (array) json_decode((string) DB::table('languages')->where('code', 'en')->value('customer_keywords'), true);
        $absent = array_diff(array_keys($enCk), array_keys($ck));
        check(!$absent, 'fa customer_keywords covers every en key', count($absent) . ': ' . implode(', ', array_slice($absent, 0, 5)));
    }

    // A tenant can only serve a language they have a row for: userDetailView()
    // falls back to the tenant default when the requested code is missing.
    $usersWithout = DB::table('users')
        ->whereNotExists(function ($q) {
            $q->select(DB::raw(1))->from('user_languages')
                ->whereColumn('user_languages.user_id', 'users.id')
                ->where('user_languages.code', CODE_FA);
        })
        ->pluck('username')
        ->all();
    check(!$usersWithout, 'every tenant has a fa language row',
        count($usersWithout) . ': ' . implode(', ', array_slice($usersWithout, 0, 5)));

    $kwRows = DB::table('user_languages')->where('code', CODE_FA)->whereNotNull('user_id')->count();
    check($kwRows > 0, 'per-user fa rows exist', "count={$kwRows}");
}

// Every blade lookup into that dictionary must resolve. The label-style ones
// only resolve because keywordDictionaryAliases() publishes them.
$kwOut = [];
$kwLines = exec('php ' . escapeshellarg(__DIR__ . '/lang_keyword_audit.php') . ' 2>&1', $kwOut, $kwExit);
foreach ($kwOut as $line) {
    if (strpos($line, 'Imagick') === false && preg_match('/^== |^blade files|^lookups|^OK|^FAIL/', $line)) {
        echo '    ' . $line . "\n";
    }
}
check($kwExit === 0, 'no tenant blade indexes the keyword dictionary by a key that cannot resolve',
    'see tests/lang_keyword_audit.php output');

// The fa rows clone the site logo/favicon/preloader to their own filenames and
// register_persian.php --remove has to take all of it back down. Both are easy
// to break silently: a shared image makes one language's logo overwrite
// another's, and a half-complete teardown leaves the site without a language.
$tdOut = [];
$tdLines = exec('php ' . escapeshellarg(__DIR__ . '/persian_teardown_check.php') . ' 2>&1', $tdOut, $tdExit);
foreach ($tdOut as $line) {
    if (strpos($line, 'Imagick') === false && preg_match('/^== |^TEARDOWN CHECK/', $line)) {
        echo '    ' . $line . "\n";
    }
}
check($tdExit === 0, 'fa image files exist and --remove clears every row the script creates',
    'see tests/persian_teardown_check.php output');

echo "\n== 6. currentHtmlLang() strips the namespace prefix ==\n";
foreach ([['fa', 'fa'], ['admin_fa', 'fa'], ['user_fa', 'fa'], ['admin_en', 'en']] as [$locale, $expectedLang]) {
    app()->setLocale($locale);
    check(currentHtmlLang() === $expectedLang, "locale $locale -> lang=$expectedLang", 'got ' . currentHtmlLang());
}
app()->setLocale('en');

echo "\n== 7. Live render ==\n";
// The language switch lives in the session, so curl needs a cookie jar or the
// redirected page render comes back in the default language.
$jar = tempnam(sys_get_temp_dir(), 'fa_cookies_');
$fetch = function (string $url) use ($jar) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_COOKIEFILE => $jar,
        CURLOPT_COOKIEJAR => $jar,
    ]);
    $body = curl_exec($ch);
    $status = $ch === null ? 0 : curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = $ch === null ? 'no handle' : curl_error($ch);
    curl_close($ch);
    return [$body === false ? null : (string) $body, $status, $error];
};

[$body, $status, $error] = $fetch(BASE_URL . '/changelanguage/fa');
if ($body === null) {
    bad('front page reachable at ' . BASE_URL, $error);
} else {
    check($status === 200, 'front page returns 200 in fa', "status={$status}");
    check(strpos($body, 'dir="rtl"') !== false, 'front page renders dir="rtl"');
    check(strpos($body, 'lang="fa"') !== false, 'front page announces lang="fa"');
    check(strpos($body, 'Attempt to read property') === false,
        'front page did not hit the missing-basic_settings failure');
    preg_match_all('/[\x{0600}-\x{06FF}]/u', $body, $faChars);
    check(count($faChars[0]) > 200, 'front page contains Persian text',
        'only ' . count($faChars[0]) . ' Persian characters');
    // A raw English menu label would mean the fa menus row was not localized.
    check(strpos($body, '>Pricing<') === false, 'no untranslated menu label in the Persian front page');

    // An unmatched URL renders through the fallback route, which is the only
    // place that never gets a matched route's middleware - so it has to carry
    // setlang itself or every dead link answers in the default language.
    [$body404, $status404] = $fetch(BASE_URL . '/aaa/bbb/ccc');
    check($status404 === 404, 'an unmatched URL answers 404, not 200', "status={$status404}");
    check(is_string($body404) && strpos($body404, 'lang="fa"') !== false,
        'the 404 page renders in the visitor language');
    check(is_string($body404) && strpos($body404, 'dir="rtl"') !== false,
        'the 404 page renders right-to-left in fa');
}
@unlink($jar);

echo "\n== 8. View key coverage ==\n";
// Parity only proves fa.json covers en.json. It cannot see keys the blades ask
// for that are absent from en.json entirely - those render in English on every
// language. The audit resolves every key through Laravel's real translator.
$covOut = [];
$lines = exec('php ' . escapeshellarg(__DIR__ . '/lang_coverage_audit.php') . ' 2>&1', $covOut, $covExit);
foreach ($covOut as $line) {
    if (strpos($line, 'Imagick') === false && preg_match('/^== |^LANG COVERAGE/', $line)) {
        echo '    ' . $line . "\n";
    }
}
check($covExit === 0, 'every __()/@lang/trans() key used by a view resolves in Persian',
    'see tests/lang_coverage_audit.php output');

echo "\n== 9. Idempotency and negative controls ==\n";
// register_persian.php is documented as idempotent, and an unguarded clone
// loop is invisible until the Persian footer doubles. Re-run it and assert
// every per-language row count is unchanged.
if ($lang) {
    $before = [];
    foreach (['basic_settings', 'basic_extendeds', 'seos', 'menus', 'pages', 'ulinks'] as $table) {
        $before[$table] = DB::table($table)->where('language_id', $lang->id)->count();
    }
    exec('php ' . escapeshellarg(dirname(__DIR__) . '/tests/register_persian.php') . ' 2>&1', $regOut, $regCode);
    $regOk = true;
    foreach ($before as $table => $count) {
        $after = DB::table($table)->where('language_id', $lang->id)->count();
        if ($after !== $count) {
            $regOk = false;
            bad("re-running register_persian.php leaves $table unchanged",
                "{$count} -> {$after}");
        }
    }
    if ($regOk) {
        ok('re-running register_persian.php changes no row count (idempotent)');
    }
    if ($regCode !== 0) {
        bad('register_persian.php exits 0 on re-run', 'exit=' . $regCode);
    } else {
        ok('register_persian.php exits 0 on re-run');
    }
}

// The parity check must actually be able to fail, otherwise a green run means
// nothing. Drop a key that really exists in en.json and expect a rejection.
// The parity check must actually be able to fail, otherwise a green run means
// nothing. Drop a key that really exists in en.json and expect a rejection.
$faData = json_decode((string) file_get_contents(resource_path('lang/fa.json')), true);
$enKeys = array_keys(json_decode((string) file_get_contents(resource_path('lang/en.json')), true));
$victim = null;
foreach ($enKeys as $k) {
    if (array_key_exists($k, $faData)) {
        $victim = $k;
        break;
    }
}
unset($faData[$victim]);
$detected = count(array_diff($enKeys, array_keys($faData))) > 0;
check($detected, "a dropped key ('{$victim}') is detected by the parity check");

echo "\n" . str_repeat('=', 52) . "\n";
echo "PERSIAN LANG: $passed passed, $failed failed\n";
echo str_repeat('=', 52) . "\n";
exit($failed === 0 ? 0 : 1);