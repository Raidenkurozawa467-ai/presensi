<?php
declare(strict_types=1);

/**
 * Koneksi PDO (mode ERRMODE_EXCEPTION) dibungkus dalam class Database (singleton).
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $c = require __DIR__ . '/../config/config.php';
        $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            // Samakan zona waktu DB dengan WITA agar CURRENT_TIMESTAMP sesuai jam sekolah
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+08:00'",
        ]);
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            try {
                self::$instance = new Database();
            } catch (PDOException $e) {
                http_response_code(500);
                exit('Koneksi database gagal. Pastikan MariaDB berjalan, schema.sql sudah diimpor, dan config/config.php benar.');
            }
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
