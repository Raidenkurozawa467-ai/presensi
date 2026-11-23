-- ============================================================
-- schema.sql : DDL + seeder  |  Aplikasi Presensi Siswa
-- Jalankan: mysql -u root -p < database/schema.sql
-- ============================================================
CREATE DATABASE IF NOT EXISTS presensi_siswa
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE presensi_siswa;

-- Urutan: kelas -> siswa -> presensi (users berdiri sendiri)
DROP TABLE IF EXISTS presensi;
DROP TABLE IF EXISTS siswa;
DROP TABLE IF EXISTS kelas;
DROP TABLE IF EXISTS users;

CREATE TABLE kelas (
  id_kelas   INT AUTO_INCREMENT PRIMARY KEY,
  nama_kelas VARCHAR(20) NOT NULL UNIQUE,
  jurusan    VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE siswa (
  nisn     CHAR(10) PRIMARY KEY,
  nama     VARCHAR(100) NOT NULL,
  alamat   VARCHAR(255) NOT NULL,
  id_kelas INT NOT NULL,
  CONSTRAINT fk_siswa_kelas FOREIGN KEY (id_kelas) REFERENCES kelas(id_kelas)
) ENGINE=InnoDB;

CREATE TABLE presensi (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  nisn          CHAR(10) NOT NULL,
  tanggal_waktu DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_presensi_siswa FOREIGN KEY (nisn) REFERENCES siswa(nisn)
) ENGINE=InnoDB;

CREATE TABLE users (
  id_user  INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50)  NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- ---------------- SEEDER ----------------
INSERT INTO kelas (nama_kelas, jurusan) VALUES
  ('X PPLG 1',  'Pengembangan Perangkat Lunak dan Gim'),
  ('X PPLG 2',  'Pengembangan Perangkat Lunak dan Gim'),
  ('XI PPLG 1', 'Pengembangan Perangkat Lunak dan Gim'),
  ('XII PPLG 1','Pengembangan Perangkat Lunak dan Gim'),
  ('X TJKT 1',  'Teknik Jaringan Komputer dan Telekomunikasi'),
  ('XI TJKT 1', 'Teknik Jaringan Komputer dan Telekomunikasi');

-- Akun demo -> username: admin | password: admin123 (disimpan sebagai password_hash)
INSERT INTO users (username, password) VALUES ('admin', '$2y$12$Jhpo64pFFOl4JGfqzHs6V.1XN6fJAyvzICLL/zdu2ZvV8F9vgeghy');

-- Contoh siswa (NISN diawali angka 0 sengaja untuk menguji tipe CHAR)
INSERT INTO siswa (nisn, nama, alamat, id_kelas) VALUES
  ('0051234567', 'Ahmad Fauzi',    'Jl. Juanda No. 12, Samarinda',     1),
  ('0059876543', 'Siti Nurhaliza', 'Jl. Pangeran Antasari No. 5',      1),
  ('0061112223', 'Budi Santoso',   'Jl. P. Suryanata Blok B No. 8',    3),
  ('0074445556', 'Dewi Lestari',   '',                                 5);
