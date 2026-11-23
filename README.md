# Aplikasi Presensi Siswa

Aplikasi web sederhana untuk mencatat dan merekap presensi siswa **SMK TI Airlangga Samarinda**
yang datang menempelkan kartu RFID (NISN) di gerbang sekolah. Siswa yang tidak tap dianggap tidak hadir,
sehingga tidak ada kolom status kehadiran.

Dibangun dengan **PHP native (PDO + OOP, tanpa framework)** dan **MariaDB**.

## Fitur (pengguna: Administrator)
1. Login & Logout (session)
2. Kelola Kelas (CRUD, data awal dari seeder)
3. Kelola Siswa (CRUD + validasi)
4. Simulasi Tap Kartu (tambah presensi)
5. Rekap Presensi (INNER JOIN 3 tabel, lihat & hapus log)
6. Pencarian siswa (nama / NISN)
7. Pencarian & filter presensi (nama/NISN, tanggal, kelas)

## Struktur Proyek
```
presensi/
├── assets/style.css
├── classes/        Database, BaseModel (abstract), Kelas, Siswa, Presensi, User
├── config/config.php
├── database/schema.sql      <- DDL + seeder
├── includes/       bootstrap, functions, header, navbar, footer
├── index.php  login.php  logout.php
├── kelas.php  siswa.php  tap.php  rekap.php
└── README.md
```

## Cara Instalasi
Kebutuhan: PHP 7.4+ (ekstensi `pdo_mysql`), MariaDB/MySQL.

1. Clone repository:
   ```bash
   git clone <URL-REPOSITORY-ANDA>
   cd presensi
   ```
2. Impor database (membuat database `presensi_siswa`, tabel, dan seeder):
   ```bash
   mysql -u root -p < database/schema.sql
   ```
   (atau impor `database/schema.sql` lewat phpMyAdmin)
3. Sesuaikan kredensial database di `config/config.php`.
4. Jalankan aplikasi:
   ```bash
   php -S 0.0.0.0:8000
   ```
   lalu buka `http://localhost:8000/login.php`. (Atau letakkan folder di `htdocs` XAMPP / `www` Laragon.)
   Agar bisa dibuka dari smartphone, gunakan `0.0.0.0` dan akses lewat IP laptop, mis. `http://192.168.x.x:8000`.

## Akun Demo
| Username | Password   |
|----------|------------|
| admin    | admin123   |

Password disimpan dengan `password_hash` dan diperiksa dengan `password_verify`.

## ERD (Notasi Chen)
Satu **kelas** memiliki banyak **siswa** (1 : N, *anggota*), satu **siswa** melakukan banyak **presensi**
(1 : N, *melakukan*). Tabel `users` berdiri sendiri.

> Tempelkan gambar ERD Chen di sini: `![ERD](docs/erd.png)`

Struktur tabel:

| Tabel | Kolom |
|---|---|
| kelas | `id_kelas` (PK, INT AI), `nama_kelas` (VARCHAR 20, NOT NULL, UNIQUE), `jurusan` (VARCHAR 50, NOT NULL) |
| siswa | `nisn` (PK, CHAR 10), `nama` (VARCHAR 100), `alamat` (VARCHAR 255), `id_kelas` (FK, INT, NOT NULL) |
| presensi | `id` (PK, INT AI), `nisn` (FK, CHAR 10), `tanggal_waktu` (DATETIME, DEFAULT CURRENT_TIMESTAMP) |
| users | `id_user` (PK, INT AI), `username` (VARCHAR 50, UNIQUE), `password` (VARCHAR 255) |

## Ketentuan Teknis yang Dipenuhi
- **Query**: seluruhnya memakai *prepared statement*.
- **Koneksi**: PDO mode `ERRMODE_EXCEPTION` dalam class `Database`.
- **OOP**: abstract class `BaseModel` (method abstrak `getAll()`), class `Kelas`, `Siswa`, `Presensi`
  mewarisi `BaseModel`; properti `private`, memiliki constructor dan getter/setter. Class `User` untuk login.
- **Session**: `id` dan `username` disimpan di session; semua halaman selain login dilindungi (`requireLogin()`).
- **Layouting**: `header.php`, `navbar.php`, `footer.php` dipakai di semua halaman.
- **Validasi**: di sisi server, pesan error per kolom, isian tidak hilang saat gagal.
- **Keamanan**: `password_hash`/`password_verify`, semua output memakai `htmlspecialchars` (`e()`).

## Hasil Pengujian
Isi kolom *Hasil Aktual*/*Status* setelah menjalankan tiap skenario, dan lampirkan screenshot dari smartphone
(folder `docs/screenshot/`).

| No | Skenario Uji | Hasil yang Diharapkan | Hasil Aktual | Status | Bukti |
|----|--------------|-----------------------|--------------|--------|-------|
| 1 | Login dengan password salah | Gagal, muncul pesan kesalahan | | | |
| 2 | Buka halaman tanpa login lewat URL | Diarahkan ke halaman login | | | |
| 3 | Tambah siswa dengan NISN 9 digit | Validasi gagal, pesan muncul, isian tidak hilang | | | |
| 4 | Tambah siswa dengan NISN yang sudah ada | Ditolak dengan pesan NISN sudah terdaftar | | | |
| 5 | Simulasi tap dengan NISN yang tidak terdaftar | Ditolak dengan pesan yang jelas | | | |
| 6 | Simulasi tap dengan NISN valid | Muncul di rekap hari ini, dengan waktu terisi otomatis | | | |
| 7 | Hapus kelas yang masih memiliki siswa | Ditolak dengan pesan yang mudah dipahami | | | |
| 8 | Cari siswa berdasarkan sebagian nama | Hanya siswa yang sesuai yang tampil | | | |

## Tautan Repository
<URL-REPOSITORY-ANDA>
