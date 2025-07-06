-- 1. Buat Database
CREATE DATABASE IF NOT EXISTS `pengumpulantugas`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
USE `pengumpulantugas`;

-- 2. Tabel Users
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('mahasiswa','asisten') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel Mata Praktikum (Tema Cyber Security)
CREATE TABLE `mata_praktikum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_praktikum` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabel Modul
CREATE TABLE `modul` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_praktikum` int(11) NOT NULL,
  `nama_modul` varchar(255) NOT NULL,
  `nama_file_materi` varchar(255) DEFAULT NULL,
  `path_file_materi` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_praktikum`) REFERENCES `mata_praktikum`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabel Pendaftaran Praktikum
CREATE TABLE `pendaftaran_praktikum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mahasiswa` int(11) NOT NULL,
  `id_praktikum` int(11) NOT NULL,
  `tanggal_daftar` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_mahasiswa`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_praktikum`) REFERENCES `mata_praktikum`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_pendaftaran` (`id_mahasiswa`, `id_praktikum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Tabel Laporan Praktikum
CREATE TABLE `laporan_praktikum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_modul` int(11) NOT NULL,
  `id_mahasiswa` int(11) NOT NULL,
  `nama_file_laporan` varchar(255) DEFAULT NULL,
  `path_file_laporan` varchar(255) DEFAULT NULL,
  `nilai` int(3) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `tanggal_kumpul` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal_dinilai` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_modul`) REFERENCES `modul`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_mahasiswa`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Sample Data (Optional, untuk testing)
-- Users (semua password hash "password123")
INSERT INTO `users` (`nama`, `email`, `password`, `role`) VALUES
('Mahasiswa Satu', 'mahasiswa1@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Mahasiswa Dua', 'mahasiswa2@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Asisten Satu',   'asisten1@test.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'asisten');

-- Mata Praktikum Cyber Security (2 entries)
INSERT INTO `mata_praktikum` (`nama_praktikum`, `deskripsi`) VALUES
('Cyber Security Fundamentals', 'Dasar‐dasar keamanan siber: konsep ancaman, enkripsi, dan kebijakan keamanan.'),
('Digital Forensics',           'Teknik analisis forensik digital: investigasi bukti, recovery data, dan laporan.');

-- Modul untuk Cyber Security Fundamentals (3 modul)
INSERT INTO `modul` (`id_praktikum`, `nama_modul`) VALUES
((SELECT id FROM mata_praktikum WHERE nama_praktikum = 'Cyber Security Fundamentals'), 'Modul 1: Pengantar Keamanan Siber'),
((SELECT id FROM mata_praktikum WHERE nama_praktikum = 'Cyber Security Fundamentals'), 'Modul 2: Kriptografi Dasar'),
((SELECT id FROM mata_praktikum WHERE nama_praktikum = 'Cyber Security Fundamentals'), 'Modul 3: Kebijakan & Manajemen Keamanan');

-- Modul untuk Digital Forensics (2 modul)
INSERT INTO `modul` (`id_praktikum`, `nama_modul`) VALUES
((SELECT id FROM mata_praktikum WHERE nama_praktikum = 'Digital Forensics'), 'Modul 1: Pengumpulan & Analisis Bukti'),
((SELECT id FROM mata_praktikum WHERE nama_praktikum = 'Digital Forensics'), 'Modul 2: Forensik Jaringan');

-- Contoh Pendaftaran Praktikum
INSERT INTO `pendaftaran_praktikum` (`id_mahasiswa`, `id_praktikum`) VALUES
((SELECT id FROM users WHERE email = 'mahasiswa1@test.com'),
 (SELECT id FROM mata_praktikum WHERE nama_praktikum = 'Cyber Security Fundamentals')),
((SELECT id FROM users WHERE email = 'mahasiswa1@test.com'),
 (SELECT id FROM mata_praktikum WHERE nama_praktikum = 'Digital Forensics')),
((SELECT id FROM users WHERE email = 'mahasiswa2@test.com'),
 (SELECT id FROM mata_praktikum WHERE nama_praktikum = 'Cyber Security Fundamentals'));

-- Contoh Laporan Praktikum (Mahasiswa Satu, Modul 1 CS Fundamentals)
INSERT INTO `laporan_praktikum` (
  `id_modul`, `id_mahasiswa`, `nama_file_laporan`, `path_file_laporan`,
  `nilai`, `feedback`, `tanggal_dinilai`
) VALUES (
  (SELECT m.id FROM modul m
     JOIN mata_praktikum mp ON m.id_praktikum = mp.id
    WHERE mp.nama_praktikum = 'Cyber Security Fundamentals'
      AND m.nama_modul = 'Modul 1: Pengantar Keamanan Siber'),
  (SELECT id FROM users WHERE email = 'mahasiswa1@test.com'),
  'laporan_mhs1_modul1.pdf',
  'uploads/laporan/laporan_mhs1_modul1.pdf',
  90,
  'Bagus, lakukan pendalaman pada teknik enkripsi.',
  NOW()
);


CREATE USER IF NOT EXISTS 'leo'@'127.0.0.1'
  IDENTIFIED BY 'password123';


GRANT SELECT, INSERT, UPDATE, DELETE
  ON `pengumpulantugas`.* TO 'leo'@'127.0.0.1';

FLUSH PRIVILEGES;
