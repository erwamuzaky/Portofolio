(function () {
    'use strict';

    /* Navigasi: buka-tutup menu di layar kecil. */
    var nav = document.getElementById('nav');
    var navToggle = document.getElementById('navToggle');
    var navLinks = document.getElementById('navLinks');

    function tutupMenu() {
        if (!nav || !navToggle) {
            return;
        }
        nav.classList.remove('nav-open');
        navToggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('menu-open');
    }

    if (nav && navToggle && navLinks) {
        navToggle.addEventListener('click', function () {
            var terbuka = nav.classList.toggle('nav-open');
            navToggle.setAttribute('aria-expanded', String(terbuka));
            document.body.classList.toggle('menu-open', terbuka);
        });

        navLinks.querySelectorAll('a').forEach(function (tautan) {
            tautan.addEventListener('click', tutupMenu);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && nav.classList.contains('nav-open')) {
                tutupMenu();
            }
        });
    }

    /* Tandai menu yang sedang aktif mengikuti bagian yang terlihat. */
    if (navLinks && 'IntersectionObserver' in window) {
        var tautanNav = Array.prototype.slice.call(navLinks.querySelectorAll('a'));
        var bagianNav = tautanNav
            .map(function (tautan) {
                return document.querySelector(tautan.getAttribute('href'));
            })
            .filter(Boolean);

        var pengamatNav = new IntersectionObserver(function (entri) {
            entri.forEach(function (e) {
                if (!e.isIntersecting) {
                    return;
                }
                tautanNav.forEach(function (tautan) {
                    tautan.classList.toggle('active', tautan.getAttribute('href') === '#' + e.target.id);
                });
            });
        }, { rootMargin: '-38% 0px -55% 0px', threshold: 0 });

        bagianNav.forEach(function (bagian) {
            pengamatNav.observe(bagian);
        });
    }

    /* Tukar figur antara versi wajah dan versi topeng. */
    var faceImg = document.getElementById('heroFace');
    var maskImg = document.getElementById('heroMask');
    var caption = document.getElementById('heroCaption');
    var faceButtons = document.querySelectorAll('[data-face]');

    if (faceImg && maskImg && faceButtons.length) {
        faceButtons.forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                var versiWajah = tombol.getAttribute('data-face') === 'wajah';
                faceImg.classList.toggle('is-hidden', !versiWajah);
                maskImg.classList.toggle('is-hidden', versiWajah);

                faceButtons.forEach(function (lain) {
                    lain.setAttribute('aria-pressed', String(lain === tombol));
                });

                if (caption) {
                    caption.textContent = 'Foto: ' + (caption.getAttribute('data-nama') || '') +
                        ' / ' + (caption.getAttribute('data-tahun') || '') +
                        ' · Figur: ' + (versiWajah ? 'versi wajah' : 'versi topeng');
                }
            });
        });
    }

    /* Dialog detail project. */
    var pemicuTerakhir = null;

    document.querySelectorAll('[data-dialog]').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            var dialog = document.getElementById(tombol.getAttribute('data-dialog'));
            if (!dialog || typeof dialog.showModal !== 'function') {
                return;
            }
            pemicuTerakhir = tombol;
            dialog.showModal();
        });
    });

    document.querySelectorAll('dialog').forEach(function (dialog) {
        dialog.querySelectorAll('[data-close]').forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                dialog.close();
            });
        });

        dialog.addEventListener('click', function (e) {
            if (e.target === dialog) {
                dialog.close();
            }
        });

        dialog.addEventListener('close', function () {
            if (pemicuTerakhir && document.contains(pemicuTerakhir)) {
                pemicuTerakhir.focus();
            }
            pemicuTerakhir = null;
        });
    });

    /* Salin alamat email ke papan klip. */
    var copyBtn = document.getElementById('copyEmail');
    var copyStatus = document.getElementById('copyStatus');

    if (copyBtn && copyStatus) {
        var email = copyBtn.getAttribute('data-email') || '';
        var pewaktuStatus;

        function tampilStatus(teks) {
            copyStatus.textContent = teks;
            window.clearTimeout(pewaktuStatus);
            pewaktuStatus = window.setTimeout(function () {
                copyStatus.textContent = '';
            }, 2400);
        }

        function salinManual() {
            var area = document.createElement('textarea');
            area.value = email;
            area.setAttribute('readonly', '');
            area.style.position = 'fixed';
            area.style.top = '-1000px';
            document.body.appendChild(area);
            area.select();

            var berhasil = false;
            try {
                berhasil = document.execCommand('copy');
            } catch (e) {
                berhasil = false;
            }

            document.body.removeChild(area);
            return berhasil;
        }

        copyBtn.addEventListener('click', function () {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(email).then(function () {
                    tampilStatus('Email tersalin.');
                }).catch(function () {
                    tampilStatus(salinManual() ? 'Email tersalin.' : 'Salin manual: ' + email);
                });
                return;
            }
            tampilStatus(salinManual() ? 'Email tersalin.' : 'Salin manual: ' + email);
        });
    }

    /* Tombol kembali ke atas. */
    var toTop = document.getElementById('toTop');

    if (toTop) {
        function periksaGulir() {
            toTop.classList.toggle('show', window.scrollY > 620);
        }

        window.addEventListener('scroll', periksaGulir, { passive: true });
        periksaGulir();

        toTop.addEventListener('click', function () {
            var tenang = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            window.scrollTo({ top: 0, behavior: tenang ? 'auto' : 'smooth' });
        });
    }

    /* Animasi masuk saat bagian mulai terlihat. */
    var daftarReveal = document.querySelectorAll('.reveal');
    var kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!daftarReveal.length) {
        return;
    }

    if (kurangiGerak || !('IntersectionObserver' in window)) {
        daftarReveal.forEach(function (el) {
            el.classList.add('in');
        });
        return;
    }

    var pengamatReveal = new IntersectionObserver(function (entri) {
        entri.forEach(function (e) {
            if (e.isIntersecting) {
                e.target.classList.add('in');
                pengamatReveal.unobserve(e.target);
            }
        });
    }, { threshold: 0.08 });

    daftarReveal.forEach(function (el) {
        pengamatReveal.observe(el);
    });
})();
