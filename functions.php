<?php
require_once __DIR__ . '/config/database.php';

// session dipakai untuk flash message & isian form terakhir
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// harus sama dengan CHECK di database/schema.sql
const DAFTAR_STATUS = ['Belum Dibaca', 'Sedang Dibaca', 'Selesai'];

// daftar pesan notifikasi: kode => [warna alert, isi pesan]
const DAFTAR_PESAN = [
    'tambah'      => ['success', 'Buku berhasil ditambahkan.'],
    'ubah'        => ['success', 'Data buku berhasil diubah.'],
    'status'      => ['success', 'Status buku berhasil diperbarui.'],
    'hapus'       => ['success', 'Buku berhasil dihapus.'],
    'gagal'       => ['danger', 'Data belum lengkap atau tidak valid.'],
    'tidak_ada'   => ['warning', 'Data buku tidak ditemukan.'],
];


/* ---------- helper ---------- */

// escape output html
function e(?string $teks): string
{
    return htmlspecialchars($teks ?? '', ENT_QUOTES, 'UTF-8');
}

// 2026-10-17 -> 17-10-2026
function formatTanggal(string $tanggal): string
{
    return date('d-m-Y', strtotime($tanggal));
}

// pindah halaman, script langsung berhenti
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

// simpan pesan (dan isian form kalau gagal) ke session lalu redirect
function kembali(string $kodePesan, string $url = 'index.php', ?array $input = null): never
{
    $_SESSION['pesan'] = $kodePesan;
    if ($input !== null) {
        $_SESSION['input_lama'] = $input;
    }
    redirect($url);
}

// ambil isi session sekali pakai, langsung dihapus biar tidak muncul lagi saat refresh
function ambilFlash(string $kunci): mixed
{
    $nilai = $_SESSION[$kunci] ?? null;
    unset($_SESSION[$kunci]);
    return $nilai;
}

// hasil: [warna alert, teks] atau null
function ambilPesan(): ?array
{
    return DAFTAR_PESAN[ambilFlash('pesan') ?? ''] ?? null;
}

// class badge bootstrap sesuai status
function warnaStatus(string $status): string
{
    return match ($status) {
        'Selesai'       => 'text-bg-success',
        'Sedang Dibaca' => 'text-bg-warning',
        default         => 'text-bg-secondary',
    };
}

// hidden input untuk form aksi (dipakai di checkbox, hapus, edit)
function inputAksi(string $aksi, ?int $id = null): string
{
    $html = '<input type="hidden" name="aksi" value="' . e($aksi) . '">';
    if ($id !== null) {
        $html .= '<input type="hidden" name="id" value="' . $id . '">';
    }
    return $html;
}


/* ---------- validasi ---------- */

// rapikan data dari form
function ambilInputBuku(array $input): array
{
    return [
        'judul'          => trim($input['judul'] ?? ''),
        'penulis'        => trim($input['penulis'] ?? ''),
        'target_selesai' => trim($input['target_selesai'] ?? ''),
        'status'         => $input['status'] ?? 'Belum Dibaca',
    ];
}

// cek format Y-m-d, sekalian nolak tanggal yang tidak ada (mis. 2026-02-30)
function tanggalValid(string $tanggal): bool
{
    $tgl = DateTime::createFromFormat('Y-m-d', $tanggal);
    return $tgl && $tgl->format('Y-m-d') === $tanggal;
}

// aturan validasi untuk tambah & ubah
function validasiBuku(array $data): bool
{
    return $data['judul'] !== ''
        && $data['penulis'] !== ''
        && mb_strlen($data['judul']) <= 100
        && mb_strlen($data['penulis']) <= 100
        && tanggalValid($data['target_selesai'])
        && in_array($data['status'], DAFTAR_STATUS, true);
}


/* ---------- query data buku ---------- */
// semua query pakai prepared statement (?) biar aman dari sql injection

// hasilnya array, dipakai sebagai $tasks di index.php
function ambilSemuaBuku(): array
{
    return getConnection()->query('SELECT * FROM buku ORDER BY id')->fetchAll();
}

function ambilBuku(int $id): ?array
{
    $stmt = getConnection()->prepare('SELECT * FROM buku WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function tambahBuku(array $data): void
{
    $stmt = getConnection()->prepare(
        'INSERT INTO buku (judul, penulis, target_selesai, status) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$data['judul'], $data['penulis'], $data['target_selesai'], $data['status']]);
}

// return false kalau id tidak ada
function ubahBuku(int $id, array $data): bool
{
    $stmt = getConnection()->prepare(
        "UPDATE buku SET judul = ?, penulis = ?, target_selesai = ?, status = ?,
         diubah_pada = datetime('now', 'localtime') WHERE id = ?"
    );
    $stmt->execute([$data['judul'], $data['penulis'], $data['target_selesai'], $data['status'], $id]);
    return $stmt->rowCount() > 0;
}

// dipanggil dari checkbox, cuma update kolom status
function ubahStatus(int $id, string $status): bool
{
    $stmt = getConnection()->prepare(
        "UPDATE buku SET status = ?, diubah_pada = datetime('now', 'localtime') WHERE id = ?"
    );
    $stmt->execute([$status, $id]);
    return $stmt->rowCount() > 0;
}

// return false kalau id tidak ada
function hapusBuku(int $id): bool
{
    $stmt = getConnection()->prepare('DELETE FROM buku WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->rowCount() > 0;
}
