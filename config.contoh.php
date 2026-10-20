<?php
// Koneksi database + helper bersama untuk halaman publik dan admin.
// Salin file ini menjadi config.php lalu isi nilai database yang sebenarnya.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const DB_HOST = '127.0.0.1';
const DB_NAME = 'portofolio';
const DB_USER = 'ISI_USERNAME_DATABASE';
const DB_PASS = 'ISI_PASSWORD_DATABASE';

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function esc(?string $teks): string
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

// Ambil satu nilai konten berdasarkan section + kunci, dengan nilai cadangan.
function konten(PDO $pdo, string $section, string $kunci, string $fallback = ''): string
{
    static $stmt = null;
    if ($stmt === null) {
        $stmt = $pdo->prepare('SELECT nilai FROM konten WHERE section = ? AND kunci = ?');
    }
    $stmt->execute([$section, $kunci]);
    $nilai = $stmt->fetchColumn();
    return $nilai === false ? $fallback : (string) $nilai;
}

// ---- CSRF: pelindung form agar tidak bisa disubmit dari situs lain ----
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . esc(csrf_token()) . '">';
}

function csrf_check(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        exit('Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.');
    }
}

function login_terautentikasi(): bool
{
    return !empty($_SESSION['login']);
}

// Simpan file gambar yang diunggah ke folder foto/uploads.
// Mengembalikan path relatif untuk disimpan ke database, atau null bila tidak ada file.
function simpan_upload_gambar(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload gagal (kode ' . $file['error'] . ').');
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Berkas upload tidak valid.');
    }

    $mimeDiizinkan = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file($file['tmp_name']);
    if (!isset($mimeDiizinkan[$mime])) {
        throw new RuntimeException('Format gambar harus JPG, PNG, WEBP, atau GIF.');
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Ukuran gambar maksimal 5 MB.');
    }

    $folder = __DIR__ . '/foto/uploads';
    if (!is_dir($folder) && !mkdir($folder, 0775, true) && !is_dir($folder)) {
        throw new RuntimeException('Folder upload tidak bisa dibuat.');
    }

    $nama = bin2hex(random_bytes(8)) . '.' . $mimeDiizinkan[$mime];
    if (!move_uploaded_file($file['tmp_name'], $folder . '/' . $nama)) {
        throw new RuntimeException('Gambar gagal disimpan.');
    }
    return 'foto/uploads/' . $nama;
}
