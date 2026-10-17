<?php
require_once __DIR__ . '/functions.php';

$tasks     = ambilSemuaBuku();
$pesan     = ambilPesan();
$inputLama = ambilFlash('input_lama');

// mode edit kalau ada ?edit=id
$bukuEdit = isset($_GET['edit']) ? ambilBuku((int) $_GET['edit']) : null;
$modeEdit = $bukuEdit !== null;

if (isset($_GET['edit']) && !$modeEdit) {
    $pesan = DAFTAR_PESAN['tidak_ada'];
}

// nilai form: default -> data yang diedit -> isian terakhir kalau validasi gagal
$formKosong = ['id' => null, 'judul' => '', 'penulis' => '', 'target_selesai' => '', 'status' => 'Belum Dibaca'];
$form = array_merge($formKosong, $bukuEdit ?? [], $inputLama ?? []);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rencana Baca Buku</title>
    <link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

    <!-- header -->
    <header class="header-app text-white text-center py-3 mb-4">
        <h1 class="h3 mb-0">Rencana Baca Buku</h1>
    </header>

    <main class="container konten-app">

        <?php if ($pesan): ?>
            <div class="alert alert-<?= $pesan[0] ?> py-2"><?= e($pesan[1]) ?></div>
        <?php endif; ?>

        <!-- form tambah / edit buku -->
        <div class="card mb-4">
            <div class="card-header fw-semibold"><?= $modeEdit ? 'Edit Buku' : 'Tambah Buku' ?></div>
            <div class="card-body">
                <form method="post" action="proses.php" class="row g-3">
                    <!-- form yang sama dipakai untuk tambah & edit -->
                    <?= inputAksi($modeEdit ? 'ubah' : 'tambah', $form['id']) ?>

                    <div class="col-md-6">
                        <label for="judul" class="form-label">Judul Buku</label>
                        <input type="text" id="judul" name="judul" class="form-control" maxlength="100"
                               value="<?= e($form['judul']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="penulis" class="form-label">Penulis</label>
                        <input type="text" id="penulis" name="penulis" class="form-control" maxlength="100"
                               value="<?= e($form['penulis']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="target_selesai" class="form-label">Target Selesai</label>
                        <input type="date" id="target_selesai" name="target_selesai" class="form-control"
                               value="<?= e($form['target_selesai']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            <?php foreach (DAFTAR_STATUS as $status): ?>
                                <option value="<?= e($status) ?>" <?= $status === $form['status'] ? 'selected' : '' ?>>
                                    <?= e($status) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-success">
                            <?= $modeEdit ? 'Simpan Perubahan' : 'Tambah' ?>
                        </button>
                        <?php if ($modeEdit): ?>
                            <a href="index.php" class="btn btn-secondary">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- daftar buku -->
        <div class="card mb-4">
            <div class="card-header fw-semibold">Daftar Buku</div>
            <div class="card-body">
                <?php if (empty($tasks)): ?>
                    <p class="text-muted text-center mb-0">Belum ada buku.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">Selesai</th>
                                    <th>No</th>
                                    <th>Judul</th>
                                    <th>Penulis</th>
                                    <th>Target Selesai</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tasks as $nomor => $task): ?>
                                    <?php $selesai = $task['status'] === 'Selesai'; ?>
                                    <tr>
                                        <td class="text-center">
                                            <!-- checkbox langsung submit begitu diklik -->
                                            <form method="post" action="proses.php">
                                                <?= inputAksi('status', $task['id']) ?>
                                                <input type="checkbox" name="selesai" class="form-check-input"
                                                       onchange="this.form.submit()" <?= $selesai ? 'checked' : '' ?>>
                                            </form>
                                        </td>
                                        <td><?= $nomor + 1 ?></td>
                                        <td class="<?= $selesai ? 'text-decoration-line-through text-muted' : '' ?>">
                                            <?= e($task['judul']) ?>
                                        </td>
                                        <td><?= e($task['penulis']) ?></td>
                                        <td><?= formatTanggal($task['target_selesai']) ?></td>
                                        <td><span class="badge <?= warnaStatus($task['status']) ?>"><?= e($task['status']) ?></span></td>
                                        <td class="text-nowrap">
                                            <a href="index.php?edit=<?= $task['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <!-- hapus lewat POST + konfirmasi dulu -->
                                            <form method="post" action="proses.php" class="d-inline"
                                                  onsubmit="return confirm('Hapus buku ini?')">
                                                <?= inputAksi('hapus', $task['id']) ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </main>

</body>
</html>
