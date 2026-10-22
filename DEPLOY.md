# Panduan Men.onlinekan Portofolio (Hosting cPanel)

Panduan ini untuk mengunggah portofolio ini ke hosting **cPanel shared hosting**.
Website-nya tetap PHP + MySQL, tidak ada proses build, tidak perlu Node.js, tidak
perlu Composer. Yang diunggah beres, website langsung jalan.

---

## 1. Yang Dibutuhkan

| Kebutuhan | Keterangan |
|---|---|
| Hosting cPanel | Shared hosting sudah cukup. Gratis: InfinityFree. Berbayar: Hostinger, Hostinger Cloud, atau cPanel lain. |
| Domain (opsional) | Kalau belum punya, pakai subdomain gratis dari hosting (misal `namamu.my.id`). |
| PHP 8.2 atau lebih baru | Website ini butuh PHP 8.1+. Jangan pakai PHP 5/7. |
| MySQL 5.7+ atau MariaDB 10.3+ | Hampir semua hosting cPanel sudah menyediakan. |
| FileZilla (opsional) | Untuk upload lewat FTP. Kalau malas, pakai File Manager bawaan cPanel. |

Domain itu luxury. Fokus dulu ke hosting, lalu pakai subdomain gratis, kalau
sudah jadi dan mau serius baru beli domain.

---

## 2. Cek Dulu di Komputer

Sebelum upload, pastikan website-nya benar-benar jalan di komputer dan datanya
lengkap. Dari folder project, jalankan:

```powershell
python tools/cek_halaman.py
python tools/audit_konten.py --db
python tools/kelola_konten.py export
```

Yang diharapkan:

- `cek_halaman.py` → semua baris berbunyi `[LULUS]`
- `audit_konten.py` → `KRITIS: 0`
- `kelola_konten.py export` → membuat file `tools/keluaran/konten.sql`

File `konten.sql` ini yang nanti diimport ke server supaya isi website sama
persis dengan isi di komputer.

---

## 3. Daftar Hosting

1. Buka infinityfree.com atau hostinger.com, pilih paket, lalu daftar.
2. Setelah masuk, cari menu **My Websites** / **Websites**, lalu **Create Website**.
3. Pilih domain gratis dari yang tersedia, atau domain sendiri kalau sudah punya.
4. Catat nama domainnya, misal `portofolio-erwa.my.id`.

---

## 4. Upload File

### Yang perlu diunggah

```
public_html/
├── index.php
├── style.css
├── style-beranda.css
├── beranda.js
├── config.contoh.php    <- diunggah lalu diubah nama jadi config.php (lihat langkah 7)
├── admin/
│   ├── index.php
│   ├── login.php
│   └── logout.php
└── foto/
    ├── foto.webp
    ├── wajah-potong.png, topeng-potong.png, gelantungan-potong.png
    ├── spiderman-logo.svg
    ├── foto-480.webp, foto-768.webp, foto-1200.webp
    ├── proyek1.webp ... (semua gambar project)
    ├── proyek1-480.webp, proyek1-768.webp, proyek1-1200.webp ... (varian WebP)
    └── WAKETOS.jpeg, Paskibraka.jpeg, Pramuka.jpeg, PKS.jpeg (+ varian WebP)
```

`style.css` tetap diperlukan untuk dashboard admin. Halaman depan memakai
`style-beranda.css` dan `beranda.js`. Folder `foto/` diupload **utuh** beserta
varian `-480.webp`, `-768.webp`, dan `-1200.webp`. Halaman project memakai
`srcset`, jadi varian-varian itu yang dipakai browser. Kalau variannya tidak
ada, gambar tetap muncul (fallback ke file asli), hanya belum hemat bandwidth.

### Yang TIDAK perlu diunggah

| File | Alasan |
|---|---|
| `tools/` | Perkakas Python, hanya jalan di komputer. |
| `design-demos/` | Berkas desain dan demo, bukan bagian website. |
| `arsip/` | Berkas lama yang sudah tidak dipakai. |
| `DEPLOY.md` | Panduan ini, tidak dipakai server. |
| `setup.sql` | Diimport langsung dari komputer lewat phpMyAdmin, tidak perlu di server. |
| `buat_hash_admin.php` | Dipakai di komputer untuk membuat hash password. |
| `config.php` (yang lama) | Berisi kredensial database lokal. Buat yang baru di server. |
| `.git`, `.gitignore` | History Git, tidak perlu di server. |
| `foto/Gemini_Generated_Image_*` | Gambar sisa yang tidak dipakai halaman. |

