<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Makassar'); // WITA (Samarinda)
session_start();

spl_autoload_register(function (string $class): void {
    $file = __DIR__ . '/../classes/' . $class . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

require_once __DIR__ . '/functions.php';
