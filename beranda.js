(function () {
    'use strict';

    var kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

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
    var figureHero = document.querySelector('.hero-figure');
    var pewaktuSense;

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
                        ' · Figur: ' + (versiWajah ? 'Erwa Parker' : 'Spiderman');
                }

                if (figureHero && !kurangiGerak) {
                    window.clearTimeout(pewaktuSense);
                    figureHero.classList.remove('is-sensing');
                    void figureHero.offsetWidth;
                    figureHero.classList.add('is-sensing');
                    pewaktuSense = window.setTimeout(function () {
                        figureHero.classList.remove('is-sensing');
                    }, 680);
                }
            });
        });
    }

    /* Parallax ringan pada foto hero, maksimal 20px. */
    if (figureHero && !kurangiGerak) {
        var parallaxTick = false;

        var perbaruiParallax = function () {
            if (window.innerWidth <= 860) {
                figureHero.style.setProperty('--parallax-main', '0px');
                figureHero.style.setProperty('--parallax-back', '0px');
                figureHero.style.setProperty('--parallax-mid', '0px');
                return;
            }

            var kotak = figureHero.getBoundingClientRect();
            if (kotak.bottom < 0 || kotak.top > window.innerHeight) {
                return;
            }

            var progres = (window.innerHeight - kotak.top) / (window.innerHeight + kotak.height);
            var utama = (progres - .5) * 20;

            figureHero.style.setProperty('--parallax-main', utama.toFixed(1) + 'px');
            figureHero.style.setProperty('--parallax-back', (-utama * .45).toFixed(1) + 'px');
            figureHero.style.setProperty('--parallax-mid', (utama * .3).toFixed(1) + 'px');
        };

        var mintaParallax = function () {
            if (parallaxTick) {
                return;
            }
            parallaxTick = true;
            window.requestAnimationFrame(function () {
                perbaruiParallax();
                parallaxTick = false;
            });
        };

        window.addEventListener('scroll', mintaParallax, { passive: true });
        window.addEventListener('resize', mintaParallax, { passive: true });
        perbaruiParallax();
    }

    /* Hitung naik angka statistik sekali saat terlihat. */
    var stats = document.querySelector('.stats');
    var angkaStats = document.querySelectorAll('[data-count-to]');

    if (stats && angkaStats.length && !kurangiGerak && 'IntersectionObserver' in window) {
        var pengamatStats = new IntersectionObserver(function (entri) {
            entri.forEach(function (e) {
                if (!e.isIntersecting) {
                    return;
                }

                pengamatStats.unobserve(stats);
                var mulai = window.performance.now();
                var durasi = 900;

                var hitung = function (sekarang) {
                    var bagian = Math.min((sekarang - mulai) / durasi, 1);
                    var halus = 1 - Math.pow(1 - bagian, 3);

                    angkaStats.forEach(function (el) {
                        var target = parseInt(el.getAttribute('data-count-to'), 10) || 0;
                        el.textContent = String(Math.round(halus * target));
                    });

                    if (bagian < 1) {
                        window.requestAnimationFrame(hitung);
                    }
                };

                window.requestAnimationFrame(hitung);
            });
        }, { threshold: .35 });

        pengamatStats.observe(stats);
    }

    /* Lambang laba-laba di garis pembatas tiap section. */
    var svgLabaLaba = '<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" focusable="false">' +
        '<ellipse cx="16" cy="19" rx="4" ry="5.5" fill="currentColor" stroke="none"/>' +
        '<circle cx="16" cy="11.5" r="3" fill="currentColor" stroke="none"/>' +
        '<path d="M12.5 15.5 6 12.5 2.5 7.5"/><path d="M19.5 15.5 26 12.5 29.5 7.5"/>' +
        '<path d="M12 18 5 18 1 21"/><path d="M20 18 27 18 31 21"/>' +
        '<path d="M12.5 21 6 24 3.5 29"/><path d="M19.5 21 26 24 28.5 29"/></svg>';

    document.querySelectorAll('.section:not(.contact)').forEach(function (section) {
        var tanda = document.createElement('span');
        tanda.className = 'divider-spider';
        tanda.setAttribute('aria-hidden', 'true');
        tanda.innerHTML = svgLabaLaba;
        section.appendChild(tanda);
    });

    var daftarKepala = document.querySelectorAll('.section-head');

    if (daftarKepala.length && !kurangiGerak && 'IntersectionObserver' in window) {
        var pengamatKepala = new IntersectionObserver(function (entri) {
            entri.forEach(function (e) {
                if (e.isIntersecting) {
                    e.target.classList.add('in');
                    pengamatKepala.unobserve(e.target);
                }
            });
        }, { rootMargin: '0px 0px -12% 0px', threshold: .45 });

        daftarKepala.forEach(function (kepala) {
            pengamatKepala.observe(kepala);
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
