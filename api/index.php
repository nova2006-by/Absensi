<?php

// Foto bukti disimpan di /tmp karena filesystem Vercel read-only; tanpa ini
// Storage::disk('public')->store() gagal membuat folder uploads dan aksi
// tambah pelanggaran berujung 500. Catatan: /tmp bersifat sementara,
// berkas hilang bila instance dingin (lihat README soal penyimpanan objek).
if (! is_dir('/tmp/uploads')) {
    mkdir('/tmp/uploads', 0777, true);
}
putenv('UPLOADS_ROOT=/tmp/uploads');
$_ENV['UPLOADS_ROOT'] = '/tmp/uploads';
$_SERVER['UPLOADS_ROOT'] = '/tmp/uploads';

// Sajikan berkas statis dari /public lewat entrypoint ini.
// Deployment yang datang dari GitHub tidak punya lapisan file statis di
// Vercel, jadi CSS/JS/foto disajikan di sini (seperti Apache pada container
// lama). Berkas PHP tidak pernah disajikan agar source code tidak bocor.
$uri = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));

if ($uri !== '' && ! str_ends_with(strtolower($uri), '.php')) {
    $public = realpath(__DIR__ . '/../public');
    $kandidat = [];

    if ($public !== false) {
        $kandidat[] = [realpath($public . $uri), $public];
    }

    // Unggahan (mis. /uploads/pelanggaran/x.jpg) ada di UPLOADS_ROOT.
    if (str_starts_with($uri, '/uploads/')) {
        $uploadsRoot = realpath(getenv('UPLOADS_ROOT') ?: (__DIR__ . '/../public/uploads'));
        if ($uploadsRoot !== false) {
            $kandidat[] = [
                realpath(rtrim($uploadsRoot, '/\\') . substr($uri, strlen('/uploads'))),
                $uploadsRoot,
            ];
        }
    }

    $file = false;
    foreach ($kandidat as [$cand, $candRoot]) {
        if ($cand !== false
            && is_file($cand)
            && str_starts_with($cand, $candRoot . DIRECTORY_SEPARATOR)) {
            $file = $cand;
            break;
        }
    }

    if ($file !== false) {
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

// Hasil kompilasi view yang dibuat saat build ikut disalin ke /tmp, sehingga
// cold start tidak perlu mengompilasi ulang seluruh template.
$compiled = __DIR__ . '/../storage/framework/views';
if (is_dir($compiled)) {
    foreach (scandir($compiled) ?: [] as $entry) {
        if (str_ends_with($entry, '.php') && ! file_exists('/tmp/storage/framework/views/' . $entry)) {
            copy($compiled . '/' . $entry, '/tmp/storage/framework/views/' . $entry);
        }
    }
}

// Cache config & route dibuat saat build di bootstrap/cache, jadi path-nya
// tidak dialihkan ke /tmp. Cache yang bisa ditulis saat runtime tetap /tmp.
// Config dan route sengaja TIDAK di-cache saat build: keduanya membakar path
// absolut folder build (/vercel/path0) yang tidak ada di runtime
// (/var/task/user) sehingga halaman error. View cache aman karena nama view
// bersifat relatif.
foreach ([
    'APP_PACKAGES_CACHE'  => '/tmp/packages.php',
    'APP_SERVICES_CACHE'  => '/tmp/services.php',
    'APP_CONFIG_CACHE'    => '/tmp/config.php',
    'APP_ROUTES_CACHE'    => '/tmp/routes.php',
    'APP_EVENTS_CACHE'    => '/tmp/events.php',
    'VIEW_COMPILED_PATH'  => '/tmp/storage/framework/views',
] as $key => $value) {
    putenv("$key=$value");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->useStoragePath('/tmp/storage');

$app->handleRequest(Illuminate\Http\Request::capture());