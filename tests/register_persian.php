<?php
/**
 * Registers Persian (fa) end-to-end.
 *
 * Inserting the row into `languages` alone is NOT enough and actively breaks
 * the site: the front page resolves BasicSetting by the active language, so a
 * language with no basic_settings row renders "Attempt to read property
 * 'menus' on null". The admin LanguageController clones a set of per-language
 * rows when a language is created; this script mirrors that, so it is safe to
 * run on a fresh install or to repair a half-added language.
 *
 * Idempotent. Pass --remove to undo everything this script created.
 *
 * Usage: php tests/register_persian.php [--remove]
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

const CODE = 'fa';
const NAME = 'فارسی';

/** Image columns the app re-copies to a unique filename per language. */
const IMAGE_COLUMNS = ['favicon', 'logo', 'preloader', 'hero_img', 'banner_image'];

/**
 * Per-language content tables this script clones. Only the first four are
 * required for the front page to render at all; pages/ulinks are cloned so a
 * Persian visitor sees the same navigation and footer links as an English
 * one instead of an empty site.
 */
const PER_LANGUAGE_TABLES = ['basic_settings', 'basic_extendeds', 'seos', 'menus', 'pages', 'ulinks'];

function teardown(): void
{
    $lang = DB::table('languages')->where('code', CODE)->first();
    if ($lang) {
        foreach (PER_LANGUAGE_TABLES as $table) {
            DB::table($table)->where('language_id', $lang->id)->delete();
        }
        DB::table('languages')->where('id', $lang->id)->delete();
    }
    DB::table('user_languages')->where('code', CODE)->delete();
    echo "removed '{$lang->name}' (code " . CODE . ") and its cloned rows\n";
}

/**
 * Translate a single English UI label through the front fa.json map, falling
 * back to the original text when it is not a known label.
 */
function faLabel(string $text): string
{
    static $fa = null;
    if ($fa === null) {
        $fa = json_decode((string) file_get_contents(__DIR__ . '/../resources/lang/fa.json'), true);
    }

    return isset($fa[$text]) ? $fa[$text] : $text;
}

/**
 * Localize a menus JSON blob for the Persian language.
 *
 * Two things happen here:
 *  - Labels go through the front fa.json map, mirroring the admin menu
 *    builder which stores the *translated* label (data-text="{{ __('Home') }}").
 *  - A custom page item's "type" holds the pages.id of the language that
 *    built the menu. Cloning the JSON verbatim would leave the Persian menu
 *    pointing at the English page rows, so numeric ids are remapped onto the
 *    matching Persian page via slug.
 *
 * @param  array<int,int>  $pageIdMap  English page id => Persian page id
 */
