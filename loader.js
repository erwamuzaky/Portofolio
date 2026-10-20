'use strict';

/* Loading screen jaring: 12 jari radial + 6 lingkaran konsentris,
   digambar sendiri lewat SVG (pathLength + stroke-dashoffset).
   Angka persen memakai font Anton dengan glow merah. */

(function () {
    var html = document.documentElement;
    var KUNCI_WAKTU = 'jaring_loader_waktu';
    var BATAS_JAM = 12 * 60 * 60 * 1000;
    var MAKSIMAL = 2000;
    var DURASI = 1600;
    var FADE_MS = 300;

    // HAPUS SEBELUM PUBLIK: parameter ?loader=<angka> hanya untuk uji visual.
    var ujiPersen = (function () {
        try {
            var nilai = new URLSearchParams(window.location.search).get('loader');
            if (nilai === null) {
                return null;
            }
            var angka = parseInt(nilai, 10);
            if (isNaN(angka)) {
                return 60;
            }
            return Math.max(0, Math.min(100, angka));
        } catch (e) {
            return null;
        }
    })();

    function hapusOverlay() {
        html.classList.remove('loader-aktif');
        var lama = document.querySelector('.loader-jaring');
        if (lama && lama.parentNode) {
            lama.parentNode.removeChild(lama);
        }
    }

    function tandaiWaktu() {
        try {
            localStorage.setItem(KUNCI_WAKTU, String(Date.now()));
        } catch (e) {
        }
    }

    try {
        var tenang = window.matchMedia('(prefers-reduced-motion: reduce)');
        var sudahBaru = false;

        try {
            var catatan = localStorage.getItem(KUNCI_WAKTU);
            sudahBaru = !!catatan && (Date.now() - parseInt(catatan, 10)) < BATAS_JAM;
        } catch (e) {
            sudahBaru = true;
        }

        if (tenang.matches || (sudahBaru && ujiPersen === null)) {
            html.classList.add('loader-selesai');
            hapusOverlay();
            return;
        }

        var JARI = 12;
        var LINGKARAN = 6;
        var jari = '';
        var i;
        for (i = 0; i < JARI; i++) {
            var sudut = (i * 360) / JARI;
            jari += '<path d="M50 50 L50 4" pathLength="100" transform="rotate(' + sudut.toFixed(2) + ' 50 50)"/>';
        }

        var lingkaran = '';
        for (i = 1; i <= LINGKARAN; i++) {
            var r = (46 * i) / LINGKARAN;
            lingkaran += '<circle cx="50" cy="50" r="' + r.toFixed(2) + '" pathLength="100"/>';
        }

        var lapis = document.createElement('div');
        lapis.className = 'loader-jaring';
        lapis.setAttribute('role', 'status');
        lapis.setAttribute('aria-live', 'polite');
        lapis.setAttribute('aria-hidden', 'true');
        lapis.setAttribute('aria-label', 'Memuat halaman');
        lapis.innerHTML =
            '<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">' +
            '<g class="jalur-jaring">' + jari + '</g>' +
            '<g class="jalur-jaring jalur-lingkaran">' + lingkaran + '</g>' +
            '</svg>' +
            '<span class="angka-loader" aria-hidden="true">0%</span>';

        document.body.appendChild(lapis);

        var angka = lapis.querySelector('.angka-loader');
        var jalurJari = lapis.querySelectorAll('.jalur-jaring:not(.jalur-lingkaran) path');
        var jalurLingkaran = lapis.querySelectorAll('.jalur-lingkaran circle');

        html.classList.add('loader-aktif');

        function gambarJalur(daftar, rasio, jeda, langkahLuar) {
            for (var k = 0; k < daftar.length; k++) {
                var maju = (rasio - k * jeda) / (1 - (daftar.length - 1) * jeda);
                maju = Math.max(0, Math.min(maju, 1));
                daftar[k].style.strokeDashoffset = String(100 - maju * 100);
                daftar[k].classList.toggle('rajut', maju >= 1);
            }
        }

        var mulai = performance.now();
        var rampung = false;

        function selesaikan() {
            if (rampung) {
                return;
            }
            rampung = true;
            html.classList.add('loader-selesai');
            tandaiWaktu();
            window.setTimeout(hapusOverlay, FADE_MS);
        }

        function langkah(waktu) {
            var lewat = waktu - mulai;
            var rasio = Math.min(lewat / DURASI, 1);
            if (ujiPersen !== null) {
                rasio = ujiPersen / 100;
            }
            var persen = Math.round(rasio * 100);

            if (angka) {
                angka.textContent = persen + '%';
            }

            gambarJalur(jalurJari, rasio, 0.04, 0);
            gambarJalur(jalurLingkaran, rasio, 0.05, 0);

            if (ujiPersen !== null) {
                window.requestAnimationFrame(langkah);
                return;
            }

            if (rasio >= 1 || lewat >= MAKSIMAL) {
                selesaikan();
                return;
            }
            window.requestAnimationFrame(langkah);
        }

        window.requestAnimationFrame(langkah);
        window.setTimeout(selesaikan, MAKSIMAL + DURASI);
        window.addEventListener('pagehide', hapusOverlay);
    } catch (e) {
        html.classList.add('loader-selesai');
        hapusOverlay();
    }
})();
