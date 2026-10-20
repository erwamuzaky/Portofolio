-- Skema database portofolio (MariaDB/MySQL)
-- Impor: & C:\xampp\mysql\bin\mysql.exe -u root --default-character-set=utf8mb4 < setup.sql
-- Aman dijalankan ulang: tabel lama dikosongkan dulu sebelum diisi.

CREATE DATABASE IF NOT EXISTS portofolio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portofolio;

DROP TABLE IF EXISTS konten, sosial, project, skill, pendidikan, user;

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
    deskripsi TEXT NOT NULL,
    gambar VARCHAR(255) NOT NULL DEFAULT '',
    tag VARCHAR(100) NOT NULL DEFAULT '',
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
('hero', 'deskripsi', 'Siswa Rekayasa Perangkat Lunak di SMKN 3 Pariaman yang fokus pada Web Development. Selain hobi ngulik koding dan mewujudkan ide menjadi web yang rapi, saya juga aktif di organisasi sekolah dan terbiasa bekerja bareng tim.'),
('hero', 'foto', 'foto/foto.jpeg'),
('about', 'paragraf_pembuka', 'Keseharian saya banyak diisi dengan ngoding dan ngebangun aplikasi web. Beberapa hal yang biasa saya kerjakan antara lain:'),
('about', 'keahlian', 'HTML & CSS: Merancang dan membangun tampilan web yang rapi serta responsif di berbagai ukuran layar.|PHP & Laravel: Mengembangkan fitur-fitur aplikasi web berbasis PHP menggunakan framework Laravel.|MySQL: Mengelola database relasional untuk kebutuhan penyimpanan dan pemrosesan data aplikasi.'),
('about', 'paragraf_organisasi', 'Tapi di sekolah, saya nggak cuma diam di depan laptop. Saya juga aktif di organisasi:'),
('about', 'paragraf_penutup', 'Dari pengalaman itu, saya jadi terbiasa disiplin, paham cara kerja bareng tim, dan nggak canggung kalau harus ngomong di depan orang. Saya selalu senang belajar hal baru dan terbuka banget sama masukan biar hasil karya saya makin bagus.'),
('about', 'lokasi', 'Korong Tanah Taban, Limau Puruik, Kec. V Koto Timur, Kab. Padang Pariaman, Sumatera Barat'),
('about', 'maps_src', 'https://maps.google.com/maps?q=C4MP%2B26J,%20Limau%20Puruik,%20Padang%20Pariaman&t=&z=14&ie=UTF8&iwloc=&output=embed'),
('about', 'jurusan', 'Rekayasa Perangkat Lunak (RPL)'),
('about', 'fokus', 'Web & Mobile Development'),
('contact', 'email', 'erwamuzaky447@gmail.com'),
('contact', 'pesan', 'Kalau ada yang mau ditanyakan, ngobrol, diskusi, langsung lewat email atau link di bawah ini ya!'),
('footer', 'teks', 'Erwa Muzaky. Student of SMKN 3 Pariaman.')
ON DUPLICATE KEY UPDATE nilai = VALUES(nilai);

-- ========== SEED: SOSIAL ==========
INSERT INTO sosial (nama, url, urutan) VALUES
('TikTok', 'https://tiktok.com', 1),
('Instagram', 'https://instagram.com', 2),
('WhatsApp', 'https://wa.me/qr/X5ZOHWKI4YGNM1', 3);

-- ========== SEED: PROJECT ==========
INSERT INTO project (judul, deskripsi, gambar, tag, link, urutan) VALUES
('Website Perpustakaan Digital', 'Website Perpustakaan Digital yang saya kembangkan untuk memudahkan pengelolaan data buku secara lebih praktis dan terorganisir. Website ini dibangun menggunakan Laravel, PHP, HTML, CSS, dan MySQL, serta dilengkapi fitur login, pengelolaan data buku, kategori, penerbit, dan CRUD (Create, Read, Update, Delete). Hasil akhirnya adalah aplikasi web yang mempermudah proses administrasi perpustakaan dengan tampilan yang sederhana, responsif, dan mudah digunakan.', 'foto/proyek1.png', 'Laravel', '', 1),
('Website Inventaris Bengkel Komputer', 'Website Inventaris Bengkel Komputer merupakan aplikasi berbasis web yang saya kembangkan untuk membantu pengelolaan inventaris peralatan dan barang di bengkel komputer. Website ini dibangun menggunakan Laravel, PHP, HTML, CSS, dan MySQL, serta dilengkapi fitur login admin, dashboard, pengelolaan data inventaris, upload gambar barang, dan pembuatan laporan. Hasil akhirnya adalah sistem yang memudahkan proses pendataan barang agar lebih rapi, terstruktur, dan efisien.', 'foto/proyek2.png', 'Laravel', '', 2),
('Aplikasi Manajemen Produk', 'Aplikasi mobile yang saya kembangkan menggunakan Flutter untuk memudahkan pengelolaan data produk. Aplikasi ini terhubung dengan API berbasis PHP dan database MySQL, serta menyediakan fitur menampilkan, menambah, mengubah, melihat detail, dan menghapus data produk. Proyek ini membantu saya memahami proses pengembangan aplikasi mobile yang terintegrasi dengan backend.', 'foto/proyek3.jpeg', 'Flutter', '', 3);

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
('Public Speaking & Komunikasi', '', 'soft', 'Percaya diri dan tidak canggung saat harus berbicara, presentasi, atau menyampaikan ide di depan orang banyak.', 1),
('Kerja Sama Tim', '', 'soft', 'Terbiasa berkolaborasi, berdiskusi, dan saling melengkapi dengan anggota tim dalam menyelesaikan project.', 2),
('Kepemimpinan & Organisasi', '', 'soft', 'Paham cara mengarahkan tim dan mengelola kegiatan dari pengalaman di OSIS, Paskibraka, PKS, dan Pramuka.', 3),
('Kedisiplinan Tinggi', '', 'soft', 'Terbiasa tepat waktu, patuh pada aturan, dan bertanggung jawab menyelesaikan tugas tepat jadwal.', 4),
('Cepat Belajar (Fast Learner)', '', 'soft', 'Selalu antusias dan cepat paham saat mempelajari teknologi, framework, atau hal baru yang belum pernah dicoba.', 5),
('Terbuka pada Evaluasi', '', 'soft', 'Menerima kritik dan saran dengan positif sebagai bahan untuk memperbaiki serta meningkatkan kualitas karya.', 6);

-- ========== SEED: PENDIDIKAN ==========
INSERT INTO pendidikan (nama, jurusan, tahun, urutan) VALUES
('SMKN 3 Pariaman', 'Rekayasa Perangkat Lunak (RPL)', '2024 – 2027 (Sekarang)', 1),
('MTsN 1 Kota Pariaman', 'Pendidikan Menengah Pertama', '2021 – 2024', 2),
('SDN 02 Sikapak Barat', 'Pendidikan Dasar', '2015 – 2021', 3);

-- ========== SEED: ADMIN ==========
-- Username: admin  Password: admin123 (ganti lewat dashboard setelah login)
INSERT INTO user (username, password) VALUES
('admin', SHA2('admin123', 256))
ON DUPLICATE KEY UPDATE username = username;
