<?php
// handle semua aksi dari form (tambah, ubah, status, hapus)
require_once __DIR__ . '/functions.php';

// aksi yang ubah data wajib lewat POST, akses GET langsung dibalikin
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$aksi = $_POST['aksi'] ?? '';
$id   = (int) ($_POST['id'] ?? 0); // dari hidden input, dipaksa int

switch ($aksi) {
    case 'tambah':
        $data = ambilInputBuku($_POST);
        if (!validasiBuku($data)) {
            kembali('gagal', 'index.php', $data);
        }
        tambahBuku($data);
        kembali('tambah');

    case 'ubah':
        $data = ambilInputBuku($_POST);
        if (!validasiBuku($data)) {
            kembali('gagal', "index.php?edit=$id", $data);
        }
        kembali(ubahBuku($id, $data) ? 'ubah' : 'tidak_ada');

    case 'status':
        // checkbox yang tidak dicentang tidak ikut terkirim
        $status = isset($_POST['selesai']) ? 'Selesai' : 'Belum Dibaca';
        kembali(ubahStatus($id, $status) ? 'status' : 'tidak_ada');

    case 'hapus':
        kembali(hapusBuku($id) ? 'hapus' : 'tidak_ada');

    default:
        redirect('index.php');
}
