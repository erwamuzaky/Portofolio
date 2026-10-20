(function () {
    'use strict';

    var html = document.documentElement;
    var KUNCI = 'jaring_loader_sudah';

    function bersihkan() {
        html.classList.remove('loader-aktif');
        html.removeAttribute('data-loader');
    }

    try {
        var tenang = window.matchMedia('(prefers-reduced-motion: reduce)');
        var sudah = false;

        try {
            sudah = sessionStorage.getItem(KUNCI) === '1';
        } catch (e) {
            sudah = true;
        }

        if (tenang.matches || sudah) {
            html.classList.add('loader-selesai');
            return;
        }

        var lapis = document.createElement('div');
        lapis.className = 'loader-jaring';
        lapis.setAttribute('role', 'status');
        lapis.setAttribute('aria-live', 'polite');
        lapis.innerHTML =
            '<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">' +
            '<g class="jalur-jaring">' +
            '<circle cx="50" cy="50" r="44" pathLength="100"/>' +
            '<circle cx="50" cy="50" r="30" pathLength="100"/>' +
            '<circle cx="50" cy="50" r="16" pathLength="100"/>' +
            '<path d="M50 6v88M6 50h88M19 19l62 62M81 19L19 81" pathLength="100"/>' +
            '</g>' +
            '</svg>' +
            '<span class="angka-loader">0%</span>';

        document.body.appendChild(lapis);

        var angka = lapis.querySelector('.angka-loader');
        var jalur = lapis.querySelectorAll('.jalur-jaring path, .jalur-jaring circle');

        html.classList.add('loader-aktif');

        var mulai = performance.now();
        var durasi = 1600;
        var maksimal = 2000;
        var rampung = false;

        function selesaikan() {
            if (rampung) {
                return;
            }
            rampung = true;
            html.classList.add('loader-selesai');
            try {
                sessionStorage.setItem(KUNCI, '1');
            } catch (e) {
            }
            window.setTimeout(bersihkan, 600);
        }

        function langkah(waktu) {
            var lewat = waktu - mulai;
            var rasio = Math.min(lewat / durasi, 1);
            var persen = Math.round(rasio * 100);

            angka.textContent = persen + '%';

            for (var i = 0; i < jalur.length; i++) {
                var maju = (rasio - i * 0.06) / (1 - (jalur.length - 1) * 0.06);
                maju = Math.max(0, Math.min(maju, 1));
                jalur[i].style.strokeDashoffset = String(100 - maju * 100);
                jalur[i].classList.toggle('rajut', maju >= 1);
            }

            if (rasio >= 1 || lewat >= maksimal) {
                selesaikan();
                return;
            }
            window.requestAnimationFrame(langkah);
        }

        window.requestAnimationFrame(langkah);
        window.setTimeout(selesaikan, maksimal + 50);
        window.addEventListener('pagehide', bersihkan);
    } catch (e) {
        bersihkan();
    }
})();
