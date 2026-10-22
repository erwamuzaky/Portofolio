-- Skema database portofolio (MariaDB/MySQL)
-- Impor: & C:\xampp\mysql\bin\mysql.exe -u root --default-character-set=utf8mb4 < setup.sql
-- Aman dijalankan ulang: tabel lama dikosongkan dulu sebelum diisi.

CREATE DATABASE IF NOT EXISTS portofolio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portofolio;

DROP TABLE IF EXISTS konten, sosial, project, skill, pendidikan, pengalaman, sertifikat, user;

CREATE TABLE IF NOT EXISTS konten (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section VARCHAR(50) NOT NULL,
    kunci VARCHAR(50) NOT NULL,
    nilai TEXT NOT NULL,
    UNIQUE KEY uj_konten (section, kunci)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sosial (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL,
    url VARCHAR(255) NOT NULL,
    urutan INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS project (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(150) NOT NULL,
    peran VARCHAR(100) NOT NULL DEFAULT '',
    tahun VARCHAR(20) NOT NULL DEFAULT '',
    deskripsi TEXT NOT NULL,
    hasil VARCHAR(255) NOT NULL DEFAULT '',
    detail TEXT NOT NULL DEFAULT '',
    gambar VARCHAR(255) NOT NULL DEFAULT '',
    tag VARCHAR(100) NOT NULL DEFAULT '',
    link VARCHAR(255) NOT NULL DEFAULT '',
    urutan INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pengalaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    peran VARCHAR(100) NOT NULL DEFAULT '',
    tahun VARCHAR(50) NOT NULL DEFAULT '',
    deskripsi TEXT NOT NULL DEFAULT '',
    urutan INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sertifikat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    penerbit VARCHAR(150) NOT NULL DEFAULT '',
    tahun VARCHAR(50) NOT NULL DEFAULT '',
    gambar VARCHAR(255) NOT NULL DEFAULT '',
    link VARCHAR(255) NOT NULL DEFAULT '',
    urutan INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS skill (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL,
    ikon VARCHAR(255) NOT NULL DEFAULT '',
    kategori ENUM('hard', 'soft') NOT NULL DEFAULT 'hard',
    deskripsi TEXT NOT NULL DEFAULT '',
    urutan INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pendidikan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    jurusan VARCHAR(150) NOT NULL DEFAULT '',
    tahun VARCHAR(50) NOT NULL DEFAULT '',
    urutan INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(64) NOT NULL
) ENGINE=InnoDB;

-- ========== SEED: KONTEN ==========
INSERT INTO konten (section, kunci, nilai) VALUES
('hero', 'sapaan', 'Halo, Saya'),
('hero', 'nama', 'Erwa Muzaky'),
('hero', 'deskripsi', 'Siswa RPL di SMKN 3 Pariaman. Belajar bikin website dan aplikasi, dari tampilan sampai database. Di luar kelas, saya aktif di organisasi sekolah.'),
('hero', 'foto', 'foto/foto.jpeg'),
('about', 'paragraf_pembuka', 'Sebagian besar waktu saya habis untuk ngoding dan bikin aplikasi web. Yang paling sering saya kerjakan:'),
('about', 'keahlian', 'HTML & CSS: Merancang tampilan web yang rapi dan tetap enak dilihat di HP maupun laptop.|PHP & Laravel: Membuat fitur aplikasi dan API dengan Laravel.|MySQL: Merancang tabel dan mengelola data yang dipakai aplikasi.'),
('about', 'paragraf_organisasi', 'Di sekolah saya juga aktif di organisasi, jadi nggak cuma duduk di depan laptop:'),
('about', 'paragraf_penutup', 'Dari situ saya belajar disiplin, bagi tugas sama tim, dan berani ngomong di depan orang. Kalau ada masukan, saya pakai buat memperbaiki hasil kerja.'),
('about', 'lokasi', 'Korong Tanah Taban, Limau Puruik, Kec. V Koto Timur, Kab. Padang Pariaman, Sumatera Barat'),
('about', 'maps_src', 'https://maps.google.com/maps?q=C4MP%2B26J,%20Limau%20Puruik,%20Padang%20Pariaman&t=&z=14&ie=UTF8&iwloc=&output=embed'),
('about', 'kutipan_kartu', 'Percayalah pada proses, karena hal besar membutuhkan waktu.'),
('about', 'peta_wilayah', 'Padang Pariaman'),
('about', 'peta_provinsi', 'Sumatera Barat'),
('about', 'peta_lokasi', 'Limau Puruik · Padang Pariaman'),
('about', 'peta_status', 'Sinyal aktif'),
('about', 'peta_marker', 'REDAKSI'),
('about', 'peta_perairan', 'SAMUDRA HINDIA'),
('about', 'jurusan', 'Rekayasa Perangkat Lunak (RPL)'),
('about', 'fokus', 'Web & Mobile Development'),
('contact', 'email', 'erwamuzaky447@gmail.com'),
('contact', 'pesan', 'Ada yang mau ditanyakan atau diajak diskusi? Kirim email atau hubungi lewat link di bawah.'),
('footer', 'teks', 'Erwa Muzaky · Siswa SMKN 3 Pariaman.')
ON DUPLICATE KEY UPDATE nilai = VALUES(nilai);

-- ========== SEED: SOSIAL ==========
INSERT INTO sosial (nama, url, urutan) VALUES
('TikTok', 'https://tiktok.com', 1),
('Instagram', 'https://instagram.com', 2),
('WhatsApp', 'https://wa.me/qr/X5ZOHWKI4YGNM1', 3);

-- ========== SEED: PROJECT ==========
INSERT INTO project (judul, peran, tahun, deskripsi, hasil, detail, gambar, tag, link, urutan) VALUES
('Website Perpustakaan Digital', 'Web Developer', '', 'Website pengelolaan buku perpustakaan dengan Laravel dan MySQL. Ada login, kategori, penerbit, dan fitur tambah-ubah-hapus. Tampilannya sederhana supaya petugas mudah memakainya.', 'Data buku, kategori, dan penerbit tersimpan di satu database, jadi petugas tidak perlu mencatat manual.', 'Website Perpustakaan Digital saya buat untuk menggantikan pencatatan buku yang masih manual.

Fitur utamanya: login, kelola data buku, kategori, dan penerbit, plus tambah, ubah, dan hapus data.

Dibangun dengan Laravel, MySQL, HTML, dan CSS.', 'foto/proyek1.png', 'Laravel, PHP, MySQL', '', 1),
('Website Inventaris Bengkel Komputer', 'Web Developer', '', 'Website untuk mendata inventaris barang di bengkel komputer. Dibuat dengan Laravel dan MySQL, ada login admin, dashboard, unggah foto barang, dan laporan. Data barang jadi lebih rapi dan gampang dicek.', 'Barang masuk dan keluar tercatat di satu tempat, dan laporannya bisa langsung dibuat dari aplikasi.', 'Website Inventaris Bengkel Komputer dipakai untuk mencatat peralatan dan barang di bengkel.

Fitur utamanya: login admin, dashboard, kelola data inventaris, unggah foto barang, dan cetak laporan.

Dibangun dengan Laravel, MySQL, HTML, dan CSS.', 'foto/proyek2.png', 'Laravel, PHP, MySQL', '', 2),
('Aplikasi Manajemen Produk', 'Mobile Developer', '', 'Aplikasi Flutter untuk mengelola data produk. Datanya diambil dari API PHP dan disimpan di MySQL, jadi produk bisa dilihat, ditambah, diubah, dan dihapus langsung dari HP.', 'Data produk bisa dikelola dari HP tanpa perlu membuka komputer.', 'Aplikasi mobile untuk mengelola data produk dari HP.

Fitur utamanya: lihat daftar produk, tambah, ubah, lihat detail, dan hapus data.

Dibangun dengan Flutter, API PHP, dan MySQL.', 'foto/proyek3.jpeg', 'Flutter, PHP, MySQL', '', 3);

-- ========== SEED: SKILL ==========
INSERT INTO skill (nama, ikon, kategori, deskripsi, urutan) VALUES
('HTML', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/html5/html5-original.svg', 'hard', '', 1),
('CSS', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/css3/css3-original.svg', 'hard', '', 2),
('C++', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/cplusplus/cplusplus-original.svg', 'hard', '', 3),
('Java', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/java/java-original.svg', 'hard', '', 4),
('PHP', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg', 'hard', '', 5),
('Laravel', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/laravel/laravel-original.svg', 'hard', '', 6),
('MySQL', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg', 'hard', '', 7),
('Flutter', 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/flutter/flutter-original.svg', 'hard', '', 8),
('Public Speaking & Komunikasi', '', 'soft', 'Berani bicara dan presentasi di depan orang, termasuk saat harus menyampaikan pendapat.', 1),
('Kerja Sama Tim', '', 'soft', 'Terbiasa bagi tugas dan berdiskusi dengan tim sampai project selesai.', 2),
('Kepemimpinan & Organisasi', '', 'soft', 'Belajar mengatur tim dan kegiatan dari pengalaman di OSIS, Paskibraka, PKS, dan Pramuka.', 3),
('Disiplin', '', 'soft', 'Tepat waktu dan menyelesaikan tugas sesuai jadwal.', 4),
('Cepat Belajar', '', 'soft', 'Cepat menyesuaikan diri saat harus memakai teknologi atau framework baru.', 5),
('Terbuka pada Masukan', '', 'soft', 'Terbuka pada kritik dan memakainya untuk memperbaiki hasil kerja.', 6);

-- ========== SEED: PENDIDIKAN ==========
INSERT INTO pendidikan (nama, jurusan, tahun, urutan) VALUES
('SMKN 3 Pariaman', 'Rekayasa Perangkat Lunak (RPL)', '2024 – 2027 (Sekarang)', 1),
('MTsN 1 Kota Pariaman', 'Pendidikan Menengah Pertama', '2021 – 2024', 2),
('SDN 02 Sikapak Barat', 'Pendidikan Dasar', '2015 – 2021', 3);

-- ========== SEED: PENGALAMAN ==========
INSERT INTO pengalaman (nama, peran, tahun, deskripsi, urutan) VALUES
('OSIS SMKN 3 Pariaman', 'Wakil Ketua', '', 'Membantu ketua OSIS mengoordinasi program kerja dan kegiatan sekolah.', 1),
('Paskibraka Kota Pariaman', 'Anggota', '', 'Mengikuti pelatihan dan bertugas pada upacara bendera.', 2),
('Pramuka Gudep SMKN 3 Pariaman', 'Anggota', '', 'Aktif dalam kegiatan kepramukaan dan kerja sama tim.', 3),
('PKS', 'Anggota', '', 'Membantu menjaga ketertiban lingkungan sekolah.', 4);

-- ========== SEED: ADMIN ==========
-- Tidak ada password default di repo. Setup akan GAGAL bila admin belum diisi.
-- Dua cara mengisi akun admin:
--   1. PALING AMAN (bcrypt): hitung hash dulu lewat PHP, lalu set sebagai @ADMIN_HASH.
--        php buat_hash_admin.php "password_pilihanmu"
--        mysql -uroot --default-character-set=utf8mb4 -e "SET @ADMIN_HASH='<hash_dari_perintah_di_atas>'; SOURCE setup.sql;"
--   2. CEPAT (hash lama): set @ADMIN_PASS, nanti otomatis dinaikkan ke bcrypt saat login pertama.
--        mysql -uroot --default-character-set=utf8mb4 -e "SET @ADMIN_PASS='password_pilihanmu'; SOURCE setup.sql;"
-- Ganti password lewat menu Ganti Password di dashboard setelah login.
DROP PROCEDURE IF EXISTS cek_password_admin;
DELIMITER //
CREATE PROCEDURE cek_password_admin()
BEGIN
    IF (@ADMIN_HASH IS NULL OR @ADMIN_HASH = '') AND (@ADMIN_PASS IS NULL OR @ADMIN_PASS = '') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Admin belum diisi. Set @ADMIN_HASH (bcrypt) atau @ADMIN_PASS sebelum SOURCE setup.sql';
    END IF;
    INSERT INTO user (username, password) VALUES
    ('admin', IF(@ADMIN_HASH IS NULL OR @ADMIN_HASH = '', SHA2(@ADMIN_PASS, 256), @ADMIN_HASH))
    ON DUPLICATE KEY UPDATE username = username;
END //
DELIMITER ;
CALL cek_password_admin();
DROP PROCEDURE cek_password_admin;
