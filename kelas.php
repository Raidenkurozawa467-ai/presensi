<?php
require __DIR__ . '/includes/bootstrap.php';
requireLogin();

$aksi   = $_GET['aksi'] ?? 'list';
$errors = [];
$form   = ['id_kelas' => '', 'nama_kelas' => '', 'jurusan' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = $_POST['do'] ?? '';

    if ($do === 'hapus') {
        try {
            (new Kelas((int) ($_POST['id_kelas'] ?? 0)))->delete();
            flash('success', 'Kelas berhasil dihapus.');
        } catch (RuntimeException $ex) {
            flash('error', $ex->getMessage());
        }
        redirect('kelas.php');
    }

    if ($do === 'simpan') {
        $id = ($_POST['id_kelas'] ?? '') !== '' ? (int) $_POST['id_kelas'] : null;
        $kelas = new Kelas($id, $_POST['nama_kelas'] ?? '', $_POST['jurusan'] ?? '');
        $errors = $kelas->validate();
        if (!$errors) {
            if ($id === null) {
                $kelas->create();
                flash('success', 'Kelas berhasil ditambahkan.');
            } else {
                $kelas->update();
                flash('success', 'Kelas berhasil diperbarui.');
            }
            redirect('kelas.php');
        }
        // Gagal validasi: tampilkan lagi form dengan isian yang sama
        $form = ['id_kelas' => (string) $id, 'nama_kelas' => $kelas->getNamaKelas(), 'jurusan' => $kelas->getJurusan()];
        $aksi = $id === null ? 'tambah' : 'ubah';
    }
} elseif ($aksi === 'ubah') {
    $data = (new Kelas())->find((int) ($_GET['id'] ?? 0));
    if ($data === null) {
        flash('error', 'Kelas tidak ditemukan.');
        redirect('kelas.php');
    }
    $form = ['id_kelas' => (string) $data['id_kelas'], 'nama_kelas' => $data['nama_kelas'], 'jurusan' => $data['jurusan']];
}

$pageTitle = 'Kelola Kelas';
$active = 'kelas';
require __DIR__ . '/includes/header.php';
?>
<?php if ($aksi === 'tambah' || $aksi === 'ubah'): ?>
    <h1><?= $aksi === 'tambah' ? 'Tambah Kelas' : 'Ubah Kelas' ?></h1>
    <form method="post" class="card" novalidate>
        <input type="hidden" name="do" value="simpan">
        <input type="hidden" name="id_kelas" value="<?= e($form['id_kelas']) ?>">

        <label for="nama_kelas">Nama Kelas</label>
        <input type="text" id="nama_kelas" name="nama_kelas" maxlength="20" value="<?= e($form['nama_kelas']) ?>">
        <?= fieldError($errors, 'nama_kelas') ?>

        <label for="jurusan">Jurusan</label>
        <input type="text" id="jurusan" name="jurusan" maxlength="50" value="<?= e($form['jurusan']) ?>">
        <?= fieldError($errors, 'jurusan') ?>

        <div class="actions">
            <button type="submit" class="btn">Simpan</button>
            <a href="kelas.php" class="btn btn-outline">Batal</a>
        </div>
    </form>
<?php else: $daftar = (new Kelas())->getAll(); ?>
    <div class="page-head">
        <h1>Kelola Kelas</h1>
        <a href="kelas.php?aksi=tambah" class="btn">+ Tambah Kelas</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>No</th><th>Nama Kelas</th><th>Jurusan</th><th>Jumlah Siswa</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if (!$daftar): ?>
                <tr><td colspan="5" class="empty">Belum ada data kelas.</td></tr>
            <?php endif; ?>
            <?php foreach ($daftar as $i => $k): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($k['nama_kelas']) ?></td>
                    <td><?= e($k['jurusan']) ?></td>
                    <td><?= (int) $k['jumlah_siswa'] ?></td>
                    <td class="aksi">
                        <a href="kelas.php?aksi=ubah&id=<?= (int) $k['id_kelas'] ?>" class="btn btn-small">Ubah</a>
                        <form method="post" onsubmit="return confirm('Hapus kelas ini?')">
                            <input type="hidden" name="do" value="hapus">
                            <input type="hidden" name="id_kelas" value="<?= (int) $k['id_kelas'] ?>">
                            <button type="submit" class="btn btn-small btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
