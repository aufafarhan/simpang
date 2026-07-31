<?php

/**
 * Koneksi database untuk skrip perawatan.
 * Kredensial dibaca dari file .env di root proyek — TIDAK ditulis di kode.
 */

function bacaEnv(string $path): array
{
    if (! is_file($path)) {
        fwrite(STDERR, "GAGAL: file .env tidak ditemukan di {$path}\n");
        exit(1);
    }

    $env = [];

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $baris) {
        $baris = trim($baris);
        if ($baris === '' || str_starts_with($baris, '#') || ! str_contains($baris, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $baris, 2);
        $env[trim($k)] = trim($v, " \t\n\r\0\x0B\"'");
    }

    return $env;
}

$env = bacaEnv(dirname(__DIR__) . '/.env');

define('DB_HOST', $env['DB_HOST'] ?? '127.0.0.1');
define('DB_PORT', (int) ($env['DB_PORT'] ?? 3306));
define('DB_NAME', $env['DB_DATABASE'] ?? '');
define('DB_USER', $env['DB_USERNAME'] ?? 'root');
define('DB_PASS', $env['DB_PASSWORD'] ?? '');

function koneksiDariEnv(): mysqli
{
    mysqli_report(MYSQLI_REPORT_OFF);
    $db = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if ($db->connect_errno) {
        fwrite(STDERR, 'GAGAL konek database: ' . $db->connect_error . "\n");
        exit(1);
    }

    $db->set_charset('utf8mb4');

    return $db;
}
