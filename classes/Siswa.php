<?php
declare(strict_types=1);

class Siswa extends BaseModel
{
    private string $nisn;
    private string $nama;
    private string $alamat;
    private ?int $idKelas;

    public function __construct(string $nisn = '', string $nama = '', string $alamat = '', ?int $idKelas = null)
    {
        parent::__construct();
        $this->nisn    = trim($nisn);
        $this->nama    = trim($nama);
        $this->alamat  = trim($alamat);
        $this->idKelas = $idKelas;
    }

    // ---- getter / setter ----
    public function getNisn(): string { return $this->nisn; }
    public function setNisn(string $v): void { $this->nisn = trim($v); }
    public function getNama(): string { return $this->nama; }
    public function setNama(string $v): void { $this->nama = trim($v); }
    public function getAlamat(): string { return $this->alamat; }
    public function setAlamat(string $v): void { $this->alamat = trim($v); }
    public function getIdKelas(): ?int { return $this->idKelas; }
    public function setIdKelas(?int $v): void { $this->idKelas = $v; }

    // ---- query ----
    /** Daftar siswa + nama kelas (INNER JOIN), opsional dicari lewat nama/NISN. */
    public function getAll(string $keyword = ''): array
    {
        $sql = "SELECT s.nisn, s.nama, s.alamat, s.id_kelas, k.nama_kelas
                FROM siswa s
                INNER JOIN kelas k ON k.id_kelas = s.id_kelas";
        $params = [];
        if (trim($keyword) !== '') {
            $sql .= " WHERE s.nama LIKE :q1 OR s.nisn LIKE :q2";
            $like = $this->like(trim($keyword));
            $params = [':q1' => $like, ':q2' => $like];
        }
        $sql .= " ORDER BY s.nama";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(string $nisn): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM siswa WHERE nisn = :n");
        $stmt->execute([':n' => $nisn]);
        return $stmt->fetch() ?: null;
    }

    /** @return array<string,string> pesan error per kolom (kosong = valid) */
    public function validate(bool $isNew): array
    {
        $errors = [];

        if ($this->nisn === '') {
            $errors['nisn'] = 'NISN wajib diisi.';
        } elseif (!preg_match('/^[0-9]{10}$/', $this->nisn)) {
            $errors['nisn'] = 'NISN harus terdiri dari 10 digit angka (saat ini ' . mb_strlen($this->nisn) . ' karakter).';
        } elseif ($isNew && $this->find($this->nisn) !== null) {
            $errors['nisn'] = 'NISN sudah terdaftar. Gunakan NISN lain.';
        }

        if ($this->nama === '') {
            $errors['nama'] = 'Nama wajib diisi.';
        } elseif (mb_strlen($this->nama) > 100) {
            $errors['nama'] = 'Nama maksimal 100 karakter.';
        }

        // Alamat boleh kosong, tapi tetap dibatasi panjangnya
        if (mb_strlen($this->alamat) > 255) {
            $errors['alamat'] = 'Alamat maksimal 255 karakter.';
        }

        if ($this->idKelas === null) {
            $errors['id_kelas'] = 'Kelas wajib dipilih.';
        } elseif ((new Kelas())->find($this->idKelas) === null) {
            $errors['id_kelas'] = 'Kelas yang dipilih tidak ada dalam daftar.';
        }

        return $errors;
    }

    public function create(): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO siswa (nisn, nama, alamat, id_kelas) VALUES (:nisn, :nama, :alamat, :kelas)"
        );
        return $stmt->execute([
            ':nisn' => $this->nisn, ':nama' => $this->nama,
            ':alamat' => $this->alamat, ':kelas' => $this->idKelas,
        ]);
    }

    /** NISN adalah primary key sehingga tidak diubah. */
    public function update(): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE siswa SET nama = :nama, alamat = :alamat, id_kelas = :kelas WHERE nisn = :nisn"
        );
        return $stmt->execute([
            ':nama' => $this->nama, ':alamat' => $this->alamat,
            ':kelas' => $this->idKelas, ':nisn' => $this->nisn,
        ]);
    }

    public function delete(): void
    {
        $data = $this->find($this->nisn);
        if ($data === null) {
            throw new RuntimeException('Siswa tidak ditemukan.');
        }
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM presensi WHERE nisn = :n");
        $stmt->execute([':n' => $this->nisn]);
        $log = (int) $stmt->fetchColumn();
        if ($log > 0) {
            throw new RuntimeException(
                "Siswa {$data['nama']} tidak dapat dihapus karena masih memiliki {$log} log presensi. "
                . 'Hapus log presensinya di menu Rekap Presensi terlebih dahulu.'
            );
        }
        $stmt = $this->db->prepare("DELETE FROM siswa WHERE nisn = :n");
        $stmt->execute([':n' => $this->nisn]);
    }
}
