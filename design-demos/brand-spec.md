# Brand spec — Spider-Man (Marvel)

## Status pemakaian
Portofolio pribadi, non-komersial. User memilih tema **sangat literal**. Aset
Marvel dipakai sebagai referensi budaya, bukan klaim afiliasi. Jangan pakai di
materi jualan/klien tanpa izin.

## Aset yang diambil
| Aset | Sumber | Berkas | Catatan |
|---|---|---|---|
| Wordmark "SPIDER-MAN" | Wikimedia Commons, `Spider-Man-Logo.svg` (PD-textlogo, trademark Marvel) | `../foto/spiderman-logo.svg` | Path tunggal, 800x100, bisa diwarnai lewat CSS mask |
| Figur kostum (topeng) | Aset project yang sudah ada | `../foto/topeng.webp` | Latar putih, sudah dipotong ke `../foto/topeng-potong.png` |
| Figur kostum (wajah Erwa) | Aset project yang sudah ada | `../foto/wajah.webp` | Posisi badan sama; sudah dipotong ke `../foto/wajah-potong.png` |
| Spiderman chibi bergelantungan | Aset project yang sudah ada | `../foto/gelantungan-potong.png` | Sudah dipotong dari aset lama; dipakai halaman baru |

## Warna (disampel dari kostum, bukan dikarang)
| Peran | Hex | Asal |
|---|---|---|
| Merah kostum | `#D8232A` | sampel dominan `#cc1818`–`#e42424`, dinaikkan sedikit agar terbaca di layar |
| Merah gelap | `#8F1118` | bayangan kostum |
| Biru komik | `#0B4F8A` | sampel `#0c5490` pada ilustrasi chibi |
| Biru malam | `#111A2E` | panel gelap Spider-Verse |
| Tinta | `#141414` | garis luar komik |
| Kertas koran | `#F3EBDD` | kertas cetak |
| Kertas terang | `#FBF7EF` | bidang baca |
| Neon cyan | `#21D4E8` | offset cetak Spider-Verse |
| Neon magenta | `#F21F7B` | offset cetak Spider-Verse |
| Kuning komik | `#F5C518` | caption box klasik |

Aturan: merah dan biru boleh jadi bidang besar. Neon hanya untuk aksen/offset,
maksimal 10% permukaan. Kuning hanya untuk caption penting.

## Tipografi
Tidak ada font resmi Marvel yang gratis. Padanan yang dipakai:
- Poster/headline: **Anton**, **Archivo Black**, **Bebas Neue**.
- Editorial: **Instrument Serif**, **Source Serif 4**.
- Label teknis: **Space Mono**, **Inter**.
- Tulisan tangan komik: **Permanent Marker** (hanya untuk anotasi, bukan body).

## Aturan visual
- Jaring digambar sebagai SVG/CSS, bukan tekstur tempelan.
- Halftone pakai `radial-gradient`, bukan gambar.
- Offset cetak: dua lapis teks/bingkai bergeser 3-6 px, cyan dan magenta.
- Panel komik boleh miring 1-3 derajat; jangan semua elemen miring.
- Logo wordmark dipakai utuh, tidak dipotong atau diubah hurufnya.

## Yang tidak boleh
- Emoji sebagai ikon.
- Gradien ungu-biru tanpa alasan.
- Klaim "resmi Marvel" atau logo Marvel Studios.
- Wajah orang lain selain Erwa di aset utama.
