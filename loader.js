(function () {
    'use strict';

    var html = document.documentElement;
    var KUNCI_WAKTU = 'jaring_loader_waktu';
    var BATAS_JAM = 12 * 60 * 60 * 1000;
    var MAKSIMAL = 2000;
    var DURASI = 1600;

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

        if (tenang.matches || sudahBaru) {
            html.classList.add('loader-selesai');
            hapusOverlay();
            return;
        }

        var lapis = document.createElement('div');
        lapis.className = 'loader-jaring';
        lapis.setAttribute('role', 'status');
        lapis.setAttribute('aria-live', 'polite');
        lapis.setAttribute('aria-hidden', 'true');
        lapis.setAttribute('aria-label', 'Memuat halaman');
        lapis.innerHTML =
            '<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">' +
            '<g class="jalur-jaring">' +
            '<circle cx="50" cy="50" r="44" pathLength="100"/>' +
            '<circle cx="50" cy="50" r="30" pathLength="100"/>' +
            '<circle cx="50" cy="50" r="16" pathLength="100"/>' +
            '<path d="M50 6v88M6 50h88M19 19l62 62M81 19L19 81" pathLength="100"/>' +
            '</g>' +
            '</svg>' +
            '<span class="angka-loader" aria-hidden="true">0%</span>';

        document.body.appendChild(lapis);

        var angka = lapis.querySelector('.angka-loader');
        var jalur = lapis.querySelectorAll('.jalur-jaring path, .jalur-jaring circle');

        html.classList.add('loader-aktif');

        var mulai = performance.now();
        var rampung = false;

        function selesaikan() {
            if (rampung) {
                return;
            }
            rampung = true;
            html.classList.add('loader-selesai');
            tandaiWaktu();
            window.setTimeout(hapusOverlay, 600);
        }

        function langkah(waktu) {
            var lewat = waktu - mulai;
            var rasio = Math.min(lewat / DURASI, 1);
            var persen = Math.round(rasio * 100);

            if (angka) {
                angka.textContent = persen + '%';
            }

            for (var i = 0; i < jalur.length; i++) {
                var maju = (rasio - i * 0.06) / (1 - (jalur.length - 1) * 0.06);
                maju = Math.max(0, Math.min(maju, 1));
                jalur[i].style.strokeDashoffset = String(100 - maju * 100);
                jalur[i].classList.toggle('rajut', maju >= 1);
            }

            if (rasio >= 1 || lewat >= MAKSIMAL) {
                selesaikan();
                return;
            }
            window.requestAnimationFrame(langkah);
        }

        window.requestAnimationFrame(langkah);
        window.setTimeout(selesaikan, MAKSIMAL + 50);
        window.addEventListener('pagehide', hapusOverlay);
    } catch (e) {
        html.classList.add('loader-selesai');
        hapusOverlay();
    }
})();