### Cara upload

**Cara A: File Manager (paling gampang)**

1. Masuk cPanel → **File Manager** → buka folder `public_html`.
2. Klik **Upload**, pilih file yang mau diunggah.
3. Buat folder `admin` dan `foto` dulu (ada tombol **+ Folder**), lalu masuk ke
   tiap folder untuk upload isinya.

**Cara B: FTP (lebih cepat kalau file banyak)**

1. Di cPanel buka **FTP Accounts**, buat user FTP dan catat host, username, password.
2. Buka FileZilla → **File → New Site**:
   - Host: `ftp.namadomainmu` (atau `ftp.yourwebsite.com` untuk Hostinger)
   - Username: user FTP yang tadi dibuat
   - Password: password FTP
   - Port: 21
3. Kiri lokal = folder project, kanan server = `public_html`.
4. Drag file yang perlu saja.

---

## 5. Buat Database

1. cPanel → **MySQL Databases**.
2. **Create New Database**: nama `portofolio` → jadi `akunmu_portofolio`.
3. **Create New User**: nama `portofolio` → jadi `akunmu_portofolio`, password
   acak minimal 10 karakter.
4. Di tabel **Users**, centang user yang baru dibuat → centang database
   `akunmu_portofolio` → klik **Add User to Database** → centang **ALL PRIVILEGES**.
5. Catat tiga hal ini, dipakai di langkah 7:
   - nama database lengkap: `akunmu_portofolio`
   - username database: `akunmu_portofolio`
   - password database

---

## 6. Import Database

1. cPanel → **phpMyAdmin**.
2. Klik database `akunmu_portofolio` di daftar kiri.
3. Tab **Import** → **Choose File** → pilih `setup.sql` dari komputer → **Go**.

**Wajar kalau muncul error di akhir.** File `setup.sql` punya prosedur
`cek_password_admin()` yang sengaja berhenti dengan pesan
`Admin belum diisi. Set @ADMIN_HASH (bcrypt) atau @ADMIN_PASS sebelum SOURCE setup.sql`.
Itu bukan kegagalan: semua tabel dan data seed sudah terimport, dan pesan itu
justru melindungi supaya website tidak online dengan password default. Akun admin
kita buat sendiri di langkah 8.

4. Balik ke tab **Import** → upload `tools/keluaran/konten.sql` → **Go**.
   File ini menimpa isi konten dengan versi terbaru dari komputer dan menambah
   4 kunci poster yang sebelumnya belum ada. Aman diimport berulang kali.

5. Tab **Structure** → pastikan ada 8 tabel: `konten`, `sosial`, `project`,
   `skill`, `pendidikan`, `pengalaman`, `sertifikat`, `user`.

6. Kalau database sudah ada dari versi sebelumnya, **jangan import ulang
   `setup.sql`** karena file itu menghapus tabel. Jalankan
   `php tools/migrasi_fase2.php` dari komputer dengan `config.php` yang
   mengarah ke database tersebut. Skrip ini hanya menambah kolom dan tabel
   baru, tanpa menghapus data lama.

---

## 7. Buat `config.php` di Server

`config.php` di server **wajib** berdasar `config.contoh.php`, bukan file baru
yang dibuat dari nol. File itu sudah berisi semua fungsi bantu yang dipakai
seluruh halaman: `db()`, `esc()`, `konten()`, `csrf_*()`, `login_terautentikasi()`,
`simpan_upload_gambar()`, dan `gambar_varian()`. Kalau `config.php` isinya
cuma konfigurasi database, situs akan error "Call to undefined function".

1. Upload `config.contoh.php` ke `public_html` (baris 4 di langkah 4).
2. Di cPanel → **File Manager** → `public_html`, klik kanan `config.contoh.php`
   → **Rename** → ubah namanya jadi `config.php`.
3. Klik kanan `config.php` → **Edit**, lalu ganti 4 baris ini saja:

   ```php
   const DB_HOST = 'localhost';
   const DB_NAME = 'akunmu_portofolio';
   const DB_USER = 'akunmu_portofolio';
   const DB_PASS = 'password_database_mimu';
   ```

   Sisanya **jangan dihapus**, termasuk fungsi-fungsi bantu yang sudah ada.
