<?php
declare(strict_types=1);

class Kelas extends BaseModel
{
    private ?int $idKelas;
    private string $namaKelas;
    private string $jurusan;

    public function __construct(?int $idKelas = null, string $namaKelas = '', string $jurusan = '')
    {
        parent::__construct();
        $this->idKelas   = $idKelas;
        $this->namaKelas = trim($namaKelas);
        $this->jurusan   = trim($jurusan);
    }

    // ---- getter / setter ----
    public function getIdKelas(): ?int { return $this->idKelas; }
    public function setIdKelas(?int $v): void { $this->idKelas = $v; }
    public function getNamaKelas(): string { return $this->namaKelas; }
    public function setNamaKelas(string $v): void { $this->namaKelas = trim($v); }
    public function getJurusan(): string { return $this->jurusan; }
    public function setJurusan(string $v): void { $this->jurusan = trim($v); }

    // ---- query ----
    public function getAll(): array
    {
        $sql = "SELECT k.id_kelas, k.nama_kelas, k.jurusan, COUNT(s.nisn) AS jumlah_siswa
                FROM kelas k
                LEFT JOIN siswa s ON s.id_kelas = k.id_kelas
                GROUP BY k.id_kelas, k.nama_kelas, k.jurusan
                ORDER BY k.nama_kelas";
        return $this->db->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM kelas WHERE id_kelas = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** @return array<string,string> pesan error per kolom (kosong = valid) */
    public function validate(): array
    {
        $errors = [];
        if ($this->namaKelas === '') {
            $errors['nama_kelas'] = 'Nama kelas wajib diisi.';
        } elseif (mb_strlen($this->namaKelas) > 20) {
            $errors['nama_kelas'] = 'Nama kelas maksimal 20 karakter.';
        } else {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM kelas WHERE nama_kelas = :n AND id_kelas <> :id"
            );
            $stmt->execute([':n' => $this->namaKelas, ':id' => $this->idKelas ?? 0]);
            if ((int) $stmt->fetchColumn() > 0) {
                $errors['nama_kelas'] = 'Nama kelas sudah dipakai kelas lain.';
            }
        }
        if ($this->jurusan === '') {
            $errors['jurusan'] = 'Jurusan wajib diisi.';
        } elseif (mb_strlen($this->jurusan) > 50) {
            $errors['jurusan'] = 'Jurusan maksimal 50 karakter.';
        }
        return $errors;
    }

    public function create(): bool
    {
        $stmt = $this->db->prepare("INSERT INTO kelas (nama_kelas, jurusan) VALUES (:n, :j)");
        return $stmt->execute([':n' => $this->namaKelas, ':j' => $this->jurusan]);
    }

    public function update(): bool
    {
        $stmt = $this->db->prepare("UPDATE kelas SET nama_kelas = :n, jurusan = :j WHERE id_kelas = :id");
        return $stmt->execute([':n' => $this->namaKelas, ':j' => $this->jurusan, ':id' => $this->idKelas]);
    }

    public function countSiswa(int $id): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM siswa WHERE id_kelas = :id");
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn();
    }

    /** Kelas yang masih memiliki siswa tidak boleh dihapus. */
    public function delete(): void
    {
        $data = $this->idKelas !== null ? $this->find($this->idKelas) : null;
        if ($data === null) {
            throw new RuntimeException('Kelas tidak ditemukan.');
        }
        $jumlah = $this->countSiswa($this->idKelas);
        if ($jumlah > 0) {
            throw new RuntimeException(
                "Kelas {$data['nama_kelas']} tidak dapat dihapus karena masih memiliki {$jumlah} siswa. "
                . 'Pindahkan atau hapus siswanya terlebih dahulu.'
            );
        }
        $stmt = $this->db->prepare("DELETE FROM kelas WHERE id_kelas = :id");
        $stmt->execute([':id' => $this->idKelas]);
    }
}
