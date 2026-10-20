<?php
require __DIR__ . '/../config.php';

if (login_terautentikasi()) {
    header('Location: index.php');
    exit;
}

$galat = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT password FROM user WHERE username = ?');
    $stmt->execute([$username]);
    $hash = $stmt->fetchColumn();

    $cocok = false;
    $perluRehash = false;

    if ($hash !== false && is_string($hash)) {
        if (password_verify($password, $hash)) {
            $cocok = true;
            $perluRehash = password_needs_rehash($hash, PASSWORD_DEFAULT);
        } elseif (preg_match('/^[0-9a-f]{64}$/i', $hash) === 1 && hash_equals(strtolower($hash), hash('sha256', $password))) {
            // Hash lama (SHA-256) -> login sekali, lalu naikkan ke bcrypt.
            $cocok = true;
            $perluRehash = true;
        }
    }

    if ($cocok) {
        if ($perluRehash) {
            $update = db()->prepare('UPDATE user SET password = ? WHERE username = ?');
            $update->execute([password_hash($password, PASSWORD_DEFAULT), $username]);
        }
        session_regenerate_id(true);
        $_SESSION['login'] = true;
        header('Location: index.php');
        exit;
    }
    $galat = 'Username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | Portofolio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="../style.css">
</head>

<body class="admin">

    <div class="login-page">

        <main class="login-box">
            <div class="login-brand">
                <span class="brand-mark">EM</span>
                <div>
                    <h1>Portofolio</h1>
                    <small>Masuk ke panel admin</small>
                </div>
            </div>

            <?php if ($galat): ?>
            <p class="pesan galat" role="alert"><?= esc($galat) ?></p>
            <?php endif; ?>

            <form method="post" class="form">
                <?= csrf_field() ?>
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="username">

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">

                <button type="submit" class="btn btn-primary">Masuk</button>
            </form>
        </main>

    </div>

</body>

</html>