4. Simpan. Ubah permission jadi `644` (lihat langkah 10).

Penting: `DB_HOST` harus `localhost`, bukan `127.0.0.1`. Nilai `127.0.0.1` di
`config.contoh.php` itu untuk XAMPP di komputer sendiri.

---

## 8. Buat Akun Admin

Ada dua cara, pilih salah satu.

### Cara A: bcrypt (paling aman)

1. Di komputer, dari folder project, jalankan:

```powershell
php buat_hash_admin.php "password_pilihanmu"
```

2. Salin hash panjang yang muncul (dimulai dengan `$2y$`).
3. Di phpMyAdmin → database → tab **SQL** → jalankan:

```sql
INSERT INTO user (username, password) VALUES
('admin', 'HASH_BCRYPT_YANG_KAMU_SALIN');
```

Kalau muncul Duplicate entry, berarti akun `admin` sudah ada. Pakai ini:

```sql
UPDATE user SET password = 'HASH_BCRYPT_YANG_KAMU_SALIN' WHERE username = 'admin';
```

### Cara B: tanpa terminal

Kalau hosting tidak menyediakan Terminal, pakai hash SHA-256. Ini otomatis
naik jadi bcrypt saat login pertama (sudah ditangani `admin/login.php`):

```sql
INSERT INTO user (username, password) VALUES
('admin', SHA2('password_pilihanmu', 256));
```

### Soal password

Pakai password yang kuat, minimal 12 karakter, dan jangan sama dengan email.
Akun `admin` ini punya akses penuh ke dashboard, jadi password lemah = website
bisa diisis ulang oleh orang lain.

---

## 9. Aktifkan SSL (HTTPS)

1. cPanel → **SSL/TLS Status** (di Hostinger: menu **Security → SSL**).
2. Klik **Run AutoSSL** untuk domainmu, tunggu sampai selesai (beberapa menit).
3. Kalau AutoSSL gagal, sertifikat gratis dari Let's Encrypt tetap bisa dibuat
   manual: **SSL/TLS → Let's Encrypt → Issue Certificate**.
4. Nyalakan **Force HTTPS Redirect** supaya semua pengunjung otomatis masuk
   lewat `https://`.

Kenapa wajib: tanpa HTTPS, password adminmu terkirim dalam bentuk biasa dan
bisa dicegat di tengah jalan. Browser juga akan nampilin tanda "Not secure".

Kalau hosting gratismu nggak punya fitur SSL, pakai **Cloudflare** (gratis):
daftarkan domainmu di Cloudflare, ganti name server di cPanel dengan milik
Cloudflare, lalu aktifkan SSL mode **Full (strict)**.

---

## 10. Permission File dan Folder

Izin yang benar:

| Item | Izin | Alasan |
|---|---|---|
| `config.php` | 644 | Isinya password database, cuma PHP boleh baca. |
| Berkas PHP lain | 644 | Normal. |
| Folder `foto/`, `foto/uploads/` | 755 | PHP perlu boleh nulis gambar hasil upload. |
| Folder lain | 755 | Normal. |

Cara mengubah lewat File Manager: klik kanan berkas atau folder →
**Change Permissions** → pilih angka → **Change Permissions**.

Kalau cPanel punya Terminal, lebih cepat:

```bash
chmod 644 config.php
chmod -R 755 foto
```

Dua hal yang paling sering bikin upload gambar gagal di dashboard: folder
`foto/uploads` belum ada, atau izinnya belum 755. Pastikan foldernya ada dan
writable sebelum dipakai.

---

## 11. Uji Website

Buka `https://namadomainmu` dan cek:

1. **Beranda** — masthead The Daily Bugle, hero, lalu tujuh bagian: Beranda,
   Tentang, Keahlian, Pendidikan, Pengalaman, Project, dan Kontak. Tekan `F12`
   → Console → harus tidak ada error merah.
2. **Admin** — buka `https://namadomainmu/admin/`, login dengan username `admin`
   dan password tadi. Kalau masuk, coba ubah satu teks di dashboard, lalu buka
   beranda lagi untuk memastikan perubahannya muncul.
3. **Wajah/Topeng** — klik tombol Topeng; gambar hero berubah dan keterangannya
   ikut berubah. Klik Wajah untuk kembali.
