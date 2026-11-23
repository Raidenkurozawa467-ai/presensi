<?php
declare(strict_types=1);

class Presensi extends BaseModel
{
    private ?int $id;
    private string $nisn;
    private ?string $tanggalWaktu;

    public function __construct(?int $id = null, string $nisn = '', ?string $tanggalWaktu = null)
    {
        parent::__construct();
        $this->id           = $id;
        $this->nisn         = trim($nisn);
        $this->tanggalWaktu = $tanggalWaktu;
    }

    // ---- getter / setter ----
    public function getId(): ?int { return $this->id; }
    public function setId(?int $v): void { $this->id = $v; }
    public function getNisn(): string { return $this->nisn; }
    public function setNisn(string $v): void { $this->nisn = trim($v); }
    public function getTanggalWaktu(): ?string { return $this->tanggalWaktu; }

    private const SELECT_JOIN = "SELECT p.id, p.nisn, p.tanggal_waktu, s.nama, k.nama_kelas
        FROM presensi p
        INNER JOIN siswa s ON s.nisn = p.nisn
        INNER JOIN kelas k ON k.id_kelas = s.id_kelas";

    /**
     * Rekap presensi: INNER JOIN presensi - siswa - kelas.
     * @param array{tanggal?:string,id_kelas?:int|string,q?:string} $filter
     */
    public function getAll(array $filter = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filter['tanggal'])) {
            $where[] = "DATE(p.tanggal_waktu) = :tgl";
            $params[':tgl'] = $filter['tanggal'];
        }
        if (!empty($filter['id_kelas'])) {
            $where[] = "k.id_kelas = :kelas";
            $params[':kelas'] = (int) $filter['id_kelas'];
        }
        if (!empty($filter['q'])) {
            $where[] = "(s.nama LIKE :q1 OR s.nisn LIKE :q2)";
            $like = $this->like(trim($filter['q']));
            $params[':q1'] = $like;
            $params[':q2'] = $like;
        }

        $sql = self::SELECT_JOIN;
        if ($where) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY p.tanggal_waktu DESC, p.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];
        if ($this->nisn === '') {
            $errors['nisn'] = 'NISN wajib diisi.';
        } elseif (!preg_match('/^[0-9]{10}$/', $this->nisn)) {
            $errors['nisn'] = 'NISN harus terdiri dari 10 digit angka (saat ini ' . mb_strlen($this->nisn) . ' karakter).';
        } elseif ((new Siswa())->find($this->nisn) === null) {
            $errors['nisn'] = 'NISN tidak terdaftar. Tap kartu ditolak.';
        }
        return $errors;
    }

    /**
     * Simulasi tap kartu: hanya NISN yang dikirim, id & tanggal_waktu diisi database.
     * @return array baris presensi (dengan nama siswa & kelas) yang baru dibuat
     */
    public function tap(): array
    {
        $stmt = $this->db->prepare("INSERT INTO presensi (nisn) VALUES (:n)");
        $stmt->execute([':n' => $this->nisn]);
        $id = (int) $this->db->lastInsertId();

        $stmt = $this->db->prepare(self::SELECT_JOIN . " WHERE p.id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /** Log presensi hanya dapat dihapus, tidak dapat diubah. */
    public function delete(): bool
    {
        $stmt = $this->db->prepare("DELETE FROM presensi WHERE id = :id");
        $stmt->execute([':id' => $this->id]);
        return $stmt->rowCount() > 0;
    }

    /** Jumlah siswa berbeda yang hadir hari ini. */
    public function countHadirHariIni(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(DISTINCT nisn) FROM presensi WHERE DATE(tanggal_waktu) = CURDATE()"
        );
        return (int) $stmt->fetchColumn();
    }
}
