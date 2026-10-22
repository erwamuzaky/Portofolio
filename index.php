<?php
require __DIR__ . '/config.php';

try {
    $pdo = db();

    $heroNama = konten($pdo, 'hero', 'nama', 'Erwa Muzaky');
    $heroDesk = konten($pdo, 'hero', 'deskripsi');

    $aboutBuka = konten($pdo, 'about', 'paragraf_pembuka');
    $aboutKeahlian = konten($pdo, 'about', 'keahlian');
    $aboutOrg = konten($pdo, 'about', 'paragraf_organisasi');
    $aboutTutup = konten($pdo, 'about', 'paragraf_penutup');
    $aboutLokasi = konten($pdo, 'about', 'lokasi');
    $aboutJurusan = konten($pdo, 'about', 'jurusan');
    $aboutFokus = konten($pdo, 'about', 'fokus');

    $kutipanKartu = konten($pdo, 'about', 'kutipan_kartu', 'Percayalah pada proses, karena hal besar membutuhkan waktu.');
    $petaWilayah = konten($pdo, 'about', 'peta_wilayah', 'Padang Pariaman') ?: 'Padang Pariaman';
    $petaProvinsi = konten($pdo, 'about', 'peta_provinsi', 'Sumatera Barat');
    $petaLokasi = konten($pdo, 'about', 'peta_lokasi', 'Limau Puruik · Padang Pariaman') ?: 'Limau Puruik · Padang Pariaman';
    $petaStatus = konten($pdo, 'about', 'peta_status', 'Sinyal aktif');
    $petaMarker = konten($pdo, 'about', 'peta_marker', 'REDAKSI');
    $petaPerairan = konten($pdo, 'about', 'peta_perairan', 'SAMUDRA HINDIA');

    $petaTautanCadangan = 'https://www.google.com/maps/search/?api=1&query=Limau%20Puruik%2C%20V%20Koto%20Timur%2C%20Padang%20Pariaman';
    $petaTautan = konten($pdo, 'about', 'maps_src', $petaTautanCadangan);
    if ($petaTautan === '' || !preg_match('#^https?://#i', $petaTautan)) {
        $petaTautan = $petaTautanCadangan;
    }

    $contactEmail = konten($pdo, 'contact', 'email');
    $contactPesan = konten($pdo, 'contact', 'pesan');
    $footerTeks = konten($pdo, 'footer', 'teks');

    $daftarSosial = $pdo->query('SELECT nama, url FROM sosial ORDER BY urutan, id')->fetchAll();
    $daftarProject = $pdo->query('SELECT * FROM project ORDER BY urutan, id')->fetchAll();
    $daftarSkill = $pdo->query('SELECT nama, ikon, kategori, deskripsi FROM skill ORDER BY urutan, id')->fetchAll();
    $daftarPendidikan = $pdo->query('SELECT nama, jurusan, tahun FROM pendidikan ORDER BY urutan, id')->fetchAll();
    $daftarPengalaman = $pdo->query('SELECT nama, peran, tahun, deskripsi FROM pengalaman ORDER BY urutan, id')->fetchAll();
    $adaCv = is_file(__DIR__ . '/foto/cv.pdf');

    $hardSkills = array_values(array_filter($daftarSkill, fn($s) => $s['kategori'] === 'hard'));
    $softSkills = array_values(array_filter($daftarSkill, fn($s) => $s['kategori'] === 'soft'));

    $tahunIni = date('Y');
    $namaDepan = explode(' ', trim($heroNama))[0] ?? $heroNama;
    $sekolah = $daftarPendidikan[0]['nama'] ?? 'SMKN 3 Pariaman';
    $tahunSekolah = $daftarPendidikan[0]['tahun'] ?? '';
    $rentangSekolah = trim(str_replace('(Sekarang)', '', $tahunSekolah));

    $akronimJurusan = 'RPL';
    if (preg_match('/\(([^)]+)\)/', $aboutJurusan, $cocokAkronim)) {
        $akronimJurusan = trim($cocokAkronim[1]);
    }

    $bagianLokasi = array_values(array_filter(array_map('trim', explode(',', $aboutLokasi))));
    if (count($bagianLokasi) >= 2) {
        $lokasiSingkat = implode(', ', array_slice($bagianLokasi, -2));
    } else {
        $lokasiSingkat = $bagianLokasi[0] ?? ($aboutLokasi !== '' ? $aboutLokasi : 'Padang Pariaman, Sumatera Barat');
    }

    // Foto organisasi dipetakan dari nama pengalaman karena tabel tidak menyimpan gambar.
    $petaFotoOrganisasi = [
        'waketos' => 'foto/WAKETOS.jpeg',
        'osis' => 'foto/WAKETOS.jpeg',
        'paskibra' => 'foto/Paskibraka.jpeg',
        'pramuka' => 'foto/Pramuka.jpeg',
        'pks' => 'foto/PKS.jpeg',
    ];

    $fotoPengalaman = static function (string $nama) use ($petaFotoOrganisasi): ?string {
        $huruf = strtolower($nama);
        foreach ($petaFotoOrganisasi as $kunci => $jalur) {
            if (str_contains($huruf, $kunci)) {
                return $jalur;
            }
        }
        return null;
    };

    $polaroidOrganisasi = [];
    foreach ($daftarPengalaman as $org) {
        $fotoOrg = $fotoPengalaman($org['nama']);
        if ($fotoOrg !== null) {
            $polaroidOrganisasi[] = [
                'nama' => $org['nama'],
                'peran' => $org['peran'],
                'foto' => $fotoOrg,
            ];
        }
    }

    // Baris keahlian pada bagian tentang: "Label: penjelasan" dipisah dengan tanda |.
    $barisKeahlian = [];
    foreach (array_filter(array_map('trim', explode('|', $aboutKeahlian))) as $baris) {
        $posisi = strpos($baris, ':');
        if ($posisi === false) {
            $barisKeahlian[] = ['label' => $baris, 'teks' => ''];
        } else {
            $barisKeahlian[] = [
                'label' => trim(substr($baris, 0, $posisi)),
                'teks' => trim(substr($baris, $posisi + 1)),
            ];
        }
    }

    // Kalimat terakhir dari paragraf penutup dipakai sebagai kutipan.
    $kalimatTutup = preg_split('/(?<=[.!?])\s+/', trim($aboutTutup), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    if (count($kalimatTutup) > 1) {
        $kutipan = array_pop($kalimatTutup);
        $paragrafTutup = implode(' ', $kalimatTutup);
    } else {
        $kutipan = $kalimatTutup[0] ?? '';
        $paragrafTutup = '';
    }

    $penandaSkill = [
        'HTML' => 'HT',
        'CSS' => 'CS',
        'C++' => 'C+',
        'JAVA' => 'JV',
        'PHP' => 'PH',
        'LARAVEL' => 'LV',
        'MYSQL' => 'MY',
        'FLUTTER' => 'FL',
        'JAVASCRIPT' => 'JS',
        'TAILWIND' => 'TW',
        'BOOTSTRAP' => 'BS',
        'GIT' => 'GT',
        'FIGMA' => 'FG',
    ];

    $labelSkill = [
        'HTML' => 'Markup',
        'CSS' => 'Styling',
        'C++' => 'Logic',
        'JAVA' => 'OOP',
        'PHP' => 'Backend',
        'LARAVEL' => 'Framework',
        'MYSQL' => 'Database',
        'FLUTTER' => 'Mobile',
        'JAVASCRIPT' => 'Script',
        'TAILWIND' => 'Utility CSS',
        'BOOTSTRAP' => 'Framework',
        'GIT' => 'Versioning',
        'FIGMA' => 'Design',
    ];
} catch (PDOException $e) {
    http_response_code(500);
    exit('<p style="font-family:sans-serif;padding:2rem">Database belum bisa dihubungi. Pastikan MySQL jalan lalu impor <code>setup.sql</code>.</p>');
}

$skema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$urlSitus = $skema . '://' . $host . strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$gambarBagikan = $skema . '://' . $host . '/foto/foto.webp';
$metaDeskripsi = trim(preg_replace('/\s+/', ' ', $heroDesk) ?? '');
if (mb_strlen($metaDeskripsi) > 158) {
    $metaDeskripsi = mb_substr($metaDeskripsi, 0, 155) . '...';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($heroNama) ?> — The Daily Bugle</title>
    <meta name="description" content="<?= esc($metaDeskripsi) ?>">
    <meta name="author" content="<?= esc($heroNama) ?>">
    <link rel="canonical" href="<?= esc($urlSitus) ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= esc($heroNama) ?> — The Daily Bugle">
    <meta property="og:description" content="<?= esc($metaDeskripsi) ?>">
    <meta property="og:image" content="<?= esc($gambarBagikan) ?>">
    <meta property="og:url" content="<?= esc($urlSitus) ?>">
    <meta property="og:locale" content="id_ID">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= esc($heroNama) ?> — The Daily Bugle">
    <meta name="twitter:description" content="<?= esc($metaDeskripsi) ?>">
    <meta name="twitter:image" content="<?= esc($gambarBagikan) ?>">
    <link rel="preload" as="image" href="foto/topeng-potong.png" fetchpriority="high">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Anton&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;0,8..60,700;1,8..60,400&family=Space+Mono:wght@400;700&family=UnifrakturCook:wght@700&display=swap">
    <link rel="stylesheet" href="style-beranda.css">
    <link rel="icon"
        href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='15' fill='%23f3ebdd'/%3E%3Ccircle cx='16' cy='16' r='13' fill='none' stroke='%23141414' stroke-width='2'/%3E%3Cpath d='M16 3v26M3 16h26M7 7l18 18M25 7L7 25' stroke='%230b4f8a' stroke-width='1' opacity='.55'/%3E%3Ccircle cx='16' cy='16' r='4' fill='%23d8232a'/%3E%3C/svg%3E">
</head>

<body>
    <a class="skip-link" href="#main">Lewati ke konten utama</a>

    <div class="edition-bar">
        <div class="wrap">
            <span>Edisi Digital · <?= $tahunIni ?></span>
            <span>Vol. 01 — No. 03</span>
            <span><?= esc($lokasiSingkat) ?></span>
            <span>Harga: Gratis</span>
        </div>
    </div>

    <header class="masthead">
        <div class="wrap">
            <div class="masthead-top">
                <div class="masthead-side">
                    <strong>Cuaca hari ini</strong>
                    Cerah berawan di Pariaman.<br>
                    Bawa payung, siapa tahu hujan.
                </div>
                <div class="masthead-title">
                    <div class="masthead-name">The Daily Bugle</div>
                    <p>Portofolio Edisi Khusus · <?= esc($heroNama) ?></p>
                </div>
                <div class="masthead-side" style="text-align:right">
                    <strong>Fotografer</strong>
                    <?= esc($heroNama) ?><br>
                    <span class="masthead-kicker">Edisi Pemula</span>
                </div>
            </div>
            <div class="masthead-rule">
                <span>“Kabar sekolah, terbit kalau ingat”</span>
                <div class="masthead-logo" aria-hidden="true"></div>
                <span><?= esc($sekolah) ?> · <?= esc($akronimJurusan) ?></span>
            </div>
        </div>
    </header>

    <nav class="main-nav" id="nav" aria-label="Navigasi utama">
        <div class="wrap">
            <a class="nav-brand" href="#beranda">The Daily Bugle / <?= esc($namaDepan) ?></a>
            <button class="nav-toggle" id="navToggle" type="button" aria-expanded="false"
                aria-controls="navLinks">Menu</button>
            <ul class="nav-links" id="navLinks">
                <li><a href="#beranda" class="active">Beranda</a></li>
                <li><a href="#tentang">Tentang</a></li>
                <li><a href="#keahlian">Keahlian</a></li>
                <li><a href="#pendidikan">Pendidikan</a></li>
                <li><a href="#pengalaman">Pengalaman</a></li>
                <li><a href="#project">Project</a></li>
                <li><a href="#kontak">Kontak</a></li>
            </ul>
        </div>
    </nav>

    <main id="main">
        <!-- ================= 1. BERANDA ================= -->
        <section class="hero wrap" id="beranda" aria-labelledby="hero-title">
            <svg class="web web--tl" viewBox="0 0 300 300" aria-hidden="true" focusable="false">
                <g fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round">
                    <path pathLength="1" d="M0 0 L300 0"/>
                    <path pathLength="1" d="M0 0 L260 80"/>
                    <path pathLength="1" d="M0 0 L200 150"/>
                    <path pathLength="1" d="M0 0 L120 220"/>
                    <path pathLength="1" d="M0 0 L0 300"/>
                    <path pathLength="1" d="M0 72 Q44 44 72 0"/>
                    <path pathLength="1" d="M0 140 Q86 86 140 0"/>
                    <path pathLength="1" d="M0 208 Q128 128 208 0"/>
                    <path pathLength="1" d="M0 276 Q170 170 276 0"/>
                </g>
            </svg>
            <svg class="web web--br" viewBox="0 0 300 300" aria-hidden="true" focusable="false">
                <g fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round">
                    <path pathLength="1" d="M0 0 L300 0"/>
                    <path pathLength="1" d="M0 0 L260 80"/>
                    <path pathLength="1" d="M0 0 L200 150"/>
                    <path pathLength="1" d="M0 0 L120 220"/>
                    <path pathLength="1" d="M0 0 L0 300"/>
                    <path pathLength="1" d="M0 72 Q44 44 72 0"/>
                    <path pathLength="1" d="M0 140 Q86 86 140 0"/>
                    <path pathLength="1" d="M0 208 Q128 128 208 0"/>
                    <path pathLength="1" d="M0 276 Q170 170 276 0"/>
                </g>
            </svg>
            <div class="hero-kicker mono">
                <span>Laporan Utama · Halaman 01</span>
                <span>Spider-Man Menyetujui</span>
            </div>
            <div class="hero-grid">
                <div class="hero-copy">
                    <h1 id="hero-title">
                        <span>Friendly</span>
                        <span>Neighborhood</span>
                        <span class="red">Developer</span>
                    </h1>
                    <p class="hero-dek"><?= esc($heroDesk) ?></p>
                    <div class="byline">
                        <span>Oleh <strong><?= esc($heroNama) ?></strong></span>
                        <span><?= esc($sekolah) ?></span>
                        <span><?= esc($akronimJurusan) ?><?= $rentangSekolah !== '' ? ' · ' . esc($rentangSekolah) : '' ?></span>
                    </div>
                    <div class="hero-actions">
                        <a class="btn btn--web" href="#project">Lihat Project
                            <span class="thwip" aria-hidden="true">THWIP!</span>
                        </a>
                        <a class="btn btn-outline" href="#kontak">Kirim Pesan</a>
                        <?php if ($adaCv): ?>
                            <a class="btn btn-outline" href="foto/cv.pdf" download>Unduh CV</a>
                        <?php endif; ?>
                    </div>
                </div>

                <figure class="hero-figure">
                    <div class="halftone" aria-hidden="true"></div>
                    <div class="blue-block" aria-hidden="true"></div>
                    <div class="face-toggle" role="group" aria-label="Pilih tampilan Spiderman atau Erwa Parker">
                        <button type="button" data-face="topeng" aria-pressed="true">Spiderman</button>
                        <button type="button" data-face="wajah" aria-pressed="false">Erwa Parker</button>
                    </div>
                    <div class="figure-stack">
                        <img id="heroFace" class="is-hidden" src="foto/wajah-potong.png"
                            alt="Erwa Muzaky sebagai Erwa Parker tanpa topeng" width="454" height="1110" loading="lazy"
                            decoding="async">
                        <img id="heroMask" src="foto/topeng-potong.png"
                            alt="Erwa Muzaky sebagai Spiderman dengan topeng" width="454" height="1110"
                            fetchpriority="high" decoding="async">
                    </div>
                    <figcaption id="heroCaption" data-nama="<?= esc($heroNama) ?>" data-tahun="<?= $tahunIni ?>">Foto:
                        <?= esc($heroNama) ?> / <?= $tahunIni ?> · Figur: Spiderman
                    </figcaption>
                    <div class="stamp" aria-hidden="true">Spider-Man<br>Menyetujui<br>★★★★★</div>
                </figure>
            </div>

            <div class="stats" aria-label="Ringkasan portofolio">
                <div><strong data-count-to="<?= count($daftarProject) ?>"><?= count($daftarProject) ?></strong><span>Project<br>terdokumentasi</span></div>
                <div><strong data-count-to="<?= count($daftarPengalaman) ?>"><?= count($daftarPengalaman) ?></strong><span>Organisasi<br>yang diikuti</span></div>
                <div><strong data-count-to="<?= count($daftarSkill) ?>"><?= count($daftarSkill) ?></strong><span>Skill<br>terlatih</span></div>
            </div>
        </section>

        <!-- ================= 2. TENTANG ================= -->
        <section class="section wrap reveal" id="tentang" aria-labelledby="tentang-title">
            <div class="section-head">
                <div class="kicker">Halaman 02<br>Profil</div>
                <h2 id="tentang-title">Di Balik Topeng</h2>
                <div class="page-no">Bagian<strong>02</strong></div>
            </div>

            <div class="about-grid">
                <div class="about-copy">
                    <?php if ($aboutBuka !== ''): ?>
                        <p class="dropcap"><?= esc($aboutBuka) ?></p>
                    <?php endif; ?>

                    <?php if ($barisKeahlian): ?>
                        <ul class="skill-lines">
                            <?php foreach ($barisKeahlian as $baris): ?>
                                <li>
                                    <strong><?= esc($baris['label']) ?></strong>
                                    <span><?= esc($baris['teks']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if ($aboutOrg !== ''): ?>
                        <p><?= esc($aboutOrg) ?></p>
                    <?php endif; ?>

                    <?php if ($paragrafTutup !== ''): ?>
                        <p><?= esc($paragrafTutup) ?></p>
                    <?php endif; ?>

                    <?php if ($kutipan !== ''): ?>
                        <blockquote class="pullquote">“<?= esc($kutipan) ?>”<cite>— <?= esc($heroNama) ?></cite>
                        </blockquote>
                    <?php endif; ?>

                    <?php if ($polaroidOrganisasi): ?>
                        <div class="polaroids" aria-label="Foto kegiatan organisasi">
                            <?php foreach (array_slice($polaroidOrganisasi, 0, 4) as $foto): ?>
                                <figure class="polaroid">
                                    <img <?= atribut_gambar($foto['foto'], '(max-width: 560px) calc(50vw - 24px), 190px') ?>
                                        alt="Kegiatan <?= esc($foto['nama']) ?>" loading="lazy" decoding="async">
                                    <figcaption><?= esc($foto['nama']) ?><br><?= esc($foto['peran']) ?></figcaption>
                                </figure>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <aside class="side-card" aria-label="Kartu identitas">
                    <header>Kartu Identitas / 02</header>
                    <dl>
                        <?php if ($heroNama !== ''): ?>
                            <dt>Nama</dt>
                            <dd><?= esc($heroNama) ?></dd>
                        <?php endif; ?>
                        <?php if ($sekolah !== ''): ?>
                            <dt>Sekolah</dt>
                            <dd><?= esc($sekolah) ?></dd>
                        <?php endif; ?>
                        <?php if ($aboutJurusan !== ''): ?>
                            <dt>Jurusan</dt>
                            <dd><?= esc($aboutJurusan) ?></dd>
                        <?php endif; ?>
                        <?php if ($aboutFokus !== ''): ?>
                            <dt>Fokus</dt>
                            <dd><?= esc($aboutFokus) ?></dd>
                        <?php endif; ?>
                        <?php if ($aboutLokasi !== ''): ?>
                            <dt>Lokasi</dt>
                            <dd><?= esc($aboutLokasi) ?></dd>
                        <?php endif; ?>
                        <?php if ($tahunSekolah !== ''): ?>
                            <dt>Status</dt>
                            <dd><?= esc($tahunSekolah) ?></dd>
                        <?php endif; ?>
                    </dl>
                    <div class="side-sticker">
                        <a class="side-map"
                            href="<?= esc($petaTautan) ?>"
                            target="_blank" rel="noopener noreferrer"
                            aria-label="Buka lokasi <?= esc($petaLokasi) ?> di Google Maps (tab baru)">
                            <div class="side-map-head">
                                <span>Peta <?= esc($petaWilayah) ?></span>
                                <?php if ($petaStatus !== ''): ?>
                                    <span class="side-map-live"><?= esc($petaStatus) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="side-map-frame">
                            <svg viewBox="0 0 320 180" role="img" aria-labelledby="peta-redaksi-title">
                                    <title id="peta-redaksi-title">Ilustrasi peta <?= esc($petaWilayah) ?> dengan penanda lokasi <?= esc($petaMarker) ?></title>
                                    <defs>
                                        <pattern id="peta-grid" width="20" height="20" patternUnits="userSpaceOnUse">
                                            <path d="M20 0H0V20" fill="none" stroke="#23414d" stroke-width=".6"/>
                                        </pattern>
                                        <linearGradient id="peta-laut" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0" stop-color="#0b2230"/>
                                            <stop offset="1" stop-color="#081720"/>
                                        </linearGradient>
                                    </defs>
                                    <rect width="320" height="180" fill="#0b1720"/>
                                    <rect width="320" height="180" fill="url(#peta-grid)" opacity=".5"/>
                                    <path d="M0 150 Q52 138 104 154 T212 152 T320 140 L320 180 L0 180Z"
                                        fill="url(#peta-laut)" stroke="#2b5563" stroke-width="1"/>
                                    <g fill="#17313b" stroke="#244752" stroke-width=".8">
                                        <path d="M18 46h42v26H18z"/>
                                        <path d="M72 34h34v40H72z"/>
                                        <path d="M118 26h46v30h-46z"/>
                                        <path d="M176 40h38v28h-38z"/>
                                        <path d="M226 16h40v34h-40z"/>
                                        <path d="M30 90h52v40H30z"/>
                                        <path d="M96 84h42v34H96z"/>
                                        <path d="M148 100h48v28h-48z"/>
                                        <path d="M210 82h42v40h-42z"/>
                                        <path d="M264 66h38v44h-38z"/>
                                    </g>
                                    <g fill="none" stroke="#5b8894" stroke-width="2.2" opacity=".8">
                                        <path d="M-8 72 328 36"/>
                                        <path d="M-8 120 328 90"/>
                                        <path d="M-8 156 328 134"/>
                                        <path d="M64-8 82 188"/>
                                        <path d="M152-8 166 188"/>
                                        <path d="M244-8 252 188"/>
                                    </g>
                                    <g fill="none" stroke="#9fc0c7" stroke-width=".7" opacity=".42">
                                        <path d="M18 88h284M18 106h284M96 34v116M196 24v126"/>
                                    </g>
                                    <path class="map-route" pathLength="1" d="M28 148 74 126 82 98 132 78 168 94 212 72 260 38"
                                        fill="none" stroke="#e03138" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle class="map-ping" cx="260" cy="38" r="10" fill="none" stroke="#e03138" stroke-width="1.4"/>
                                    <g transform="translate(260 38)">
                                        <g class="map-pin">
                                            <circle r="10" fill="#f3ebdd" stroke="#e03138" stroke-width="2"/>
                                            <g fill="#d8232a">
                                                <ellipse cx="0" cy="2" rx="2.2" ry="3"/>
                                                <circle cx="0" cy="-2.4" r="1.7"/>
                                            </g>
                                            <g stroke="#d8232a" stroke-width="1.1" stroke-linecap="round">
                                                <path d="M-2-1-5-3M2-1 5-3M-2.5 1-6 0M2.5 1 6 0M-2 3-5 6M2 3 5 6"/>
                                            </g>
                                        </g>
                                    </g>
                                    <text x="18" y="26" fill="#dce9ec" font-family="Space Mono, monospace" font-size="8"
                                        letter-spacing="1.4"><?= esc(strtoupper($petaWilayah)) ?></text>
                                    <text x="18" y="36" fill="#7fa3ac" font-family="Space Mono, monospace" font-size="6"
                                        letter-spacing=".8"><?= esc(strtoupper($petaProvinsi)) ?></text>
                                    <text x="260" y="58" text-anchor="middle" fill="#ff8b8f"
                                        font-family="Space Mono, monospace" font-size="6" letter-spacing=".8"><?= esc($petaMarker) ?></text>
                                    <text x="20" y="170" fill="#6d97a2" font-family="Space Mono, monospace" font-size="5.5"
                                        letter-spacing=".8"><?= esc($petaPerairan) ?></text>
                                    <g transform="translate(294 28)">
                                        <path d="M0-7 4 5 0 2-4 5Z" fill="#dce9ec"/>
                                        <text x="0" y="14" text-anchor="middle" fill="#7fa3ac"
                                            font-family="Space Mono, monospace" font-size="6">N</text>
                                    </g>
                                </svg>
                            </div>
                            <div class="side-map-foot">
                                <span><?= esc($petaLokasi) ?></span>
                                <span class="side-map-open">Buka peta ↗</span>
                            </div>
                        </a>
                        <?php if ($kutipanKartu !== ''): ?>
                            <p>“<?= esc($kutipanKartu) ?>”</p>
                        <?php endif; ?>
                    </div>
                </aside>
            </div>
        </section>

        <!-- ================= 3. KEAHLIAN ================= -->
        <?php if ($hardSkills || $softSkills): ?>
            <section class="section wrap reveal" id="keahlian" aria-labelledby="keahlian-title">
                <div class="section-head">
                    <div class="kicker">Halaman 03<br>Kekuatan</div>
                    <h2 id="keahlian-title">Kekuatan &amp; Jaring</h2>
                    <div class="page-no">Bagian<strong>03</strong></div>
                </div>

                <div class="skills-top">
                    <?php if ($hardSkills): ?>
                        <div>
                            <div class="soft-head tanpa-jarak">
                                <h3>Hard Skills</h3>
                                <span class="mono"><?= count($hardSkills) ?> kemampuan teknis</span>
                            </div>
                            <div class="power-grid">
                                <?php foreach ($hardSkills as $index => $skill): ?>
                                    <?php
                                    $kunciSkill = strtoupper(preg_replace('/[^A-Za-z0-9+]/', '', $skill['nama']) ?? '');
                                    $penanda = $penandaSkill[$kunciSkill] ?? ($kunciSkill !== '' ? substr($kunciSkill, 0, 2) : 'SK');
                                    $label = $labelSkill[$kunciSkill] ?? 'Skill';
                                    ?>
                                    <div class="power">
                                        <span class="power-mark" aria-hidden="true"><?= esc($penanda) ?></span>
                                        <strong><?= esc($skill['nama']) ?></strong>
                                        <small><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?> /
                                            <?= esc($label) ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($softSkills): ?>
                        <div>
                            <div class="soft-head">
                                <h3>Soft Skills</h3>
                                <span class="mono"><?= count($softSkills) ?> kekuatan tim</span>
                            </div>
                            <div class="soft-grid">
                                <?php foreach ($softSkills as $index => $skill): ?>
                                    <article class="soft">
                                        <div class="num"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></div>
                                        <div>
                                            <h4><?= esc($skill['nama']) ?></h4>
                                            <p><?= esc($skill['deskripsi']) ?></p>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- ================= 4. PENDIDIKAN ================= -->
        <?php if ($daftarPendidikan): ?>
            <section class="section wrap reveal" id="pendidikan" aria-labelledby="pendidikan-title">
                <div class="section-head">
                    <div class="kicker">Halaman 04<br>Riwayat</div>
                    <h2 id="pendidikan-title">Jejak Pendidikan</h2>
                    <div class="page-no">Bagian<strong>04</strong></div>
                </div>

                <div class="edu-list">
                    <?php foreach ($daftarPendidikan as $didik): ?>
                        <article class="edu">
                            <div class="year"><?= esc($didik['tahun']) ?></div>
                            <div>
                                <h3><?= esc($didik['nama']) ?></h3>
                                <p><?= esc($didik['jurusan']) ?></p>
                            </div>
                            <div class="level"><?= str_contains($didik['tahun'], 'Sekarang') ? 'Sekarang' : 'Selesai' ?></div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- ================= 5. PENGALAMAN ================= -->
        <?php if ($daftarPengalaman): ?>
            <section class="section wrap reveal" id="pengalaman" aria-labelledby="pengalaman-title">
                <div class="section-head">
                    <div class="kicker">Halaman 05<br>Liputan</div>
                    <h2 id="pengalaman-title">Liputan Lapangan</h2>
                    <div class="page-no">Bagian<strong>05</strong></div>
                </div>

                <div class="exp-grid">
                    <?php foreach ($daftarPengalaman as $kerja): ?>
                        <?php $fotoKerja = $fotoPengalaman($kerja['nama']); ?>
                        <article class="exp<?= $fotoKerja === null ? ' tanpa-gambar' : '' ?>">
                            <?php if ($fotoKerja !== null): ?>
                                <div class="exp-img">
                                    <img <?= atribut_gambar($fotoKerja, '(max-width: 560px) calc(100vw - 32px), (max-width: 1080px) calc(100vw - 48px), 260px') ?> alt="Kegiatan <?= esc($kerja['nama']) ?>" loading="lazy"
                                        decoding="async">
                                </div>
                            <?php endif; ?>
                            <div class="exp-body">
                                <span class="org"><?= esc($kerja['nama']) ?></span>
                                <h3><?= esc($kerja['peran']) ?></h3>
                                <p><?= esc($kerja['deskripsi']) ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- ================= 6. PROJECT ================= -->
        <section class="section wrap reveal" id="project" aria-labelledby="project-title">
            <div class="section-head">
                <div class="kicker">Halaman 06<br>Edisi Terbit</div>
                <h2 id="project-title">Project Pilihan</h2>
                <div class="page-no">Bagian<strong>06</strong></div>
            </div>

            <?php if ($daftarProject): ?>
                <div class="clip-grid">
                    <?php foreach ($daftarProject as $index => $proyek): ?>
                        <?php
                        $tags = array_values(array_filter(array_map('trim', explode(',', $proyek['tag']))));
                        $jenisProject = 'Web';
                        foreach ($tags as $tag) {
                            if (str_contains(strtolower($tag), 'flutter')) {
                                $jenisProject = 'Mobile';
                                break;
                            }
                        }

                        $ringkasan = trim(preg_replace('/\s+/', ' ', $proyek['deskripsi']) ?? '');
                        if (mb_strlen($ringkasan) > 150) {
                            $ringkasan = mb_substr($ringkasan, 0, 147) . '...';
                        }

                        $sumberDetail = trim($proyek['detail'] !== '' ? $proyek['detail'] : $proyek['deskripsi']);
                        $paragrafDetail = $sumberDetail === '' ? [] : (preg_split('/\R{2,}/', $sumberDetail) ?: []);

                        $nomorProject = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
                        $idDialog = 'project-' . (int) $proyek['id'];
                        $metaProject = array_values(array_filter([
                            $proyek['peran'],
                            $proyek['tahun'],
                            implode(', ', $tags),
                        ], fn($nilai) => $nilai !== ''));
                        ?>
                        <article class="clip">
                            <?php if ($proyek['gambar']): ?>
                                <div class="clip-img">
                                    <img <?= atribut_gambar($proyek['gambar'], '(max-width: 860px) 42vw, 360px') ?>
                                        alt="Tampilan <?= esc($proyek['judul']) ?>" loading="lazy" decoding="async">
                                </div>
                            <?php endif; ?>
                            <div class="clip-body">
                                <span class="clip-label">Project <?= $nomorProject ?> / <?= $jenisProject ?></span>
                                <h3><?= esc($proyek['judul']) ?></h3>
                                <?php if ($metaProject): ?>
                                    <p class="clip-meta"><?= esc(implode(' · ', $metaProject)) ?></p>
                                <?php endif; ?>
                                <p class="excerpt"><?= esc($ringkasan) ?></p>
                                <button class="link-btn" type="button" data-dialog="<?= esc($idDialog) ?>">Baca Edisi →</button>
                            </div>

                            <dialog id="<?= esc($idDialog) ?>" aria-labelledby="judul-<?= esc($idDialog) ?>">
                                <div class="dialog-inner">
                                    <div class="dialog-head">
                                        <div>
                                            <span class="mono">Project <?= $nomorProject ?> / <?= $jenisProject ?></span>
                                            <h3 id="judul-<?= esc($idDialog) ?>"><?= esc($proyek['judul']) ?></h3>
                                        </div>
                                        <button class="dialog-close" type="button" data-close
                                            aria-label="Tutup detail">×</button>
                                    </div>

                                    <?php if ($proyek['gambar']): ?>
                                        <img class="dialog-img" <?= atribut_gambar($proyek['gambar'], '(max-width: 720px) calc(100vw - 48px), 560px') ?> alt="Tampilan <?= esc($proyek['judul']) ?>" loading="lazy"
                                            decoding="async">
                                    <?php endif; ?>

                                    <?php if ($tags): ?>
                                        <div class="dialog-meta">
                                            <?php foreach ($tags as $tag): ?>
                                                <span><?= esc($tag) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="dialog-teks">
                                        <?php foreach ($paragrafDetail as $paragraf): ?>
                                            <?php if (trim($paragraf) === '') {
                                                continue;
                                            } ?>
                                            <p><?= esc(trim($paragraf)) ?></p>
                                        <?php endforeach; ?>
                                    </div>

                                    <?php if ($proyek['hasil']): ?>
                                        <p class="dialog-result"><strong>Hasil:</strong> <?= esc($proyek['hasil']) ?></p>
                                    <?php endif; ?>

                                    <?php if ($proyek['link']): ?>
                                        <p><a class="btn" href="<?= esc($proyek['link']) ?>" target="_blank" rel="noopener">Kunjungi
                                                Project</a></p>
                                    <?php endif; ?>
                                </div>
                            </dialog>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="kosong">Belum ada project yang ditambahkan.</p>
            <?php endif; ?>
        </section>

        <!-- ================= 7. KONTAK ================= -->
        <section class="section wrap reveal contact" id="kontak" aria-labelledby="kontak-title">
            <div class="section-head">
                <div class="kicker">Halaman 07<br>Kontak</div>
                <h2 id="kontak-title">Kirim Surat</h2>
                <div class="page-no">Bagian<strong>07</strong></div>
            </div>

            <img class="contact-sticker" src="foto/gelantungan-potong.png" alt="" width="146" height="271"
                loading="lazy" decoding="async">

            <div class="contact-grid">
                <div>
                    <p class="contact-besar">Ke Redaksi<span>Daily Bugle</span></p>
                    <p class="lead"><?= esc($contactPesan) ?></p>
                </div>

                <div class="contact-card">
                    <span class="label">Surel Resmi / Email</span>
                    <a class="email" href="mailto:<?= esc($contactEmail) ?>"><?= esc($contactEmail) ?></a>
                    <div class="copy-row">
                        <button class="btn btn-outline" type="button" id="copyEmail"
                            data-email="<?= esc($contactEmail) ?>">Salin Email</button>
                        <span class="copy-status" id="copyStatus" role="status" aria-live="polite"></span>
                    </div>
                    <?php if ($daftarSosial): ?>
                        <ul class="socials">
                            <?php foreach ($daftarSosial as $sosial): ?>
                                <li>
                                    <span><?= esc($sosial['nama']) ?></span>
                                    <a href="<?= esc($sosial['url']) ?>" target="_blank" rel="noopener noreferrer">Buka ↗</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="wrap">
            <div class="footer-grid">
                <div>
                    <h2>The Daily Bugle</h2>
                    <p><?= esc($footerTeks) ?></p>
                </div>
                <div class="mono">
                    <strong>Edisi</strong><br>
                    Vol. 01 — No. 03<br>
                    Digital · <?= $tahunIni ?><br>
                    <?= esc($lokasiSingkat) ?>
                </div>
                <div class="mono">
                    <strong>Navigasi</strong><br>
                    <a href="#beranda">Beranda</a><br>
                    <a href="#project">Project</a><br>
                    <a href="#kontak">Kontak</a>
                </div>
            </div>
            <div class="footer-bottom">
                <span>© <?= $tahunIni ?> <?= esc($heroNama) ?>. Semua hak cipta dilindungi.</span>
                <span>Dicetak di atas kertas digital.</span>
            </div>
        </div>
    </footer>

    <button class="to-top" id="toTop" type="button" aria-label="Kembali ke atas">↑</button>

    <script src="beranda.js"></script>
</body>

</html>