4. **Baca Edisi** — klik salah satu kartu project. Dialog terbuka; tutup dengan
   tombol X, tombol Esc, atau klik area gelap di luar dialog.
5. **Navigasi** — scroll ke tiap bagian. Menu aktif berubah dan garis tipis di
   bawah header ikut memanjang. Di layar HP, tombol menu membuka/menutup daftar.
6. **Salin email** — klik tombol "Salin" di bagian Kontak. Tulisan berubah jadi
   "Email tersalin." lalu kembali ke "Salin" sendiri.
7. **Tombol atas** — scroll jauh ke bawah, tombol panah muncul di pojok kanan
   bawah. Klik, halaman naik ke Beranda.

Kalau semuanya beres, website sudah online.

---

## 12. Cara Update Website

Kalau kamu ngubah isi dari dashboard admin, tidak perlu upload ulang apa pun.
Cukup:

1. Dari komputer: `python tools/kelola_konten.py export`
2. Di phpMyAdmin → **Import** → upload `tools/keluaran/konten.sql` → **Go**.

Kalau kamu ngoding (ganti isi `index.php`, `style-beranda.css`, `beranda.js`,
atau file admin):

1. Upload ulang file yang berubah ke `public_html`.
2. Kalau `config.php` ikut berubah, edit lagi lewat File Manager.
3. Kalau nama file baru dipakai, cek juga referensinya di `index.php`.

---

## 13. Kalau Ada Masalah

| Gejala | Penyebab | Solusi |
|---|---|---|
| Halaman putih / "Database belum bisa dihubungi" | Kredensial DB salah | Cek ulang `DB_NAME`, `DB_USER`, `DB_PASS` di `config.php`. Pastikan user DB sudah diberi ALL PRIVILEGES. |
| Halaman putih polos tanpa pesan | PHP error disembunyikan | cPanel → **Error Log**, atau ubah `display_errors` jadi `On` sementara di `config.php`. |
| "Call to undefined function ..." | `config.php` di server dibuat dari nol, tanpa fungsi bantu | Upload ulang `config.contoh.php`, rename jadi `config.php`, lalu edit 4 baris `DB_*` (lihat langkah 7). |
| "Fatal error: syntax error" | PHP terlalu lama | cPanel → **Select PHP Version** → pilih PHP 8.2. |
| Tampilan berantakan, gaya nggak muncul | `style-beranda.css`, `beranda.js`, atau folder `foto/` tidak ikut terupload | Upload ulang `style-beranda.css`, `beranda.js`, dan folder `foto/`. |
| Gambar project nggak muncul | Nilai `gambar` di database menunjuk file yang tidak ada | Jalankan `python tools/audit_konten.py --db` di komputer untuk melihat file mana yang hilang. |
| Login admin selalu gagal | Akun admin belum dibuat | Ulangi langkah 8, cek username `admin` (tanpa spasi). |
| website cannot connect: too many connections / timeout | Batas shared hosting kecil | Matikan plugin atau cron yang tidak perlu, kurangi query berat. |
| Upload FTP ditolak | FTP aktif tapi pakai FTPS/SFTP | Ganti protocol ke FTP biasa (21), matikan mode pasif kalau gagal. |

---

## 14. Checklist Sebelum Upload

- [ ] PHP 8.2 sudah dipilih di cPanel
- [ ] Database dibuat, user dibuat, ALL PRIVILEGES sudah diberikan
- [ ] `setup.sql` sudah diimport (error di akhir diabaikan)
- [ ] `tools/keluaran/konten.sql` sudah diimport
- [ ] `config.php` dibuat di server dari `config.contoh.php` (rename), dengan `DB_HOST` = `localhost`
- [ ] Akun admin dibuat
- [ ] `style.css` ikut terupload karena masih dipakai dashboard admin
- [ ] `admin/`, `foto/`, `style-beranda.css`, `beranda.js`, dan semua file PHP/CSS/JS sudah terupload
- [ ] Varian `-480.webp`, `-768.webp`, `-1200.webp` ikut terupload bersama `foto/`
- [ ] `tools/`, `design-demos/`, `arsip/`, `setup.sql`, `buat_hash_admin.php` tidak ikut terupload
- [ ] SSL aktif, `https://` bisa dibuka, Force HTTPS Redirect menyala
- [ ] `config.php` permission 644, folder `foto/uploads` ada dengan permission 755
- [ ] Website dibuka dan dicek di browser
