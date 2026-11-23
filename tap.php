<?php
require __DIR__ . '/includes/bootstrap.php';
requireLogin();

$errors = [];
$nisn = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nisn = trim($_POST['nisn'] ?? '');
    $presensi = new Presensi(null, $nisn);
    $errors = $presensi->validate();
    if (!$errors) {
        $baris = $presensi->tap();
        flash('success', sprintf(
            'Presensi tercatat: %s (%s) pada %s.',
            $baris['nama'], $baris['nama_kelas'], formatWaktu($baris['tanggal_waktu'])
        ));
        redirect('tap.php');
    }
}

$pageTitle = 'Simulasi Tap Kartu';
$active = 'tap';
require __DIR__ . '/includes/header.php';
?>
<h1>Simulasi Tap Kartu</h1>
<p>Masukkan NISN seperti yang dikirim mesin RFID. ID dan waktu presensi diisi otomatis oleh database.</p>
<form method="post" class="card" novalidate>
    <label for="nisn">NISN (10 digit angka)</label>
    <input type="text" id="nisn" name="nisn" inputmode="numeric" maxlength="10" value="<?= e($nisn) ?>" autofocus>
    <?= fieldError($errors, 'nisn') ?>
    <div class="actions">
        <button type="submit" class="btn">Tap Kartu</button>
        <a href="rekap.php" class="btn btn-outline">Lihat Rekap</a>
    </div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
