-- ============ DDL: SKEMA DATABASE ============
CREATE DATABASE IF NOT EXISTS db_klinik CHARACTER SET utf8mb4;
USE db_klinik;

CREATE TABLE tb_user (
  id_user INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  pass VARCHAR(255) NOT NULL,
  role ENUM('admin','supplier') NOT NULL DEFAULT 'supplier',
  nama VARCHAR(100) NOT NULL
);

CREATE TABLE tb_obat (
  id_obat INT AUTO_INCREMENT PRIMARY KEY,
  nama_obat VARCHAR(100) NOT NULL,
  satuan VARCHAR(30) NOT NULL,
  stok INT NOT NULL DEFAULT 0
);

CREATE TABLE tb_pengadaan (
  id_pengadaan INT AUTO_INCREMENT PRIMARY KEY,
  id_obat INT NOT NULL,
  id_user INT NOT NULL,
  jumlah_beli INT NOT NULL,
  harga_beli INT NOT NULL,
  tgl_masuk DATE NOT NULL,
  FOREIGN KEY (id_obat) REFERENCES tb_obat(id_obat) ON DELETE RESTRICT,
  FOREIGN KEY (id_user) REFERENCES tb_user(id_user) ON DELETE RESTRICT
);

-- ============ DML: INSERT DATA DUMMY ============
-- Password semua akun: "password"
INSERT INTO tb_user (username, pass, role, nama) VALUES
('admin',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin',    'Administrator Klinik'),
('kimiafarma','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supplier', 'PT Kimia Farma'),
('kalbe',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supplier', 'PT Kalbe Farma');

INSERT INTO tb_obat (nama_obat, satuan, stok) VALUES
('Paracetamol 500mg', 'Tablet', 150),
('Amoxicillin 500mg', 'Kapsul', 100),
('Vitamin C 100mg',   'Botol',   60),
('Betadine 15ml',     'Botol',   20);

INSERT INTO tb_pengadaan (id_obat, id_user, jumlah_beli, harga_beli, tgl_masuk) VALUES
(1, 2, 100, 500,   '2026-11-01'),
(1, 2,  50, 520,   '2026-11-10'),
(2, 3, 100, 1500,  '2026-11-05'),
(3, 2,  60, 12000, '2026-11-12'),
(4, 3,  20, 9000,  '2026-11-15');

-- ============ SELECT DENGAN JOIN ============
-- Data pengadaan lengkap (obat + supplier + total biaya)
SELECT p.id_pengadaan, o.nama_obat, o.satuan, u.nama AS supplier,
       p.jumlah_beli, p.harga_beli, (p.jumlah_beli * p.harga_beli) AS total_biaya, p.tgl_masuk
FROM tb_pengadaan p
JOIN tb_obat o ON p.id_obat = o.id_obat
JOIN tb_user u ON p.id_user = u.id_user
ORDER BY p.tgl_masuk DESC;

-- Total pengadaan per supplier
SELECT u.nama AS supplier, COUNT(*) AS jml_transaksi, SUM(p.jumlah_beli * p.harga_beli) AS total_biaya
FROM tb_pengadaan p JOIN tb_user u ON p.id_user = u.id_user
GROUP BY u.id_user, u.nama;

-- Obat yang belum pernah diadakan (LEFT JOIN)
SELECT o.nama_obat FROM tb_obat o
LEFT JOIN tb_pengadaan p ON o.id_obat = p.id_obat WHERE p.id_pengadaan IS NULL;
