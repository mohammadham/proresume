<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.org>
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

/*
|--------------------------------------------------------------------------
| Sub-directory installs
|--------------------------------------------------------------------------
|
| The root .htaccess forwards every non-file request to this file, but it
| hands PHP the untouched request URI - including the folder the project
| lives in. For a site served from http://localhost/proresume/ Laravel
| would therefore look for a "/proresume/..." route, match nothing and
| 404 on every page. Strip that prefix here so the app sees its own root,
| and present SCRIPT_NAME as "/index.php" so the URL generator does not
| mistake the shim itself for the application base path.
|
*/

$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

if ($basePath !== '' && $basePath !== '/' && $uri !== null) {
    $matchesBasePath = $uri === $basePath || strpos($uri, $basePath . '/') === 0;

    if ($matchesBasePath) {
        $stripped = substr($uri, strlen($basePath));
        $query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);

        $_SERVER['REQUEST_URI'] = ($stripped === '' ? '/' : $stripped)
            . ($query ? '?' . $query : '');

        $uri = $_SERVER['REQUEST_URI'];
    }
}

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Laravel derives its routing path from REQUEST_URI only, so the prefix is
| stripped and nothing else has to change. SCRIPT_NAME is deliberately left
| alone: it is what tells the app which sub-directory it is mounted in, which
| AppServiceProvider uses to fix up generated URLs.
|
*/

if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

require_once __DIR__.'/public/index.php';