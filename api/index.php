<?php

// Sajikan berkas statis dari /public lewat entrypoint ini.
// Deployment yang datang dari GitHub tidak punya lapisan file statis di
// Vercel, jadi CSS/JS/foto disajikan di sini (seperti Apache pada container
// lama). Berkas PHP tidak pernah disajikan agar source code tidak bocor.
$uri = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));

if ($uri !== '' && ! str_ends_with(strtolower($uri), '.php')) {
    $public = realpath(__DIR__ . '/../public');
    $file = $public === false ? false : realpath($public . $uri);

    if ($file !== false
        && str_starts_with($file, $public . DIRECTORY_SEPARATOR)
        && is_file($file)) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $types = [
            'css' => 'text/css; charset=UTF-8',
            'js' => 'text/javascript; charset=UTF-8',
            'mjs' => 'text/javascript; charset=UTF-8',
            'json' => 'application/json; charset=UTF-8',
            'map' => 'application/json; charset=UTF-8',
            'txt' => 'text/plain; charset=UTF-8',
            'html' => 'text/html; charset=UTF-8',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'otf' => 'font/otf',
            'eot' => 'application/vnd.ms-fontobject',
        ];
        $type = $types[$ext]
            ?? (function_exists('mime_content_type') ? mime_content_type($file) : null)
            ?? 'application/octet-stream';
        $cache = str_starts_with($uri, '/build/')
            ? 'public, max-age=31536000, immutable'
            : 'public, max-age=3600';

        header('Content-Type: ' . $type);
        header('X-Content-Type-Options: nosniff');
        header('Content-Length: ' . filesize($file));
        header('Cache-Control: ' . $cache);
        readfile($file);
        exit;
    }
}

// Filesystem Vercel read-only, jadi semua yang ditulis Laravel harus di /tmp
foreach (['framework/cache/data', 'framework/sessions', 'framework/views', 'logs'] as $dir) {
    if (!is_dir("/tmp/storage/$dir")) {
        mkdir("/tmp/storage/$dir", 0777, true);
    }
}

foreach ([
    'APP_PACKAGES_CACHE' => '/tmp/packages.php',
    'APP_SERVICES_CACHE' => '/tmp/services.php',
    'APP_CONFIG_CACHE'   => '/tmp/config.php',
    'APP_ROUTES_CACHE'   => '/tmp/routes.php',
    'APP_EVENTS_CACHE'   => '/tmp/events.php',
] as $key => $value) {
    putenv("$key=$value");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->useStoragePath('/tmp/storage');

$app->handleRequest(Illuminate\Http\Request::capture());