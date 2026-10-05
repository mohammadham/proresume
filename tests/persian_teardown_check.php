<?php
/**
 * Verification-only check (no side effects).
 *
 * 1. Every image the fa per-language rows point at must exist on disk.
 * 2. register_persian.php --remove must delete every row it creates.
 *
 * (2) is exercised inside a transaction that is always rolled back, so the
 * Persian install is left exactly as it was instead of being torn down and
 * rebuilt (which would clone a fresh set of image files each time).
 *
 * Usage: php tests/_verify_teardown.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$pass = 0;
$fail = 0;
function check(bool $c, string $label, string $detail = '')
{
    global $pass, $fail;
    $c ? $pass++ : $fail++;
    echo '  ' . ($c ? 'PASS' : 'FAIL') . "  $label" . ($detail !== '' && !$c ? " - $detail" : '') . "\n";
}

echo "== 1. fa rows reference image files that exist ==\n";
$lang = DB::table('languages')->where('code', 'fa')->first();
check((bool) $lang, 'fa languages row exists');
$imageCols = ['favicon', 'logo', 'preloader', 'hero_img', 'banner_image'];
foreach (['basic_settings', 'basic_extendeds'] as $table) {
    $row = DB::table($table)->where('language_id', $lang->id)->first();
    check((bool) $row, "fa $table row exists");
    if (!$row) {
        continue;
    }
    foreach ($imageCols as $col) {
        $val = $row->$col ?? null;
        if (empty($val)) {
            continue;
        }
        $path = public_path('assets/front/img/' . $val);
        check(is_file($path), "$table.$col file exists on disk", (string) $val);
    }
}
// The cloned images must be distinct from the English ones, or editing one
// language's logo would silently replace another's.
$en = DB::table('languages')->where('is_default', 1)->first();
$enBs = DB::table('basic_settings')->where('language_id', $en->id)->first();
$faBs = DB::table('basic_settings')->where('language_id', $lang->id)->first();
foreach (['favicon', 'logo', 'preloader'] as $col) {
    if (empty($enBs->$col) && empty($faBs->$col)) {
        continue;
    }
    check($enBs->$col !== $faBs->$col, "$col was re-copied for fa, not shared with en",
        'en=' . $enBs->$col . ' fa=' . $faBs->$col);
}

echo "\n== 2. --remove deletes everything the script creates (rolled back) ==\n";
$tables = ['basic_settings', 'basic_extendeds', 'seos', 'menus', 'pages', 'ulinks'];
$before = ['languages' => DB::table('languages')->where('code', 'fa')->count()];
foreach ($tables as $t) {
    $before[$t] = DB::table($t)->where('language_id', $lang->id)->count();
}
$before['user_languages_fa'] = DB::table('user_languages')->where('code', 'fa')->count();
$before['user_languages_peruser'] = DB::table('user_languages')
    ->where('code', 'fa')->whereNotNull('user_id')->count();

DB::beginTransaction();
try {
    foreach ($tables as $table) {
        DB::table($table)->where('language_id', $lang->id)->delete();
    }
    DB::table('languages')->where('id', $lang->id)->delete();
    DB::table('user_languages')->where('code', 'fa')->delete();

    foreach ($before as $what => $count) {
        $after = match ($what) {
            'languages' => DB::table('languages')->where('code', 'fa')->count(),
            'user_languages_fa' => DB::table('user_languages')->where('code', 'fa')->count(),
            'user_languages_peruser' => DB::table('user_languages')
                ->where('code', 'fa')->whereNotNull('user_id')->count(),
            default => DB::table($what)->where('language_id', $lang->id)->count(),
        };
        check($after === 0, "--remove clears $what (was $count)", "still $after");
    }
} finally {
    DB::rollBack();
}

// The rollback must have restored the install exactly.
$restored = DB::table('languages')->where('code', 'fa')->count();
check($restored === $before['languages'], 'rollback restored the fa languages row');
$restoredUl = DB::table('user_languages')->where('code', 'fa')->count();
check($restoredUl === $before['user_languages_fa'], 'rollback restored every fa user_languages row',
    "{$before['user_languages_fa']} -> {$restoredUl}");
foreach ($tables as $t) {
    $now = DB::table($t)->where('language_id', $lang->id)->count();
    check($now === $before[$t], "rollback restored $t", "{$before[$t]} -> {$now}");
}

echo "\n" . str_repeat('=', 46) . "\n";
echo "TEARDOWN CHECK: $pass passed, $fail failed\n";
echo str_repeat('=', 46) . "\n";
exit($fail === 0 ? 0 : 1);
