(function () {
    'use strict';

    try {
        var html = document.documentElement;
        var halus = window.matchMedia('(pointer: fine)');
        var tenang = window.matchMedia('(prefers-reduced-motion: reduce)');

        if (!halus.matches || tenang.matches) {
            return;
        }

        var lapis = document.createElement('div');
        lapis.className = 'kursor-jaring';
        lapis.setAttribute('aria-hidden', 'true');
        lapis.innerHTML =
            '<svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">' +
            '<g fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round">' +
            '<circle cx="20" cy="20" r="19"/>' +
            '<circle cx="20" cy="20" r="12.5"/>' +
            '<circle cx="20" cy="20" r="6"/>' +
            '<path d="M20 1v38M1 20h38M6.6 6.6l26.8 26.8M33.4 6.6L6.6 33.4"/>' +
            '</g>' +
            '<g fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round">' +
            '<path d="M20 7.5a12.5 12.5 0 0 1 12.5 12.5"/>' +
            '<path d="M20 32.5A12.5 12.5 0 0 1 7.5 20"/>' +
            '</g>' +
            '</svg>';
        document.body.appendChild(lapis);

        var targetX = window.innerWidth / 2;
        var targetY = window.innerHeight / 2;
        var posX = targetX;
        var posY = targetY;
        var jalan = false;
        var figur = null;

        function gambar() {
            posX += (targetX - posX) * 0.22;
            posY += (targetY - posY) * 0.22;
            lapis.style.transform = 'translate3d(' + posX + 'px,' + posY + 'px,0) rotate(' + (posX + posY) * 0.12 + 'deg)';

            if (Math.abs(targetX - posX) > 0.4 || Math.abs(targetY - posY) > 0.4) {
                window.requestAnimationFrame(gambar);
            } else {
                jalan = false;
            }
        }

        function mulai() {
            if (!jalan) {
                jalan = true;
                window.requestAnimationFrame(gambar);
            }
        }

        function cariFigur() {
            if (!figur) {
                figur = document.getElementById('figurTumpuk');
            }
            return figur;
        }

        document.addEventListener('mousemove', function (e) {
            targetX = e.clientX;
            targetY = e.clientY;
            html.classList.add('kursor-aktif');

            var area = cariFigur();
            if (area) {
                var kotak = area.getBoundingClientRect();
                var diDalam =
                    e.clientX >= kotak.left &&
                    e.clientX <= kotak.right &&
                    e.clientY >= kotak.top &&
                    e.clientY <= kotak.bottom;
                html.classList.toggle('kursor-di-figur', diDalam);
            }

            mulai();
        }, { passive: true });

        document.addEventListener('mouseleave', function () {
            html.classList.remove('kursor-aktif');
        });
    } catch (e) {
    }
})();
