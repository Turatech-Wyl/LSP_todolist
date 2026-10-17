<?php
// koneksi database sqlite

define('DB_PATH', __DIR__ . '/../database/rencana_baca.sqlite');
define('SCHEMA_PATH', __DIR__ . '/../database/schema.sql');

function getConnection(): PDO
{
    // static: koneksi cukup dibuat sekali per request
    static $pdo = null;

    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // buat tabel kalau belum ada
        $pdo->exec(file_get_contents(SCHEMA_PATH));
    }

    return $pdo;
}
