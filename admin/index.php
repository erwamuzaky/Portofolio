<?php
require __DIR__ . '/../config.php';

if (!login_terautentikasi()) {
    header('Location: login.php');
    exit;
}

$pdo = db();

// ---------- Proses aksi (POST) lalu redirect (PRG) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $aksi = $_POST['aksi'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    try {
        if ($aksi === 'simpan_konten') {
            $section = $_POST['section'] ?? '';
            $stmt = $pdo->prepare('UPDATE konten SET nilai = ? WHERE id = ? AND section = ?');
            foreach ($_POST['nilai'] ?? [] as $idKonten => $nilai) {
                $stmt->execute([(string) $nilai, (int) $idKonten, $section]);
            }
            $_SESSION['pesan'] = 'Konten "' . $section . '" berhasil disimpan.';
        } elseif ($aksi === 'tambah_sosial') {
            $stmt = $pdo->prepare('INSERT INTO sosial (nama, url, urutan) VALUES (?, ?, ?)');
            $stmt->execute([
                trim($_POST['nama'] ?? ''),
                trim($_POST['url'] ?? ''),
                (int) ($_POST['urutan'] ?? 0),
            ]);
            $_SESSION['pesan'] = 'Link sosial ditambahkan.';
        } elseif ($aksi === 'ubah_sosial') {
            $stmt = $pdo->prepare('UPDATE sosial SET nama = ?, url = ?, urutan = ? WHERE id = ?');
            $stmt->execute([
                trim($_POST['nama'] ?? ''),
                trim($_POST['url'] ?? ''),
                (int) ($_POST['urutan'] ?? 0),
                $id,
            ]);
            $_SESSION['pesan'] = 'Link sosial diperbarui.';
        } elseif ($aksi === 'hapus_sosial') {
            $pdo->prepare('DELETE FROM sosial WHERE id = ?')->execute([$id]);
            $_SESSION['pesan'] = 'Link sosial dihapus.';
        } elseif ($aksi === 'tambah_project') {
            $gambar = simpan_upload_gambar($_FILES['gambar_file'] ?? [])
                ?? trim($_POST['gambar'] ?? '');
            $stmt = $pdo->prepare('INSERT INTO project (judul, deskripsi, gambar, tag, link, urutan) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                trim($_POST['judul'] ?? ''),
                trim($_POST['deskripsi'] ?? ''),
                $gambar,
                trim($_POST['tag'] ?? ''),
                trim($_POST['link'] ?? ''),
                (int) ($_POST['urutan'] ?? 0),
            ]);
            $_SESSION['pesan'] = 'Project ditambahkan.';
        } elseif ($aksi === 'ubah_project') {
            $gambarBaru = simpan_upload_gambar($_FILES['gambar_file'] ?? []);
            $gambar = $gambarBaru ?? trim($_POST['gambar'] ?? '');
            $stmt = $pdo->prepare('UPDATE project SET judul = ?, deskripsi = ?, gambar = ?, tag = ?, link = ?, urutan = ? WHERE id = ?');
            $stmt->execute([
                trim($_POST['judul'] ?? ''),
                trim($_POST['deskripsi'] ?? ''),
                $gambar,
                trim($_POST['tag'] ?? ''),
                trim($_POST['link'] ?? ''),
                (int) ($_POST['urutan'] ?? 0),
                $id,
            ]);
            $_SESSION['pesan'] = $gambarBaru ? 'Project diperbarui dan gambar baru diunggah.' : 'Project diperbarui.';
        } elseif ($aksi === 'hapus_project') {
            $pdo->prepare('DELETE FROM project WHERE id = ?')->execute([$id]);
            $_SESSION['pesan'] = 'Project dihapus.';
        } elseif ($aksi === 'tambah_skill') {
            $kategori = $_POST['kategori'] === 'soft' ? 'soft' : 'hard';
            $stmt = $pdo->prepare('INSERT INTO skill (nama, ikon, kategori, deskripsi, urutan) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([
                trim($_POST['nama'] ?? ''),
                trim($_POST['ikon'] ?? ''),
                $kategori,
                trim($_POST['deskripsi'] ?? ''),
                (int) ($_POST['urutan'] ?? 0),
            ]);
            $_SESSION['pesan'] = 'Skill ditambahkan.';
        } elseif ($aksi === 'ubah_skill') {
            $kategori = $_POST['kategori'] === 'soft' ? 'soft' : 'hard';
            $stmt = $pdo->prepare('UPDATE skill SET nama = ?, ikon = ?, kategori = ?, deskripsi = ?, urutan = ? WHERE id = ?');
            $stmt->execute([
                trim($_POST['nama'] ?? ''),
                trim($_POST['ikon'] ?? ''),
                $kategori,
                trim($_POST['deskripsi'] ?? ''),
                (int) ($_POST['urutan'] ?? 0),
                $id,
            ]);
            $_SESSION['pesan'] = 'Skill diperbarui.';
        } elseif ($aksi === 'hapus_skill') {
            $pdo->prepare('DELETE FROM skill WHERE id = ?')->execute([$id]);
            $_SESSION['pesan'] = 'Skill dihapus.';
        } elseif ($aksi === 'tambah_pendidikan') {
            $stmt = $pdo->prepare('INSERT INTO pendidikan (nama, jurusan, tahun, urutan) VALUES (?, ?, ?, ?)');
            $stmt->execute([
                trim($_POST['nama'] ?? ''),
                trim($_POST['jurusan'] ?? ''),
                trim($_POST['tahun'] ?? ''),
                (int) ($_POST['urutan'] ?? 0),
            ]);
            $_SESSION['pesan'] = 'Riwayat pendidikan ditambahkan.';
        } elseif ($aksi === 'ubah_pendidikan') {
            $stmt = $pdo->prepare('UPDATE pendidikan SET nama = ?, jurusan = ?, tahun = ?, urutan = ? WHERE id = ?');
            $stmt->execute([
                trim($_POST['nama'] ?? ''),
                trim($_POST['jurusan'] ?? ''),
                trim($_POST['tahun'] ?? ''),
                (int) ($_POST['urutan'] ?? 0),
                $id,
            ]);
            $_SESSION['pesan'] = 'Riwayat pendidikan diperbarui.';
        } elseif ($aksi === 'hapus_pendidikan') {
            $pdo->prepare('DELETE FROM pendidikan WHERE id = ?')->execute([$id]);
            $_SESSION['pesan'] = 'Riwayat pendidikan dihapus.';
        } elseif ($aksi === 'ganti_password') {
            $stmt = $pdo->prepare('SELECT password FROM user WHERE username = ?');
            $stmt->execute(['admin']);
            $hashLama = (string) $stmt->fetchColumn();
            $lama = $_POST['lama'] ?? '';
            $baru = $_POST['baru'] ?? '';
            if (!hash_equals($hashLama, hash('sha256', $lama))) {
                $_SESSION['galat'] = 'Password lama salah.';
            } elseif (strlen($baru) < 8) {
                $_SESSION['galat'] = 'Password baru minimal 8 karakter.';
            } else {
                $pdo->prepare('UPDATE user SET password = ? WHERE username = ?')
                    ->execute([hash('sha256', $baru), 'admin']);
                $_SESSION['pesan'] = 'Password berhasil diganti.';
            }
        }
    } catch (PDOException $e) {
        $_SESSION['galat'] = 'Gagal menyimpan: ' . $e->getMessage();
    }

    header('Location: index.php');
    exit;
}

