<?php
declare(strict_types=1);

/**
 * Abstract class induk semua model. Menyimpan koneksi PDO (protected)
 * dan mewajibkan setiap model punya getAll().
 */
abstract class BaseModel
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    abstract public function getAll(): array;

    /** Bungkus kata kunci untuk LIKE '%kata%' (karakter % dan _ di-escape). */
    protected function like(string $keyword): string
    {
        return '%' . addcslashes($keyword, '%_\\') . '%';
    }
}
