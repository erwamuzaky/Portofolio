"""Cek cepat semua halaman portofolio dari sisi server.

Menyalakan server bawaan PHP, lalu memeriksa tiap halaman penting:
status HTTP, penanda konten yang harus ada, dan ada atau tidaknya pesan error PHP.

Jalankan dari folder project:
    python tools/cek_halaman.py
    python tools/cek_halaman.py --port 8765
    python tools/cek_halaman.py --tanpa-server --base-url https://contoh.my.id
"""

from __future__ import annotations

import argparse
import shutil
import socket
import subprocess
import sys
import time
import urllib.error
import urllib.request
from dataclasses import dataclass, field
from pathlib import Path

AKAR = Path(__file__).resolve().parent.parent

KONFIGURASI_KELUAR = getattr(sys.stdout, "reconfigure", None)
if callable(KONFIGURASI_KELUAR):
    KONFIGURASI_KELUAR(encoding="utf-8", errors="replace")

TANDA_ERROR_PHP = (
    "Fatal error",
    "Parse error",
    "Warning:",
    "Notice:",
    "Deprecated:",
    "Uncaught",
    "Database belum bisa dihubungi",
)

KETERANGAN_ERROR = "halaman memuat pesan error PHP"
KETERANGAN_DB = "halaman memuat pesan 'Database belum bisa dihubungi' (MySQL belum jalan)"


@dataclass
class Cek:
    """Satu halaman yang harus diuji."""

    nama: str
    jalur: str
    status_harap: tuple[int, ...] = (200,)
    penanda: tuple[str, ...] = ()
    akhir_url_harap: str | None = None
    tipe_konten_harap: str | None = None


@dataclass
class Hasil:
    """Hasil pengujian satu halaman."""

    nama: str
    lulus: bool
    catatan: list[str] = field(default_factory=list)


CEK_SEMUA = (
    Cek(
        nama="Beranda",
        jalur="/",
        penanda=("The Daily Bugle", "Baca Edisi", "style-beranda.css", "beranda.js"),
    ),
    Cek(
        nama="Dashboard admin",
        jalur="/admin/",
        penanda=("Masuk",),
        akhir_url_harap="login.php",
    ),
    Cek(
        nama="Login admin",
        jalur="/admin/login.php",
        penanda=('name="csrf"', "Masuk"),
    ),
    Cek(
        nama="Logout admin",
        jalur="/admin/logout.php",
        akhir_url_harap="login.php",
    ),
    Cek(
        nama="Gambar hero",
        jalur="/foto/foto.webp",
        tipe_konten_harap="image/",
    ),
)


def cari_php(nilai: str | None) -> str | None:
    """Temukan binary PHP dari pilihan pengguna, PATH, atau lokasi XAMPP."""
    if nilai:
        return nilai if Path(nilai).is_file() else None

    for nama in ("php", "php.exe"):
        ditemukan = shutil.which(nama)
        if ditemukan:
            return ditemukan

    for calon in (
        Path(r"C:\xampp\php\php.exe"),
        Path(r"C:\laragon\bin\php\php-8.2.12-Win32-vs16-x64\php.exe"),
    ):
        if calon.is_file():
            return str(calon)

    return None


def port_terbuka(port: int, proses: subprocess.Popen | None = None) -> bool:
    """Cek apakah ada yang menerima koneksi di port tersebut."""
    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as koneksi:
        koneksi.settimeout(0.5)
        return koneksi.connect_ex(("127.0.0.1", port)) == 0


def tunggu_port(port: int, proses: subprocess.Popen, batas_detik: int = 20) -> bool:
    """Tunggu sampai server PHP siap menerima koneksi."""
    akhir = time.monotonic() + batas_detik
    while time.monotonic() < akhir:
        if proses.poll() is not None:
            return False
        if port_terbuka(port):
            return True
        time.sleep(0.3)
    return False


def jalankan_server(php: str, port: int) -> subprocess.Popen:
    """Nyalakan server bawaan PHP untuk folder project."""
    perintah = [php, "-S", f"127.0.0.1:{port}", "-t", str(AKAR)]
    proses = subprocess.Popen(
        perintah,
        cwd=str(AKAR),
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
        text=True,
        encoding="utf-8",
        errors="replace",
    )
    if not tunggu_port(port, proses):
        proses.kill()
        keluaran = ""
        if proses.stdout is not None:
            keluaran = proses.stdout.read()[:500]
        raise RuntimeError(
            "Server PHP tidak mau jalan di port "
            f"{port}. Keluaran server:\n{keluaran or '(kosong)'}"
        )
    return proses


def matikan_server(proses: subprocess.Popen | None) -> None:
    """Hentikan server PHP dengan rapi."""
    if proses is None or proses.poll() is not None:
        return
    proses.terminate()
    try:
        proses.wait(timeout=5)
    except subprocess.TimeoutExpired:
        proses.kill()
        proses.wait(timeout=5)


