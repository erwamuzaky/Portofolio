(function () {
    var kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var semua = document.querySelectorAll('.reveal');

    if (!semua.length) {
        return;
    }

    if (kurangiGerak || !('IntersectionObserver' in window)) {
        semua.forEach(function (el) {
            el.classList.add('tampil');
        });
        return;
    }

    var pengamat = new IntersectionObserver(function (entri) {
        entri.forEach(function (e) {
            if (e.isIntersecting) {
                e.target.classList.add('tampil');
                pengamat.unobserve(e.target);
            }
        });
    }, {
        rootMargin: '0px 0px -8% 0px',
        threshold: 0.12
    });

    semua.forEach(function (el) {
        pengamat.observe(el);
    });
})();
