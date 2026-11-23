<?php
/** Variabel: $pageTitle, $active (opsional) */
$pageTitle = $pageTitle ?? 'Presensi Siswa';
$active = $active ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> - Presensi Siswa</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php if (isLoggedIn()) { require __DIR__ . '/navbar.php'; } ?>
<main class="container">
<?php showFlash(); ?>
