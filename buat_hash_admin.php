<?php
// Membuat hash bcrypt untuk akun admin, dipakai oleh setup.sql (@ADMIN_HASH).
// Jalankan: php buat_hash_admin.php "password_pilihanmu"

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Skrip ini hanya untuk dijalankan lewat command line.');
}

$password = $argv[1] ?? '';
if ($password === '') {
    fwrite(STDERR, "Pakai: php buat_hash_admin.php \"password_pilihanmu\"" . PHP_EOL);
    exit(1);
}
if (strlen($password) < 8) {
    fwrite(STDERR, 'Password minimal 8 karakter.' . PHP_EOL);
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);

echo 'Hash bcrypt: ' . $hash . PHP_EOL . PHP_EOL;
echo 'Jalankan setup:' . PHP_EOL;
echo '  mysql -uroot --default-character-set=utf8mb4 -e "SET @ADMIN_HASH=\'' . $hash . '\'; SOURCE setup.sql;"' . PHP_EOL;