// ---------- Ambil data untuk tampilan ----------
$pesan = $_SESSION['pesan'] ?? null;
$galat = $_SESSION['galat'] ?? null;
unset($_SESSION['pesan'], $_SESSION['galat']);

$semuaKonten = $pdo->query('SELECT * FROM konten ORDER BY section, id')->fetchAll();
$kontenPerSection = [];
foreach ($semuaKonten as $baris) {
    $kontenPerSection[$baris['section']][] = $baris;
}
$judulSection = [
    'hero' => 'Bagian Beranda (Hero)',
    'about' => 'Bagian About',
    'contact' => 'Bagian Kontak',
    'footer' => 'Bagian Footer',
];

$sosial = $pdo->query('SELECT * FROM sosial ORDER BY urutan, id')->fetchAll();
$projects = $pdo->query('SELECT * FROM project ORDER BY urutan, id')->fetchAll();
$skills = $pdo->query('SELECT * FROM skill ORDER BY kategori, urutan, id')->fetchAll();
$pendidikan = $pdo->query('SELECT * FROM pendidikan ORDER BY urutan, id')->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | Portofolio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="../style.css">
</head>

<body class="admin">

    <div class="admin-shell">

        <aside class="admin-sidebar">
            <div class="admin-brand">
                <span class="brand-mark">EM</span>
                <span class="brand-text">
                    <strong>Portofolio</strong>
                    <small>Panel Admin</small>
                </span>
            </div>

            <nav class="admin-nav" aria-label="Menu admin">
                <a href="#konten">
                    <svg class="ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
                    Konten Teks
                </a>
                <a href="#sosial">
                    <svg class="ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1"/><path d="M14 11a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1"/></svg>
                    Link Sosial
                </a>
                <a href="#project">
                    <svg class="ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18"/></svg>
                    Project
                </a>
                <a href="#skill">
                    <svg class="ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 20V10M10 20V4M16 20v-8M22 20H2"/></svg>
                    Skill
                </a>
                <a href="#pendidikan">
                    <svg class="ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 3 3 6 3s6-2 6-3v-5"/></svg>
                    Pendidikan
                </a>
                <a href="#password">
                    <svg class="ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                    Ganti Password
                </a>
            </nav>

            <div class="sidebar-foot">
                <a href="../index.php" target="_blank" class="btn btn-secondary">
                    <svg class="ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M14 3h7v7M21 3l-9 9"/><path d="M19 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h5"/></svg>
                    Lihat Situs
                </a>
                <a href="logout.php" class="btn btn-danger">
                    <svg class="ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/></svg>
                    Keluar
                </a>
            </div>
        </aside>

        <main class="admin-content">

            <div class="admin-topbar">
                <div>
                    <h1>Dashboard</h1>
                    <p>Kelola isi portofolio kamu dari sini. Perubahan langsung tampil di halaman publik.</p>
                </div>
            </div>

            <?php if ($pesan): ?>
            <p class="pesan sukses" role="status"><?= esc($pesan) ?></p>
            <?php endif; ?>
            <?php if ($galat): ?>
            <p class="pesan galat" role="alert"><?= esc($galat) ?></p>
            <?php endif; ?>

            <div class="stat-grid">
                <div class="stat-card highlight">
                    <span class="stat-num"><?= count($projects) ?></span>
                    <span class="stat-label">Project</span>
                </div>
                <div class="stat-card">
                    <span class="stat-num"><?= count($skills) ?></span>
                    <span class="stat-label">Skill</span>
                </div>
                <div class="stat-card">
                    <span class="stat-num"><?= count($sosial) ?></span>
                    <span class="stat-label">Link Sosial</span>
                </div>
                <div class="stat-card">
                    <span class="stat-num"><?= count($pendidikan) ?></span>
                    <span class="stat-label">Pendidikan</span>
                </div>
            </div>

            <section class="panel" id="konten">
                <div class="panel-head">
                    <h2>Konten Teks</h2>
                    <span class="panel-hint">Teks hero, about, kontak, footer</span>
                </div>
                <?php foreach ($judulSection as $section => $judul): ?>
                <?php if (empty($kontenPerSection[$section])) continue; ?>
                <h3 class="panel-sub"><?= esc($judul) ?></h3>
                <form method="post" class="form form-2kolom">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="simpan_konten">
                    <input type="hidden" name="section" value="<?= esc($section) ?>">
                    <?php foreach ($kontenPerSection[$section] as $baris): ?>
                    <div class="<?= strlen($baris['nilai']) > 90 ? 'lebar-penuh' : '' ?>">
                        <label for="k<?= $baris['id'] ?>"><?= esc(str_replace('_', ' ', $baris['kunci'])) ?></label>
                        <?php if (strlen($baris['nilai']) > 90): ?>
                        <textarea id="k<?= $baris['id'] ?>" name="nilai[<?= $baris['id'] ?>]" rows="3"><?= esc($baris['nilai']) ?></textarea>
                        <?php else: ?>
                        <input type="text" id="k<?= $baris['id'] ?>" name="nilai[<?= $baris['id'] ?>]" value="<?= esc($baris['nilai']) ?>">
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <div class="form-aksi lebar-penuh">
                        <button type="submit" class="btn btn-primary">Simpan <?= esc($judul) ?></button>
                    </div>
                </form>
                <?php endforeach; ?>
                <p class="catatan">Untuk daftar keahlian di About, pisahkan tiap baris dengan tanda <code>|</code>.</p>
            </section>

            <section class="panel" id="sosial">
                <div class="panel-head">
                    <h2>Link Sosial</h2>
                    <span class="panel-hint"><?= count($sosial) ?> tautan</span>
                </div>

                <?php foreach ($sosial as $item): ?>
                <div class="entri">
                    <div class="entri-kepala">
                        <h3><span class="nomor"><?= esc((string) $item['urutan']) ?></span><?= esc($item['nama']) ?></h3>
                    </div>
                    <form method="post" class="form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="ubah_sosial">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <div class="form-2kolom">
                            <div>
                                <label for="sn<?= $item['id'] ?>">Nama</label>
                                <input type="text" id="sn<?= $item['id'] ?>" name="nama" value="<?= esc($item['nama']) ?>" required>
                            </div>
                            <div>
                                <label for="so<?= $item['id'] ?>">Urutan</label>
                                <input type="number" id="so<?= $item['id'] ?>" name="urutan" value="<?= (int) $item['urutan'] ?>">
                            </div>
                            <div class="lebar-penuh">
                                <label for="su<?= $item['id'] ?>">URL</label>
                                <input type="url" id="su<?= $item['id'] ?>" name="url" value="<?= esc($item['url']) ?>" required>
                            </div>
                        </div>
                        <div class="form-aksi">
                            <button type="submit" class="btn btn-secondary">Simpan</button>
                            <span class="spacer"></span>
                        </div>
                    </form>
                    <form method="post" class="form-aksi" onsubmit="return confirm('Hapus link ini?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="hapus_sosial">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
                <?php endforeach; ?>

                <h3 class="panel-sub">Tambah Link Sosial</h3>
                <form method="post" class="form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="tambah_sosial">
                    <div class="form-2kolom">
                        <div>
                            <label for="sns-baru">Nama</label>
                            <input type="text" id="sns-baru" name="nama" placeholder="TikTok" required>
                        </div>
                        <div>
                            <label for="sos-baru">Urutan</label>
                            <input type="number" id="sos-baru" name="urutan" value="0">
                        </div>
                        <div class="lebar-penuh">
                            <label for="sus-baru">URL</label>
                            <input type="url" id="sus-baru" name="url" placeholder="https://tiktok.com/username" required>
                        </div>
                    </div>
                    <div class="form-aksi">
                        <button type="submit" class="btn btn-primary">Tambah Link</button>
                    </div>
                </form>
            </section>

            <section class="panel" id="project">
                <div class="panel-head">
                    <h2>Project</h2>
                    <span class="panel-hint"><?= count($projects) ?> project</span>
                </div>

                <?php foreach ($projects as $item): ?>
                <div class="entri">
                    <div class="entri-kepala">
                        <h3><span class="nomor"><?= esc((string) $item['urutan']) ?></span><?= esc($item['judul']) ?></h3>
                        <?php if ($item['tag']): ?>
                        <span class="badge biru"><?= esc($item['tag']) ?></span>
                        <?php endif; ?>
                    </div>
                    <form method="post" class="form" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="ubah_project">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <label for="pj<?= $item['id'] ?>">Judul</label>
                        <input type="text" id="pj<?= $item['id'] ?>" name="judul" value="<?= esc($item['judul']) ?>" required>
                        <label for="pd<?= $item['id'] ?>">Deskripsi</label>
                        <textarea id="pd<?= $item['id'] ?>" name="deskripsi" rows="4" required><?= esc($item['deskripsi']) ?></textarea>
                        <div class="form-2kolom">
                            <div class="lebar-penuh">
                                <label for="pl<?= $item['id'] ?>">Link Website Project</label>
                                <input type="url" id="pl<?= $item['id'] ?>" name="link" value="<?= esc($item['link']) ?>" placeholder="https://contoh.com">
                            </div>
                            <div>
                                <label for="pt<?= $item['id'] ?>">Tag Teknologi</label>
                                <input type="text" id="pt<?= $item['id'] ?>" name="tag" value="<?= esc($item['tag']) ?>">
                            </div>
                            <div>
                                <label for="pu<?= $item['id'] ?>">Urutan</label>
                                <input type="number" id="pu<?= $item['id'] ?>" name="urutan" value="<?= (int) $item['urutan'] ?>">
                            </div>
                            <div>
                                <label for="pf<?= $item['id'] ?>">Ganti Gambar (upload, opsional)</label>
                                <input type="file" id="pf<?= $item['id'] ?>" name="gambar_file" accept="image/*">
                            </div>
                            <div>
                                <label for="pg<?= $item['id'] ?>">atau path/URL Gambar</label>
                                <input type="text" id="pg<?= $item['id'] ?>" name="gambar" value="<?= esc($item['gambar']) ?>">
                            </div>
                            <?php if ($item['gambar']): ?>
                            <div class="lebar-penuh">
                                <img src="../<?= esc($item['gambar']) ?>" alt="Gambar <?= esc($item['judul']) ?>" class="pratinjau">
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="form-aksi">
                            <button type="submit" class="btn btn-secondary">Simpan Perubahan</button>
                            <span class="spacer"></span>
                        </div>
                    </form>
                    <form method="post" class="form-aksi" onsubmit="return confirm('Hapus project ini?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="hapus_project">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <button type="submit" class="btn btn-danger">Hapus Project</button>
                    </form>
                </div>
                <?php endforeach; ?>

                <h3 class="panel-sub">Tambah Project</h3>
                <form method="post" class="form" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="tambah_project">
                    <div class="form-2kolom">
                        <div>
                            <label for="pj-baru">Judul</label>
                            <input type="text" id="pj-baru" name="judul" required>
                        </div>
                        <div>
                            <label for="pt-baru">Tag Teknologi</label>
                            <input type="text" id="pt-baru" name="tag" placeholder="Laravel">
                        </div>
                        <div class="lebar-penuh">
                            <label for="pd-baru">Deskripsi</label>
                            <textarea id="pd-baru" name="deskripsi" rows="4" required></textarea>
                        </div>
                        <div class="lebar-penuh">
                            <label for="pl-baru">Link Website Project</label>
                            <input type="url" id="pl-baru" name="link" placeholder="https://contoh.com">
                        </div>
                        <div>
                            <label for="pf-baru">Upload Gambar (opsional)</label>
                            <input type="file" id="pf-baru" name="gambar_file" accept="image/*">
                        </div>
                        <div>
                            <label for="pg-baru">atau path/URL Gambar</label>
                            <input type="text" id="pg-baru" name="gambar" placeholder="foto/proyek1.png">
                        </div>
                        <div>
                            <label for="pu-baru">Urutan</label>
                            <input type="number" id="pu-baru" name="urutan" value="0">
                        </div>
                    </div>
                    <div class="form-aksi">
                        <button type="submit" class="btn btn-primary">Tambah Project</button>
                    </div>
                </form>
            </section>

            <section class="panel" id="skill">
                <div class="panel-head">
                    <h2>Skill</h2>
                    <span class="panel-hint"><?= count($skills) ?> skill</span>
                </div>

                <?php foreach ($skills as $item): ?>
                <div class="entri">
                    <div class="entri-kepala">
                        <h3><span class="nomor"><?= esc((string) $item['urutan']) ?></span><?= esc($item['nama']) ?></h3>
                        <span class="badge <?= $item['kategori'] === 'hard' ? 'merah' : 'biru' ?>"><?= esc($item['kategori']) ?></span>
                    </div>
                    <form method="post" class="form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="ubah_skill">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <div class="form-2kolom">
                            <div>
                                <label for="skn<?= $item['id'] ?>">Nama</label>
                                <input type="text" id="skn<?= $item['id'] ?>" name="nama" value="<?= esc($item['nama']) ?>" required>
                            </div>
                            <div>
                                <label for="skk<?= $item['id'] ?>">Kategori</label>
                                <select id="skk<?= $item['id'] ?>" name="kategori">
                                    <option value="hard" <?= $item['kategori'] === 'hard' ? 'selected' : '' ?>>Hard</option>
                                    <option value="soft" <?= $item['kategori'] === 'soft' ? 'selected' : '' ?>>Soft</option>
                                </select>
                            </div>
                            <div>
                                <label for="sku<?= $item['id'] ?>">Urutan</label>
                                <input type="number" id="sku<?= $item['id'] ?>" name="urutan" value="<?= (int) $item['urutan'] ?>">
                            </div>
                            <div>
                                <label for="ski<?= $item['id'] ?>">Ikon (URL, khusus hard skill)</label>
                                <input type="text" id="ski<?= $item['id'] ?>" name="ikon" value="<?= esc($item['ikon']) ?>">
                            </div>
                            <div class="lebar-penuh">
                                <label for="skd<?= $item['id'] ?>">Deskripsi (khusus soft skill)</label>
                                <textarea id="skd<?= $item['id'] ?>" name="deskripsi" rows="2"><?= esc($item['deskripsi']) ?></textarea>
                            </div>
                        </div>
                        <div class="form-aksi">
                            <button type="submit" class="btn btn-secondary">Simpan Perubahan</button>
                            <span class="spacer"></span>
                        </div>
                    </form>
                    <form method="post" class="form-aksi" onsubmit="return confirm('Hapus skill ini?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="hapus_skill">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
                <?php endforeach; ?>

                <h3 class="panel-sub">Tambah Skill</h3>
                <form method="post" class="form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="tambah_skill">
                    <div class="form-2kolom">
                        <div>
                            <label for="sn-baru">Nama</label>
                            <input type="text" id="sn-baru" name="nama" required>
                        </div>
                        <div>
                            <label for="sk-baru">Kategori</label>
                            <select id="sk-baru" name="kategori">
                                <option value="hard">Hard</option>
                                <option value="soft">Soft</option>
                            </select>
                        </div>
                        <div>
                            <label for="si-baru">Ikon (URL, khusus hard skill)</label>
                            <input type="text" id="si-baru" name="ikon">
                        </div>
                        <div>
                            <label for="su-baru">Urutan</label>
                            <input type="number" id="su-baru" name="urutan" value="0">
                        </div>
                        <div class="lebar-penuh">
                            <label for="sd-baru">Deskripsi (khusus soft skill)</label>
                            <textarea id="sd-baru" name="deskripsi" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="form-aksi">
                        <button type="submit" class="btn btn-primary">Tambah Skill</button>
                    </div>
                </form>
            </section>

            <section class="panel" id="pendidikan">
                <div class="panel-head">
                    <h2>Riwayat Pendidikan</h2>
                    <span class="panel-hint"><?= count($pendidikan) ?> riwayat</span>
                </div>

                <?php foreach ($pendidikan as $item): ?>
                <div class="entri">
                    <div class="entri-kepala">
                        <h3><span class="nomor"><?= esc((string) $item['urutan']) ?></span><?= esc($item['nama']) ?></h3>
                        <?php if ($item['tahun']): ?>
                        <span class="badge biru"><?= esc($item['tahun']) ?></span>
                        <?php endif; ?>
                    </div>
                    <form method="post" class="form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="ubah_pendidikan">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <div class="form-2kolom">
                            <div class="lebar-penuh">
                                <label for="pn<?= $item['id'] ?>">Nama Sekolah</label>
                                <input type="text" id="pn<?= $item['id'] ?>" name="nama" value="<?= esc($item['nama']) ?>" required>
                            </div>
                            <div>
                                <label for="pj<?= $item['id'] ?>">Jurusan / Jenjang</label>
                                <input type="text" id="pj<?= $item['id'] ?>" name="jurusan" value="<?= esc($item['jurusan']) ?>">
                            </div>
                            <div>
                                <label for="pt<?= $item['id'] ?>">Tahun</label>
                                <input type="text" id="pt<?= $item['id'] ?>" name="tahun" value="<?= esc($item['tahun']) ?>">
                            </div>
                            <div>
                                <label for="pu<?= $item['id'] ?>">Urutan</label>
                                <input type="number" id="pu<?= $item['id'] ?>" name="urutan" value="<?= (int) $item['urutan'] ?>">
                            </div>
                        </div>
                        <div class="form-aksi">
                            <button type="submit" class="btn btn-secondary">Simpan</button>
                            <span class="spacer"></span>
                        </div>
                    </form>
                    <form method="post" class="form-aksi" onsubmit="return confirm('Hapus riwayat ini?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="hapus_pendidikan">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
                <?php endforeach; ?>

                <h3 class="panel-sub">Tambah Pendidikan</h3>
                <form method="post" class="form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="tambah_pendidikan">
                    <div class="form-2kolom">
                        <div class="lebar-penuh">
                            <label for="pn-baru">Nama Sekolah</label>
                            <input type="text" id="pn-baru" name="nama" required>
                        </div>
                        <div>
                            <label for="pj-baru">Jurusan / Jenjang</label>
                            <input type="text" id="pj-baru" name="jurusan">
                        </div>
                        <div>
                            <label for="pt-baru">Tahun</label>
                            <input type="text" id="pt-baru" name="tahun" placeholder="2024 – 2027">
                        </div>
                        <div>
                            <label for="pu-baru">Urutan</label>
                            <input type="number" id="pu-baru" name="urutan" value="0">
                        </div>
                    </div>
                    <div class="form-aksi">
                        <button type="submit" class="btn btn-primary">Tambah Pendidikan</button>
                    </div>
                </form>
            </section>

            <section class="panel" id="password">
                <div class="panel-head">
                    <h2>Ganti Password Admin</h2>
                    <span class="panel-hint">Amankan akun kamu</span>
                </div>
                <form method="post" class="form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="ganti_password">
                    <div class="form-2kolom">
                        <div>
                            <label for="plama">Password Lama</label>
                            <input type="password" id="plama" name="lama" required autocomplete="current-password">
                        </div>
                        <div>
                            <label for="pbaru">Password Baru (minimal 8 karakter)</label>
                            <input type="password" id="pbaru" name="baru" required minlength="8" autocomplete="new-password">
                        </div>
                    </div>
                    <div class="form-aksi">
                        <button type="submit" class="btn btn-primary">Ganti Password</button>
                    </div>
                </form>
            </section>

        </main>

    </div>

</body>

</html>
