'use strict';

/* Kursor jaring berbasis CSS (data-URI) + efek klik.
   Semua elemen digambar sendiri lewat SVG inline di style.css,
   tidak memakai aset karakter apa pun. */

(function () {
    // Jalankan hanya di perangkat pointer halus dan bila pengguna tidak minta kurangi gerak.
    var halus = window.matchMedia('(hover: hover) and (pointer: fine)');
    var tenang = window.matchMedia('(prefers-reduced-motion: reduce)');

    try {
        var html = document.documentElement;

        function terapkan() {
            var aktif = halus.matches && !tenang.matches;
            html.classList.toggle('js-kursor', aktif);
        }

        terapkan();

        if (halus.addEventListener) {
            halus.addEventListener('change', terapkan);
        }
        if (tenang.addEventListener) {
            tenang.addEventListener('change', terapkan);
        }

        // Efek jaring kecil melebar dan memudar saat klik (400 ms).
        document.addEventListener('pointerdown', function (e) {
            if (e.pointerType !== 'mouse' || !html.classList.contains('js-kursor')) {
                return;
            }
            var jaring = document.createElement('span');
            jaring.className = 'jaring-klik';
            jaring.setAttribute('aria-hidden', 'true');
            jaring.style.left = e.clientX + 'px';
            jaring.style.top = e.clientY + 'px';
            document.body.appendChild(jaring);
            jaring.addEventListener('animationend', function () {
                jaring.remove();
            });
        }, { passive: true });
    } catch (e) {
    }
})();
