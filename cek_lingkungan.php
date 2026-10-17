<?php
// halaman cek lingkungan: versi php, ekstensi sqlite, koneksi & tabel database
// buka: http://localhost/todolist/cek_lingkungan.php

require_once __DIR__ . '/config/database.php';

$hasil = [];

$hasil['Versi PHP'] = PHP_VERSION;
$hasil['Ekstensi pdo_sqlite'] = extension_loaded('pdo_sqlite') ? 'Aktif' : 'TIDAK AKTIF';

try {
    $db = getConnection();
    $hasil['Versi SQLite'] = $db->query('SELECT sqlite_version()')->fetchColumn();
    $hasil['File database'] = realpath(DB_PATH);

    $tabel = $db->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'buku'")->fetchColumn();
    $hasil['Tabel buku'] = $tabel ? 'Tersedia' : 'Belum ada';
    $hasil['Jumlah data buku'] = $db->query('SELECT COUNT(*) FROM buku')->fetchColumn();
    $status = 'BERHASIL';
} catch (PDOException $e) {
    $hasil['Error'] = $e->getMessage();
    $status = 'GAGAL';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Lingkungan - Rencana Baca Buku</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 640px; margin: 40px auto; padding: 0 16px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: left; }
        th { background: #f3f3f3; width: 40%; }
        .ok { color: #1a7f37; } .gagal { color: #cf222e; }
    </style>
</head>
<body>
    <h1>Cek Lingkungan Pengembangan</h1>
    <p>Status koneksi database:
        <strong class="<?= $status === 'BERHASIL' ? 'ok' : 'gagal' ?>"><?= $status ?></strong>
    </p>
    <table>
        <?php foreach ($hasil as $label => $nilai): ?>
            <tr>
                <th><?= htmlspecialchars($label) ?></th>
                <td><?= htmlspecialchars((string) $nilai) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
