<?php
require __DIR__ . '/config.php';

try {
    $pdo = db();

    $heroSapaan  = konten($pdo, 'hero', 'sapaan', 'Halo, Saya');
    $heroNama    = konten($pdo, 'hero', 'nama', 'Erwa Muzaky');
    $heroDesk    = konten($pdo, 'hero', 'deskripsi');
    $heroFoto    = konten($pdo, 'hero', 'foto', 'foto/foto.jpeg');

    $aboutBuka   = konten($pdo, 'about', 'paragraf_pembuka');
    $aboutKeahlian = konten($pdo, 'about', 'keahlian');
    $aboutOrg    = konten($pdo, 'about', 'paragraf_organisasi');
    $aboutTutup  = konten($pdo, 'about', 'paragraf_penutup');
    $aboutLokasi = konten($pdo, 'about', 'lokasi');
    $aboutMaps   = konten($pdo, 'about', 'maps_src');
    $aboutJurusan = konten($pdo, 'about', 'jurusan');
    $aboutFokus  = konten($pdo, 'about', 'fokus');

    $contactEmail = konten($pdo, 'contact', 'email');
    $contactPesan = konten($pdo, 'contact', 'pesan');
    $footerTeks   = konten($pdo, 'footer', 'teks');

    $daftarSosial = db()->query('SELECT nama, url FROM sosial ORDER BY urutan, id')->fetchAll();
    $daftarProject = db()->query('SELECT judul, deskripsi, gambar, tag, link FROM project ORDER BY urutan, id')->fetchAll();
    $daftarSkill = db()->query('SELECT nama, ikon, kategori, deskripsi FROM skill ORDER BY urutan, id')->fetchAll();
    $daftarPendidikan = db()->query('SELECT nama, jurusan, tahun FROM pendidikan ORDER BY urutan, id')->fetchAll();

    $pendidikanTerakhir = $daftarPendidikan ? end($daftarPendidikan) : null;
    $posterPeran  = konten($pdo, 'hero', 'poster_peran', $aboutJurusan !== '' ? $aboutJurusan : 'Pelajar');
    $posterFokus  = konten($pdo, 'hero', 'poster_fokus', $aboutFokus !== '' ? $aboutFokus : 'Web Development');
    $posterWaktu  = konten($pdo, 'hero', 'poster_waktu', $pendidikanTerakhir['tahun'] ?? 'Sekarang');
    $posterVertikal = konten($pdo, 'hero', 'poster_vertikal', 'PORTFOLIO — ' . strtoupper($heroNama));

    $hardSkills = array_filter($daftarSkill, fn ($s) => $s['kategori'] === 'hard');
    $softSkills = array_filter($daftarSkill, fn ($s) => $s['kategori'] === 'soft');
    $barisKeahlian = array_values(array_filter(explode('|', $aboutKeahlian)));
} catch (PDOException $e) {
    http_response_code(500);
    exit('<p style="font-family:sans-serif;padding:2rem">Database belum bisa dihubungi. Pastikan MySQL jalan lalu impor <code>setup.sql</code>.</p>');
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($heroNama) ?> | Portofolio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anton&family=Caveat:wght@600&display=swap">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='15' fill='%23090b12'/%3E%3Ccircle cx='16' cy='16' r='13' fill='none' stroke='%23e23636' stroke-width='2'/%3E%3Cpath d='M16 3v26M3 16h26M7 7l18 18M25 7L7 25' stroke='%234f7dff' stroke-width='1' opacity='.55'/%3E%3Ccircle cx='16' cy='16' r='4' fill='%23e23636'/%3E%3C/svg%3E">
    <script>document.documentElement.classList.add('js');</script>
    <script>
        (function () {
            try {
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    document.documentElement.classList.add('loader-selesai');
                    return;
                }
                var catatan = localStorage.getItem('jaring_loader_waktu');
                var batasJam = 12 * 60 * 60 * 1000;
                var baru = catatan && (Date.now() - parseInt(catatan, 10)) < batasJam;
                if (!baru) {
                    document.documentElement.classList.add('loader-aktif');
                } else {
                    document.documentElement.classList.add('loader-selesai');
                }
            } catch (e) {
                document.documentElement.classList.add('loader-selesai');
            }
        })();
    </script>
</head>

