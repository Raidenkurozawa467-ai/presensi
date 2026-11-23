<?php
require __DIR__ . '/includes/bootstrap.php';
requireLogin();

$jumlahKelas = count((new Kelas())->getAll());
$jumlahSiswa = count((new Siswa())->getAll());
$hadir       = (new Presensi())->countHadirHariIni();

$pageTitle = 'Beranda';
$active = 'index';
require __DIR__ . '/includes/header.php';
?>
<h1>Beranda</h1>
<p>Selamat datang, <strong><?= e($_SESSION['username']) ?></strong>. Hari ini <?= date('d/m/Y') ?>.</p>

<div class="stats">
    <div class="card stat"><span class="num"><?= $hadir ?></span><span>Siswa hadir hari ini</span></div>
    <div class="card stat"><span class="num"><?= $jumlahSiswa ?></span><span>Total siswa</span></div>
    <div class="card stat"><span class="num"><?= $jumlahKelas ?></span><span>Total kelas</span></div>
</div>

<p class="actions">
    <a class="btn" href="tap.php">Simulasi Tap Kartu</a>
    <a class="btn btn-outline" href="rekap.php">Lihat Rekap Hari Ini</a>
</p>
<?php require __DIR__ . '/includes/footer.php'; ?>
