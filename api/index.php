<?php

/**
 * Vercel Serverless Entry Point for Laravel
 *
 * Vercel's filesystem is read-only except /tmp.
 * We redirect Laravel's writable directories to /tmp so compiled views,
 * cache, and logs work inside the ephemeral function container.
 */

define('LARAVEL_START', microtime(true));

// ── Ensure writable directories exist in /tmp ─────────────────────────────
$storage = '/tmp/laravel_storage';

foreach ([
    "$storage/app/public",
    "$storage/framework/cache/data",
    "$storage/framework/sessions",
    "$storage/framework/views",
    "$storage/logs",
] as $dir) {
    is_dir($dir) || mkdir($dir, 0755, true);
}

// ── Bootstrap ─────────────────────────────────────────────────────────────
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

// Point writable paths to /tmp so views can compile, logs can write, etc.
$app->useStoragePath($storage);

// ── Handle the request ────────────────────────────────────────────────────
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
)->send();

$kernel->terminate($request, $response);