<body>

    <header>
        <nav aria-label="Navigasi utama">
            <a href="#home">Home</a>
            <a href="#about">About</a>
            <a href="#project">Project</a>
            <a href="#contact">Contact</a>
        </nav>
    </header>

    <div class="ornamen-jaring ornamen-kanan-atas" aria-hidden="true">
        <svg viewBox="0 0 300 300" focusable="false">
            <g fill="none" stroke="currentColor" stroke-width="1">
                <circle cx="300" cy="0" r="50" />
                <circle cx="300" cy="0" r="100" />
                <circle cx="300" cy="0" r="150" />
                <circle cx="300" cy="0" r="200" />
                <circle cx="300" cy="0" r="250" />
                <path d="M0 300 L300 0 M0 0 L300 300 M0 0 L300 0" />
                <path d="M300 50 Q230 70 200 100 Q170 130 150 200 Q130 270 50 300" />
                <path d="M50 0 Q70 70 100 100 Q130 130 200 150 Q270 170 300 250" />
                <path d="M150 0 Q160 90 200 100 Q240 110 250 150" />
                <path d="M0 150 Q90 160 100 200 Q110 240 150 250" />
            </g>
        </svg>
    </div>

    <div class="ornamen-jaring ornamen-kiri-bawah" aria-hidden="true">
        <svg viewBox="0 0 300 300" focusable="false">
            <g fill="none" stroke="currentColor" stroke-width="1">
                <circle cx="0" cy="300" r="50" />
                <circle cx="0" cy="300" r="100" />
                <circle cx="0" cy="300" r="150" />
                <circle cx="0" cy="300" r="200" />
                <circle cx="0" cy="300" r="250" />
                <path d="M0 0 L300 300 M0 300 L300 0 M0 300 L300 300" />
                <path d="M0 250 Q70 230 100 200 Q130 170 150 100 Q170 30 250 0" />
                <path d="M250 300 Q230 230 200 200 Q170 170 100 150 Q30 130 0 50" />
                <path d="M150 300 Q140 210 100 200 Q60 190 50 150" />
                <path d="M300 150 Q210 140 200 100 Q190 60 150 50" />
            </g>
        </svg>
    </div>

    <div class="ornamen-jaring ornamen-laba" aria-hidden="true">
        <svg viewBox="0 0 120 120" focusable="false">
            <g fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M52 62 Q34 52 22 62 Q30 46 20 36" />
                <path d="M50 70 Q30 66 20 78 Q26 60 16 52" />
                <path d="M52 78 Q34 82 26 96 Q30 78 20 72" />
                <path d="M56 84 Q46 98 44 112 Q40 96 34 90" />
                <path d="M68 62 Q86 52 98 62 Q90 46 100 36" />
                <path d="M70 70 Q90 66 100 78 Q94 60 104 52" />
                <path d="M68 78 Q86 82 94 96 Q90 78 100 72" />
                <path d="M64 84 Q74 98 76 112 Q80 96 86 90" />
            </g>
            <g fill="currentColor">
                <ellipse cx="60" cy="76" rx="15" ry="18" />
                <ellipse cx="60" cy="55" rx="11" ry="10" />
                <path d="M49 50 L44 44 M71 50 L76 44" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
            </g>
        </svg>
    </div>

    <main>

        <!-- ================= 1. HOME / HERO (POSTER) ================= -->
        <?php
        $bagianNama = preg_split('/\s+/', trim($heroNama), 2);
        $namaBaris1 = $bagianNama[0] ?? $heroNama;
        $namaBaris2 = $bagianNama[1] ?? '';
        ?>
        <section id="home" class="hero hero-poster">
            <svg class="poster-jaring" viewBox="0 0 400 400" aria-hidden="true" focusable="false">
                <g fill="none" stroke="currentColor" stroke-width="1">
                    <circle cx="200" cy="200" r="40" />
                    <circle cx="200" cy="200" r="80" />
                    <circle cx="200" cy="200" r="120" />
                    <circle cx="200" cy="200" r="160" />
                    <circle cx="200" cy="200" r="200" />
                    <path d="M200 0 L200 400 M0 200 L400 200 M59 59 L341 341 M341 59 L59 341" />
                    <path d="M200 40 Q260 60 283 117 Q317 140 360 200" />
                    <path d="M200 40 Q140 60 117 117 Q83 140 40 200" />
                    <path d="M200 40 Q260 60 283 117 Q317 140 360 200" transform="rotate(90 200 200)" />
                    <path d="M200 40 Q140 60 117 117 Q83 140 40 200" transform="rotate(90 200 200)" />
                    <path d="M200 40 Q260 60 283 117 Q317 140 360 200" transform="rotate(180 200 200)" />
                    <path d="M200 40 Q140 60 117 117 Q83 140 40 200" transform="rotate(180 200 200)" />
                    <path d="M200 40 Q260 60 283 117 Q317 140 360 200" transform="rotate(270 200 200)" />
                    <path d="M200 40 Q140 60 117 117 Q83 140 40 200" transform="rotate(270 200 200)" />
                </g>
            </svg>

            <svg class="poster-sinar" viewBox="0 0 300 220" aria-hidden="true" focusable="false">
                <g stroke="currentColor" stroke-linecap="round" fill="none">
                    <path d="M0 220 L120 60" stroke-width="2" />
                    <path d="M18 220 L132 78" stroke-width="1.4" />
                    <path d="M38 220 L146 96" stroke-width="1" />
                    <path d="M0 190 L96 44" stroke-width="1.4" />
                    <path d="M0 158 L72 30" stroke-width="1" />
                </g>
            </svg>

            <div class="poster-kiri">
                <p class="poster-sapaan"><?= esc($heroSapaan) ?></p>

                <h1 class="poster-judul">
                    <span class="poster-baris1"><?= esc($namaBaris1) ?></span>
                    <?php if ($namaBaris2 !== ''): ?>
                    <span class="poster-script"><?= esc($namaBaris2) ?></span>
                    <?php endif; ?>
                    <span class="poster-baris2">Portofolio</span>
                </h1>

                <p class="poster-desk"><?= esc($heroDesk) ?></p>

                <div class="hero-buttons poster-tombol">
                    <a href="#project" class="btn btn-primary">Lihat Project</a>
                    <a href="#contact" class="btn btn-secondary">Hubungi Saya</a>
                </div>

                <ul class="poster-label">
                    <li>
                        <span class="poster-label-kunci">Role</span>
                        <span class="poster-label-nilai"><?= esc($posterPeran) ?></span>
                    </li>
                    <li>
                        <span class="poster-label-kunci">Focus</span>
                        <span class="poster-label-nilai"><?= esc($posterFokus) ?></span>
                    </li>
                    <li>
                        <span class="poster-label-kunci">Time</span>
                        <span class="poster-label-nilai"><?= esc($posterWaktu) ?></span>
                    </li>
                </ul>
            </div>

            <p class="poster-vertikal"><?= esc($posterVertikal) ?></p>

            <div class="poster-figur" id="posterFigur">
                <svg class="poster-reticle" viewBox="0 0 400 400" aria-hidden="true" focusable="false">
                    <g fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="200" cy="200" r="150" />
                        <circle cx="200" cy="200" r="168" stroke-dasharray="10 14" stroke-width="1.2" />
                        <path d="M200 30 L200 70 M200 330 L200 370 M30 200 L70 200 M330 200 L370 200" />
                        <path d="M112 112 L136 136 M288 112 L264 136 M112 288 L136 264 M288 288 L264 264" stroke-width="1.4" />
                    </g>
                </svg>

                <div class="figur-tumpuk" id="figurTumpuk">
                    <img class="figur-topeng" src="foto/topeng.webp" alt="Siluet figur bertopeng" width="900" height="1200" loading="eager">
                    <img class="figur-wajah" id="figurWajah" src="foto/wajah.webp" alt="Foto wajah Erwa Muzaky" width="900" height="1200" loading="eager">
                </div>

                <button type="button" class="figur-tombol" id="figurTombol" aria-label="Tukar tampilan antara topeng dan wajah" aria-pressed="false" hidden></button>
            </div>
        </section>

        <!-- ================= 2. ABOUT ================= -->
        <section id="about">
            <h2>About Me</h2>

            <div class="about-grid reveal">
                <div class="about-bio">
                    <p><?= esc($aboutBuka) ?></p>

                    <?php if ($barisKeahlian): ?>
                    <ul class="about-list">
                        <?php foreach ($barisKeahlian as $baris): ?>
                        <li><?= esc($baris) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>

                    <p><?= esc($aboutOrg) ?></p>

                    <div class="org-grid">
                        <figure class="org-card">
                            <img src="foto/WAKETOS.jpeg" alt="Wakil Ketua OSIS" loading="lazy">
                            <figcaption>Wakil Ketua OSIS</figcaption>
                        </figure>
                        <figure class="org-card">
                            <img src="foto/Paskibraka.jpeg" alt="Paskibraka" loading="lazy">
                            <figcaption>Paskibraka</figcaption>
                        </figure>
                        <figure class="org-card">
                            <img src="foto/Pramuka.jpeg" alt="Pramuka" loading="lazy">
                            <figcaption>Pramuka</figcaption>
                        </figure>
                        <figure class="org-card">
                            <img src="foto/PKS.jpeg" alt="PKS" loading="lazy">
                            <figcaption>PKS</figcaption>
                        </figure>
                    </div>

                    <p><?= esc($aboutTutup) ?></p>
                </div>

                <aside class="info-card">
                    <div class="map-wrapper">
                        <iframe src="<?= esc($aboutMaps) ?>" title="Peta lokasi" width="100%" height="170" loading="lazy" allowfullscreen></iframe>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Lokasi Tempat Tinggal</span>
                        <p class="info-val"><?= esc($aboutLokasi) ?></p>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Jurusan Saat Ini</span>
                        <p class="info-val"><?= esc($aboutJurusan) ?></p>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fokus Keahlian</span>
                        <p class="info-val"><?= esc($aboutFokus) ?></p>
                    </div>
                </aside>
            </div>

            <!-- HARD SKILLS -->
            <div class="skills-block reveal">
                <h3 class="sub-title">Hard Skills</h3>
                <ul class="skills-grid">
                    <?php foreach ($hardSkills as $skill): ?>
                    <li class="skill-card" style="--delay: 0ms">
                        <?php if ($skill['ikon']): ?>
                        <img src="<?= esc($skill['ikon']) ?>" alt="" loading="lazy">
                        <?php endif; ?>
                        <span><?= esc($skill['nama']) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- SOFT SKILLS -->
            <div class="skills-block reveal">
                <h3 class="sub-title">Soft Skills</h3>
                <ul class="soft-skills-grid">
                    <?php foreach ($softSkills as $skill): ?>
                    <li class="soft-card">
                        <h4><?= esc($skill['nama']) ?></h4>
                        <p><?= esc($skill['deskripsi']) ?></p>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- RIWAYAT PENDIDIKAN -->
            <div class="education-block reveal">
                <h3 class="sub-title">Riwayat Pendidikan</h3>
                <ol class="timeline">
                    <?php foreach ($daftarPendidikan as $didik): ?>
                    <li class="timeline-item">
                        <span class="timeline-dot" aria-hidden="true"></span>
                        <div class="timeline-card">
                            <h4><?= esc($didik['nama']) ?></h4>
                            <p class="timeline-sub"><?= esc($didik['jurusan']) ?></p>
                            <span class="timeline-badge"><?= esc($didik['tahun']) ?></span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </section>

        <!-- ================= 3. PROJECT ================= -->
        <section id="project">
            <h2>My Project</h2>
            <ul class="projects-grid">
                <?php foreach ($daftarProject as $i => $proyek): ?>
                <li class="project-card reveal" style="--delay: <?= min($i, 5) * 70 ?>ms">
                    <?php if ($proyek['gambar']): ?>
                    <img src="<?= esc($proyek['gambar']) ?>" alt="Tampilan <?= esc($proyek['judul']) ?>" class="project-img" loading="lazy">
                    <?php endif; ?>
                    <h3><?= esc($proyek['judul']) ?></h3>
                    <p><?= esc($proyek['deskripsi']) ?></p>
                    <?php if ($proyek['tag'] || $proyek['link']): ?>
                    <div class="tech-tags">
                        <?php if ($proyek['tag']): ?>
                        <span class="tech-tag"><?= esc($proyek['tag']) ?></span>
                        <?php endif; ?>
                        <?php if ($proyek['link']): ?>
                        <a href="<?= esc($proyek['link']) ?>" class="project-link" target="_blank" rel="noopener">
                            Kunjungi Project
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8"/></svg>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <!-- ================= 4. CONTACT ================= -->
        <section id="contact">
            <div class="contact-box reveal">
                <h2>Contact Me</h2>
                <p><?= esc($contactPesan) ?></p>

                <a href="mailto:<?= esc($contactEmail) ?>" class="btn btn-primary contact-btn"><?= esc($contactEmail) ?></a>

                <ul class="social-links">
                    <?php foreach ($daftarSosial as $sosial): ?>
                    <li><a href="<?= esc($sosial['url']) ?>" target="_blank" rel="noopener"><?= esc($sosial['nama']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>

    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> <?= esc($footerTeks) ?></p>
    </footer>

    <script src="galeri.js"></script>
    <script src="poster.js"></script>
    <script src="kursor.js"></script>
    <script src="loader.js"></script>

</body>

</html>
