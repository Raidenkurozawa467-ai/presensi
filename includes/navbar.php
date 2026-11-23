<?php
$menu = [
    'index'   => ['index.php',  'Beranda'],
    'kelas'   => ['kelas.php',  'Kelas'],
    'siswa'   => ['siswa.php',  'Siswa'],
    'tap'     => ['tap.php',    'Tap Kartu'],
    'rekap'   => ['rekap.php',  'Rekap Presensi'],
];
?>
<nav class="navbar">
    <div class="nav-inner">
        <span class="brand">Presensi Siswa</span>
        <ul class="nav-links">
            <?php foreach ($menu as $key => [$url, $label]): ?>
                <li><a href="<?= e($url) ?>" class="<?= $active === $key ? 'active' : '' ?>"><?= e($label) ?></a></li>
            <?php endforeach; ?>
        </ul>
        <form method="post" action="logout.php" class="logout-form">
            <span class="user"><?= e($_SESSION['username']) ?></span>
            <button type="submit" class="btn btn-small btn-outline">Logout</button>
        </form>
    </div>
</nav>