function localizeMenu(string $json, array $pageIdMap): string
{
    $links = json_decode($json, true);
    if (!is_array($links)) {
        return $json;
    }

    $walk = function (array $items) use (&$walk, $pageIdMap): array {
        foreach ($items as &$item) {
            if (isset($item['text'])) {
                $item['text'] = faLabel($item['text']);
            }
            if (isset($item['type']) && isset($pageIdMap[$item['type']])) {
                $item['type'] = (string) $pageIdMap[$item['type']];
            }
            if (!empty($item['children']) && is_array($item['children'])) {
                $item['children'] = $walk($item['children']);
            }
        }
        return $items;
    };

    return json_encode($walk($links), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * Apply the Persian site copy to a cloned per-language row.
 *
 * Covers the prose columns of basic_settings / basic_extendeds / seos, plus
 * the two structured ones: package_features is a JSON list of feature names
 * that go through fa.json, and default_language_direction must flip to rtl.
 * Data columns (emails, phones, image names, SMTP credentials, brand names)
 * are intentionally left as cloned.
 */
function applyPersianCopy(string $table, array $row): array
{
    $copy = require __DIR__ . '/lang_maps/fa_site_copy.php';

    foreach ($copy[$table] ?? [] as $column => $text) {
        if (array_key_exists($column, $row)) {
            $row[$column] = $text;
        }
    }

    if ($table === 'basic_extendeds') {
        if (!empty($row['package_features'])) {
            $features = json_decode((string) $row['package_features'], true);
            if (is_array($features)) {
                $row['package_features'] = json_encode(
                    array_map('faLabel', $features),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
            }
        }
        // Cloned from an LTR default, so the direction has to be corrected.
        $row['default_language_direction'] = 'rtl';
    }

    return $row;
}

if (in_array('--remove', $argv ?? [], true)) {
    teardown();
    return;
}

$default = DB::table('languages')->where('is_default', 1)->first()
    ?: DB::table('languages')->orderBy('id')->first();
if (!$default) {
    fwrite(STDERR, "no language rows at all - cannot clone from a default\n");
    exit(1);
}

DB::transaction(function () use ($default) {
    $lang = DB::table('languages')->where('code', CODE)->first();
    if (!$lang) {
        $id = DB::table('languages')->insertGetId([
            'name' => NAME, 'code' => CODE, 'is_default' => 0, 'rtl' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        echo "created languages row id={$id}\n";
    } else {
        $id = $lang->id;
        DB::table('languages')->where('id', $id)->update(['name' => NAME, 'rtl' => 1]);
        echo "languages row id={$id} already present, refreshed\n";
    }

    // ---- basic_settings ------------------------------------------------
    // clone from the default language, re-copying each image to its own file
    // so editing one language's logo cannot silently replace another's
    $src = DB::table('basic_settings')->where('language_id', $default->id)->first();
    if (!$src) {
        throw new RuntimeException('default language has no basic_settings row');
    }
    $existing = DB::table('basic_settings')->where('language_id', $id)->first();
    if (!$existing) {
        $row = (array) $src;
        unset($row['id']);
        $row['language_id'] = $id;
        foreach (IMAGE_COLUMNS as $col) {
            if (empty($row[$col])) {
                continue;
            }
            $from = public_path('assets/front/img/' . $row[$col]);
            if (!is_file($from)) {
                continue;
            }
            $ext = pathinfo($row[$col], PATHINFO_EXTENSION);
            $new = uniqid() . ($ext ? '.' . $ext : '');
            if (@copy($from, public_path('assets/front/img/' . $new))) {
                $row[$col] = $new;
            }
        }
        DB::table('basic_settings')->insert(applyPersianCopy('basic_settings', $row));
        echo "cloned basic_settings for fa\n";
    }

    // ---- basic_extendeds ------------------------------------------------
    $srcBe = DB::table('basic_extendeds')->where('language_id', $default->id)->first();
    if ($srcBe && !DB::table('basic_extendeds')->where('language_id', $id)->exists()) {
        $row = (array) $srcBe;
        unset($row['id']);
        $row['language_id'] = $id;
        $row = applyPersianCopy('basic_extendeds', $row);
        DB::table('basic_extendeds')->insert($row);
        echo "cloned basic_extendeds for fa\n";
    }

    // ---- seos -----------------------------------------------------------
    $srcSeo = DB::table('seos')->where('language_id', $default->id)->first();
    if ($srcSeo && !DB::table('seos')->where('language_id', $id)->exists()) {
        $row = (array) $srcSeo;
        unset($row['id']);
        $row['language_id'] = $id;
        $row = applyPersianCopy('seos', $row);
        DB::table('seos')->insert($row);
        echo "cloned seos for fa\n";
    }

    // ---- pages ----------------------------------------------------------
    // Cloned before the menu, because the menu's custom-page items reference
    // pages.id and have to be remapped onto the Persian rows.
    $pageIdMap = [];
    if (DB::table('pages')->where('language_id', $default->id)->exists()) {
        foreach (DB::table('pages')->where('language_id', $default->id)->get() as $row) {
            $existing = DB::table('pages')->where('language_id', $id)->where('slug', $row->slug)->first();
            if (!$existing) {
                $copy = (array) $row;
                unset($copy['id']);
                $copy['language_id'] = $id;
                foreach (['name', 'title'] as $field) {
                    if (isset($copy[$field])) {
                        $copy[$field] = faLabel($copy[$field]);
                    }
                }
                $newId = DB::table('pages')->insertGetId($copy);
            } else {
                $newId = $existing->id;
            }
            $pageIdMap[$row->id] = $newId;
        }
        echo "cloned pages for fa\n";
    }

    // ---- menus ----------------------------------------------------------
    // Clone the default language's menu so the Persian site has the same
    // structure (including nested items). Labels are translated through the
    // front fa.json map, exactly like the admin menu builder does when a
    // Persian admin builds a menu.
    $srcMenu = DB::table('menus')->where('language_id', $default->id)->first();
    if ($srcMenu && !DB::table('menus')->where('language_id', $id)->exists()) {
        DB::table('menus')->insert([
            'language_id' => $id,
            'menus' => localizeMenu((string) $srcMenu->menus, $pageIdMap),
        ]);
        echo "cloned menus for fa\n";
    }

    // ---- footer useful links --------------------------------------------
    // Guarded by (name, url), not url alone: a fresh install ships several
    // footer links pointing at the same placeholder URL, so deduping on url
    // would drop all but the first. Without any guard a re-run appends a
    // second copy of every link and the Persian footer grows each time.
    if (DB::table('ulinks')->where('language_id', $default->id)->exists()) {
        $copied = 0;
        foreach (DB::table('ulinks')->where('language_id', $default->id)->get() as $row) {
            $name = isset($row->name) ? faLabel($row->name) : $row->name;
            $exists = DB::table('ulinks')
                ->where('language_id', $id)
                ->where('url', $row->url)
                ->where('name', $name)
                ->exists();
            if ($exists) {
                continue;
            }
            $copy = (array) $row;
            unset($copy['id']);
            $copy['language_id'] = $id;
            $copy['name'] = $name;
            DB::table('ulinks')->insert($copy);
            $copied++;
        }
        echo "cloned ulinks for fa ($copied new)\n";
    }

    // ---- user dashboard language ---------------------------------------
    if (!DB::table('user_languages')->where('code', CODE)->whereNull('user_id')->exists()) {
        DB::table('user_languages')->insert([
            'name' => NAME, 'code' => CODE, 'is_default' => 0, 'rtl' => 1,
            'type' => null, 'user_id' => null, 'created_at' => now(), 'updated_at' => now(),
        ]);
        echo "created user_languages row for fa\n";
    }

    // ---- tenant / booking keyword dictionary ----------------------------
    // languages.customer_keywords is a JSON dictionary the booking, checkout
    // and receipt screens read straight out of the DB - no lang file involved.
    // Left empty it renders English labels even with the locale set to fa.
    $customerKeywords = json_encode(
        require __DIR__ . '/lang_maps/fa_customer_keywords.php',
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    DB::table('languages')->where('id', $id)->update(['customer_keywords' => $customerKeywords]);
    echo "set languages.customer_keywords for fa\n";

    // ---- per-user tenant languages ---------------------------------------
    // A tenant can only offer a language they have a user_languages row for:
    // userDetailView() looks the requested code up among that user's rows and
    // falls back to the tenant default when it is missing, so without this a
    // visitor who picks Persian is silently put back on the tenant's English.
    $created = 0;
    foreach (DB::table('users')->pluck('id') as $userId) {
        if (DB::table('user_languages')->where('code', CODE)->where('user_id', $userId)->exists()) {
            continue;
        }
        DB::table('user_languages')->insert([
            'user_id' => $userId, 'type' => 'admin', 'name' => NAME, 'code' => CODE,
            'is_default' => 0, 'rtl' => 1, 'keywords' => $customerKeywords,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $created++;
    }
    echo "created per-user fa tenant languages ($created new)\n";
});

echo "\n--- resulting state ---\n";
foreach (DB::table('languages')->get() as $l) {
    $bs = DB::table('basic_settings')->where('language_id', $l->id)->count();
    $be = DB::table('basic_extendeds')->where('language_id', $l->id)->count();
    $mn = DB::table('menus')->where('language_id', $l->id)->count();
    $se = DB::table('seos')->where('language_id', $l->id)->count();
    $pg = DB::table('pages')->where('language_id', $l->id)->count();
    $ul = DB::table('ulinks')->where('language_id', $l->id)->count();
    printf("  %-8s id=%-4d rtl=%d default=%d  basic_settings=%d basic_extendeds=%d menus=%d seos=%d pages=%d ulinks=%d\n",
        $l->code, $l->id, $l->rtl, $l->is_default, $bs, $be, $mn, $se, $pg, $ul);
}