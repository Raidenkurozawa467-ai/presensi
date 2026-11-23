<?php
require __DIR__ . '/includes/bootstrap.php';
requireLogin();

$isValidDate = static function (string $d): bool {
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt !== false && $dt->format('Y-m-d') === $d;
};

// Bawaan: presensi hari ini. Jika kolom tanggal dikosongkan -> semua tanggal.
$tanggal  = isset($_GET['tanggal']) ? trim($_GET['tanggal']) : date('Y-m-d');
$idKelas  = ($_GET['id_kelas'] ?? '') !== '' ? (int) $_GET['id_kelas'] : '';
$q        = trim($_GET['q'] ?? '');
$filter   = ['tanggal' => $tanggal, 'id_kelas' => $idKelas, 'q' => $q];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Hapus log (log tidak dapat diubah). Filter dipertahankan setelah hapus.
    $ok = (new Presensi((int) ($_POST['id'] ?? 0)))->delete();
    flash($ok ? 'success' : 'error', $ok ? 'Log presensi berhasil dihapus.' : 'Log presensi tidak ditemukan.');
    redirect('rekap.php?' . http_build_query([
        'tanggal'  => $_POST['tanggal'] ?? '',
        'id_kelas' => $_POST['id_kelas'] ?? '',
        'q'        => $_POST['q'] ?? '',
    ]));
}

$dateError = '';
if ($tanggal !== '' && !$isValidDate($tanggal)) {
    $dateError = 'Format tanggal tidak valid.';
    $filter['tanggal'] = '';
    $tanggal = '';
}

$kelasList = (new Kelas())->getAll();
$daftar    = (new Presensi())->getAll($filter);

$pageTitle = 'Rekap Presensi';
$active = 'rekap';
require __DIR__ . '/includes/header.php';
?>
<h1>Rekap Presensi</h1>
<?php if ($dateError): ?><div class="alert alert-error"><?= e($dateError) ?></div><?php endif; ?>

<form method="get" class="filter card">
    <div>
        <label for="tanggal">Tanggal</label>
        <input type="date" id="tanggal" name="tanggal" value="<?= e($tanggal) ?>">
    </div>
    <div>
        <label for="id_kelas">Kelas</label>
        <select id="id_kelas" name="id_kelas">
            <option value="">Semua kelas</option>
            <?php foreach ($kelasList as $k): ?>
                <option value="<?= (int) $k['id_kelas'] ?>" <?= (string) $idKelas === (string) $k['id_kelas'] ? 'selected' : '' ?>>
                    <?= e($k['nama_kelas']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="q">Cari nama / NISN</label>
        <input type="text" id="q" name="q" value="<?= e($q) ?>" placeholder="Sebagian nama atau NISN">
    </div>
    <div class="actions">
        <button type="submit" class="btn">Terapkan</button>
        <a href="rekap.php" class="btn btn-outline">Hari ini</a>
    </div>
</form>

<p class="muted">
    <?= $filter['tanggal'] !== '' ? 'Tanggal ' . e(date('d/m/Y', strtotime($filter['tanggal']))) : 'Semua tanggal' ?>
    &middot; <?= count($daftar) ?> log presensi
</p>

<div class="table-wrap">
    <table>
        <thead><tr><th>No</th><th>NISN</th><th>Nama</th><th>Kelas</th><th>Waktu Tap</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php if (!$daftar): ?>
            <tr><td colspan="6" class="empty">Tidak ada data presensi untuk filter ini.</td></tr>
        <?php endif; ?>
        <?php foreach ($daftar as $i => $p): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= e($p['nisn']) ?></td>
                <td><?= e($p['nama']) ?></td>
                <td><?= e($p['nama_kelas']) ?></td>
                <td><?= e(formatWaktu($p['tanggal_waktu'])) ?></td>
                <td class="aksi">
                    <form method="post" onsubmit="return confirm('Hapus log presensi ini?')">
                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <input type="hidden" name="tanggal" value="<?= e($tanggal) ?>">
                        <input type="hidden" name="id_kelas" value="<?= e((string) $idKelas) ?>">
                        <input type="hidden" name="q" value="<?= e($q) ?>">
                        <button type="submit" class="btn btn-small btn-danger">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
