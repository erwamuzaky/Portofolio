(function () {
    'use strict';

    var figur = document.getElementById('posterFigur');
    var wajah = document.getElementById('figurWajah');
    var tombol = document.getElementById('figurTombol');
    var panggung = document.getElementById('figurTumpuk');
    if (!figur || !wajah || !tombol || !panggung) {
        return;
    }

    var median = window.matchMedia('(prefers-reduced-motion: reduce)');
    var halus = window.matchMedia('(hover: hover) and (pointer: fine)');
    var x = 50;
    var y = 42;
    var radius = 26;
    var targetRadius = 26;
    var tampil = false;
    var rafBelum = false;

    function terapkan() {
        rafBelum = false;
        var xv = x.toFixed(2) + '%';
        var yv = y.toFixed(2) + '%';
        figur.style.setProperty('--x', xv);
        figur.style.setProperty('--y', yv);
        var rv = radius.toFixed(2) + '%';
        figur.style.setProperty('--r', rv);
        wajah.style.setProperty('--x', xv);
        wajah.style.setProperty('--y', yv);
        wajah.style.setProperty('--r', rv);
    }

    function minta() {
        if (rafBelum) {
            return;
        }
        rafBelum = true;
        window.requestAnimationFrame(animasiLembut);
    }

    function setTampil(nilai) {
        if (tampil === nilai) {
            return;
        }
        tampil = nilai;
        figur.classList.toggle('figur-aktif', nilai);
        tombol.setAttribute('aria-pressed', nilai ? 'true' : 'false');
    }

    function padaGerak(e) {
        var kotak = panggung.getBoundingClientRect();
        if (!kotak.width || !kotak.height) {
            return;
        }
        var lx = (e.clientX - kotak.left) / kotak.width;
        var ly = (e.clientY - kotak.top) / kotak.height;
        x = Math.min(100, Math.max(0, lx * 100));
        y = Math.min(100, Math.max(0, ly * 100));

        var dx = lx - 0.5;
        var dy = ly - 0.5;
        var jauh = Math.sqrt(dx * dx + dy * dy);
        if (jauh < 0.3) {
            var kepadatan = 1 - jauh / 0.3;
            targetRadius = 26 + kepadatan * 16;
            setTampil(true);
        } else {
            targetRadius = 0;
            setTampil(false);
        }
        minta();
    }

    function padaKeluar() {
        targetRadius = 0;
        setTampil(false);
        minta();
    }

    function padaMasuk(e) {
        padaGerak(e);
    }

    function animasiLembut() {
        rafBelum = false;
        if (Math.abs(radius - targetRadius) > 0.15) {
            radius = radius + (targetRadius - radius) * 0.18;
            terapkan();
            minta();
        } else {
            radius = targetRadius;
            terapkan();
        }
    }

    function aktifkanModeKursor() {
        if (median.matches || !halus.matches) {
            return;
        }
        figur.classList.add('figur-halus');
        figur.addEventListener('pointermove', padaGerak, { passive: true });
        figur.addEventListener('pointerenter', padaMasuk, { passive: true });
        figur.addEventListener('pointerleave', padaKeluar, { passive: true });
        minta();
    }

    function aktifkanModeTap() {
        tombol.hidden = false;
        tombol.addEventListener('click', function () {
            if (tampil) {
                setTampil(false);
                radius = 20;
                targetRadius = 20;
            } else {
                setTampil(true);
                radius = 44;
                targetRadius = 44;
            }
            terapkan();
        });

        panggung.addEventListener('pointerdown', function (e) {
            if (e.pointerType === 'touch') {
                var kotak = panggung.getBoundingClientRect();
                if (kotak.width && kotak.height) {
                    x = Math.min(100, Math.max(0, ((e.clientX - kotak.left) / kotak.width) * 100));
                    y = Math.min(100, Math.max(0, ((e.clientY - kotak.top) / kotak.height) * 100));
                    terapkan();
                }
            }
        }, { passive: true });

        setTampil(false);
        radius = 20;
        targetRadius = 20;
        terapkan();
    }

    function aturUlang() {
        if (median.matches || !halus.matches) {
            aktifkanModeTap();
        } else {
            aktifkanModeKursor();
        }
    }

    setTampil(false);
    terapkan();
    aturUlang();

    if (halus.addEventListener) {
        halus.addEventListener('change', aturUlang);
    }
    if (median.addEventListener) {
        median.addEventListener('change', aturUlang);
    }

    var vertikal = document.querySelector('.poster-vertikal');
    var home = document.getElementById('home');
    if (vertikal && home && 'IntersectionObserver' in window) {
        var pengamatVertikal = new IntersectionObserver(function (entri) {
            entri.forEach(function (e) {
                vertikal.classList.toggle('vertikal-tampil', e.isIntersecting);
            });
        }, { threshold: 0.15 });
        pengamatVertikal.observe(home);
    }
})();
