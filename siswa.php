<?php
require __DIR__ . '/includes/bootstrap.php';
requireLogin();

$aksi   = $_GET['aksi'] ?? 'list';
$errors = [];
$form   = ['mode' => 'baru', 'nisn' => '', 'nama' => '', 'alamat' => '', 'id_kelas' => ''];
$kelasList = (new Kelas())->getAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = $_POST['do'] ?? '';

    if ($do === 'hapus') {
        try {
            (new Siswa($_POST['nisn'] ?? ''))->delete();
            flash('success', 'Siswa berhasil dihapus.');
        } catch (RuntimeException $ex) {
            flash('error', $ex->getMessage());
        }
        redirect('siswa.php');
    }

    if ($do === 'simpan') {
        $isNew   = ($_POST['mode'] ?? 'baru') === 'baru';
        $idKelas = ($_POST['id_kelas'] ?? '') !== '' ? (int) $_POST['id_kelas'] : null;
        $siswa   = new Siswa($_POST['nisn'] ?? '', $_POST['nama'] ?? '', $_POST['alamat'] ?? '', $idKelas);
        $errors  = $siswa->validate($isNew);
        if (!$errors) {
            if ($isNew) {
                $siswa->create();
                flash('success', 'Siswa berhasil ditambahkan.');
            } else {
                $siswa->update();
                flash('success', 'Data siswa berhasil diperbarui.');
            }
            redirect('siswa.php');
        }
        // Gagal validasi: isian tidak hilang
        $form = [
            'mode' => $isNew ? 'baru' : 'ubah',
            'nisn' => $siswa->getNisn(), 'nama' => $siswa->getNama(),
            'alamat' => $siswa->getAlamat(), 'id_kelas' => $idKelas === null ? '' : (string) $idKelas,
        ];
        $aksi = $isNew ? 'tambah' : 'ubah';
    }
} elseif ($aksi === 'ubah') {
    $data = (new Siswa())->find($_GET['nisn'] ?? '');
    if ($data === null) {
        flash('error', 'Siswa tidak ditemukan.');
        redirect('siswa.php');
    }
    $form = ['mode' => 'ubah', 'nisn' => $data['nisn'], 'nama' => $data['nama'],
             'alamat' => $data['alamat'], 'id_kelas' => (string) $data['id_kelas']];
}

$pageTitle = 'Kelola Siswa';
$active = 'siswa';
require __DIR__ . '/includes/header.php';
?>
<?php if ($aksi === 'tambah' || $aksi === 'ubah'): ?>
    <h1><?= $aksi === 'tambah' ? 'Tambah Siswa' : 'Ubah Siswa' ?></h1>
    <form method="post" class="card" novalidate>
        <input type="hidden" name="do" value="simpan">
        <input type="hidden" name="mode" value="<?= e($form['mode']) ?>">

        <label for="nisn">NISN (10 digit angka)</label>
        <!-- type="text" agar angka 0 di depan tidak hilang -->
        <input type="text" id="nisn" name="nisn" inputmode="numeric" maxlength="10"
               value="<?= e($form['nisn']) ?>" <?= $form['mode'] === 'ubah' ? 'readonly' : '' ?>>
        <?= fieldError($errors, 'nisn') ?>

        <label for="nama">Nama</label>
        <input type="text" id="nama" name="nama" maxlength="100" value="<?= e($form['nama']) ?>">
        <?= fieldError($errors, 'nama') ?>

        <label for="alamat">Alamat <small>(boleh kosong)</small></label>
        <input type="text" id="alamat" name="alamat" maxlength="255" value="<?= e($form['alamat']) ?>">
        <?= fieldError($errors, 'alamat') ?>

        <label for="id_kelas">Kelas</label>
        <select id="id_kelas" name="id_kelas">
            <option value="">-- Pilih kelas --</option>
            <?php foreach ($kelasList as $k): ?>
                <option value="<?= (int) $k['id_kelas'] ?>" <?= $form['id_kelas'] === (string) $k['id_kelas'] ? 'selected' : '' ?>>
                    <?= e($k['nama_kelas']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?= fieldError($errors, 'id_kelas') ?>

        <div class="actions">
            <button type="submit" class="btn">Simpan</button>
            <a href="siswa.php" class="btn btn-outline">Batal</a>
        </div>
    </form>
<?php else:
    $q = trim($_GET['q'] ?? '');
    $daftar = (new Siswa())->getAll($q);
?>
    <div class="page-head">
        <h1>Kelola Siswa</h1>
        <a href="siswa.php?aksi=tambah" class="btn">+ Tambah Siswa</a>
    </div>

    <form method="get" class="filter card">
        <input type="text" name="q" placeholder="Cari nama atau NISN..." value="<?= e($q) ?>">
        <button type="submit" class="btn">Cari</button>
        <?php if ($q !== ''): ?><a href="siswa.php" class="btn btn-outline">Reset</a><?php endif; ?>
    </form>

    <div class="table-wrap">
        <table>
            <thead><tr><th>No</th><th>NISN</th><th>Nama</th><th>Kelas</th><th>Alamat</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if (!$daftar): ?>
                <tr><td colspan="6" class="empty"><?= $q !== '' ? 'Tidak ada siswa yang cocok dengan pencarian.' : 'Belum ada data siswa.' ?></td></tr>
            <?php endif; ?>
            <?php foreach ($daftar as $i => $s): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($s['nisn']) ?></td>
                    <td><?= e($s['nama']) ?></td>
                    <td><?= e($s['nama_kelas']) ?></td>
                    <td><?= e($s['alamat'] !== '' ? $s['alamat'] : '-') ?></td>
                    <td class="aksi">
                        <a href="siswa.php?aksi=ubah&nisn=<?= urlencode($s['nisn']) ?>" class="btn btn-small">Ubah</a>
                        <form method="post" onsubmit="return confirm('Hapus siswa ini?')">
                            <input type="hidden" name="do" value="hapus">
                            <input type="hidden" name="nisn" value="<?= e($s['nisn']) ?>">
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