def ambil_halaman(url: str, timeout: int) -> tuple[int, str, str, str]:
    """Ambil satu URL, kembalikan (status, isi, url akhir, tipe konten)."""
    permintaan = urllib.request.Request(
        url, headers={"User-Agent": "cek-halaman-portofolio/1.0"}
    )
    try:
        with urllib.request.urlopen(permintaan, timeout=timeout) as jawaban:
            isi = jawaban.read().decode("utf-8", errors="replace")
            return (
                jawaban.status,
                isi,
                jawaban.geturl(),
                jawaban.headers.get("Content-Type") or "",
            )
    except urllib.error.HTTPError as galat:
        isi = galat.read().decode("utf-8", errors="replace")
        return galat.code, isi, galat.url, galat.headers.get("Content-Type") or ""
    except urllib.error.URLError as galat:
        raise RuntimeError(f"Tidak bisa menghubungi {url}: {galat.reason}") from galat


def periksa(cek: Cek, base_url: str, timeout: int) -> Hasil:
    """Jalankan satu pengujian halaman."""
    url = base_url.rstrip("/") + cek.jalur
    try:
        status, isi, url_akhir, tipe = ambil_halaman(url, timeout)
    except RuntimeError as galat:
        return Hasil(cek.nama, False, [str(galat)])

    catatan: list[str] = []
    lulus = True

    if status not in cek.status_harap:
        lulus = False
        catatan.append(f"status {status}, harapan {'/'.join(str(s) for s in cek.status_harap)}")

    for penanda in cek.penanda:
        if penanda not in isi:
            lulus = False
            catatan.append(f"penanda '{penanda}' tidak ditemukan")

    if cek.akhir_url_harap and not url_akhir.endswith(cek.akhir_url_harap):
        lulus = False
        catatan.append(f"berakhir di {url_akhir}, harapan berakhiran {cek.akhir_url_harap}")

    if cek.tipe_konten_harap and not tipe.startswith(cek.tipe_konten_harap):
        lulus = False
        catatan.append(f"tipe konten '{tipe or '(kosong)'}', harapan {cek.tipe_konten_harap}")

    for tanda in TANDA_ERROR_PHP:
        if tanda in isi:
            lulus = False
            keterangan = KETERANGAN_DB if "Database belum bisa dihubungi" in tanda else KETERANGAN_ERROR
            potongan = potong(isi, tanda)
            catatan.append(f"{keterangan}: {potongan}")
            break

    if not catatan:
        catatan.append(f"status {status}, {len(isi)} karakter, url {url_akhir}")

    return Hasil(cek.nama, lulus, catatan)


def potong(isi: str, tanda: str, lebar: int = 90) -> str:
    """Ambil potongan teks sekitar kemunculan pertama tanda."""
    indeks = isi.find(tanda)
    if indeks < 0:
        return tanda
    awal = max(0, indeks - 30)
    akhir = min(len(isi), indeks + lebar)
    potongan = " ".join(isi[awal:akhir].split())
    return f"\"{potongan}\""


def main() -> int:
    parser = argparse.ArgumentParser(
        description="Cek cepat semua halaman portofolio dari sisi server.",
        formatter_class=argparse.RawDescriptionHelpFormatter,
    )
    parser.add_argument("--port", type=int, default=8765, help="port server PHP (bawaan 8765)")
    parser.add_argument(
        "--tanpa-server",
        action="store_true",
        help="jangan nyalakan server, pakai server yang sudah jalan",
    )
    parser.add_argument(
        "--base-url",
        default="",
        help="alamat situs yang diuji, misal https://contoh.my.id (default http://127.0.0.1:PORT)",
    )
    parser.add_argument("--php", default="", help="lokasi binary PHP bila tidak ada di PATH")
    parser.add_argument("--timeout", type=int, default=15, help="batas waktu tiap permintaan (detik)")
    args = parser.parse_args()

    proses = None
    if args.base_url:
        base_url = args.base_url.rstrip("/")
    else:
        binari_php = cari_php(args.php)
        if binari_php is None:
            print("PHP tidak ditemukan di PATH maupun C:\\xampp\\php\\php.exe.")
            print("Sebut lokasi PHP dengan --php, atau nyalakan XAMPP lalu jalankan ulang.")
            return 2
        base_url = f"http://127.0.0.1:{args.port}"
        try:
            proses = jalankan_server(binari_php, args.port)
        except RuntimeError as galat:
            print(f"Gagal menyalakan server: {galat}")
            return 2

    print(f"Memeriksa {len(CEK_SEMUA)} halaman di {base_url}\n")

    hasil: list[Hasil] = []
    try:
        for cek in CEK_SEMUA:
            hasil.append(periksa(cek, base_url, args.timeout))
    finally:
        matikan_server(proses)

    for item in hasil:
        tanda = "LULUS" if item.lulus else "GAGAL"
        print(f"[{tanda}] {item.nama}")
        for catatan in item.catatan:
            print(f"         {catatan}")

    jumlah_lulus = sum(1 for item in hasil if item.lulus)
    jumlah_gagal = len(hasil) - jumlah_lulus
    print(f"\nRingkasan: {jumlah_lulus} lulus, {jumlah_gagal} gagal")

    if jumlah_gagal:
        print("Saran: kalau halaman beranda gagal dengan pesan database, nyalakan MySQL di XAMPP.")
    return 1 if jumlah_gagal else 0


if __name__ == "__main__":
    sys.exit(main())
