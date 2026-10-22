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

// Susun src dan srcset untuk gambar, memakai varian responsif yang tersedia.
function gambar_varian(string $jalur): array
{
    $direktori = dirname($jalur);
    $prefix = $direktori === '.' ? '' : $direktori . '/';
    $nama = pathinfo($jalur, PATHINFO_FILENAME);

    $webp = $prefix . $nama . '.webp';
    $src = is_file(__DIR__ . '/' . $webp) ? $webp : $jalur;

    $bagian = [];
    foreach ([480, 768, 1200] as $lebar) {
        $varian = $prefix . $nama . '-' . $lebar . '.webp';
        if (is_file(__DIR__ . '/' . $varian)) {
            $bagian[] = $varian . ' ' . $lebar . 'w';
        }
    }

    return ['src' => $src, 'srcset' => implode(', ', $bagian)];
}

// Cetak atribut src, plus srcset dan sizes bila varian responsif tersedia.
function atribut_gambar(string $jalur, string $sizes = ''): string
{
    $gambar = gambar_varian($jalur);
    $atribut = 'src="' . esc($gambar['src']) . '"';
    if ($gambar['srcset'] !== '') {
        $atribut .= ' srcset="' . esc($gambar['srcset']) . '"';
        if ($sizes !== '') {
            $atribut .= ' sizes="' . esc($sizes) . '"';
        }
    }
    return $atribut;
}

// Ikon SVG untuk platform sosial, dicocokkan dari nama pada tabel sosial.
function ikon_sosial(string $nama): string
{
    static $pola = [
        'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="0.9" fill="currentColor" stroke="none"/>',
        'tiktok' => '<path d="M14 3v10.5a3.5 3.5 0 1 1-3-3.5"/><path d="M14 3c.5 2.2 2.1 3.8 4.5 4"/>',
        'whatsapp' => '<path d="M3.5 20.5l1.4-4.8A8.5 8.5 0 1 1 8 19.1"/><path d="M9 8.7c.2-.4.4-.4.7-.4h.5c.2 0 .4 0 .6.5l.6 1.5c.1.3 0 .5-.2.7l-.4.5c-.2.2-.3.4-.1.7a6.2 6.2 0 0 0 2.8 2.4c.3.1.5.1.7-.1l.5-.6c.2-.2.4-.3.6-.2l1.5.7c.3.1.4.3.4.5 0 .8-.5 1.5-1.2 1.7-2.6.7-6.2-1.2-7.7-4a7.4 7.4 0 0 1-.4-3.9Z"/>',
    ];

    $cadangan = '<path d="M10 13a4 4 0 0 0 5.7 0l2.8-2.8A4 4 0 0 0 12.8 4.5l-1.3 1.3"/><path d="M14 11a4 4 0 0 0-5.7 0l-2.8 2.8A4 4 0 1 0 11.2 19.5l1.3-1.3"/>';
    $huruf = strtolower($nama);
    $isi = $cadangan;

    foreach ($pola as $kunci => $svg) {
        if (str_contains($huruf, $kunci)) {
            $isi = $svg;
            break;
        }
    }

    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $isi . '</svg>';
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
