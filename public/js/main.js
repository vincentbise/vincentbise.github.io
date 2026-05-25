(function () {
    'use strict';
    const toggleBtn = document.getElementById('toggleSideNav');
    const sideNav = document.getElementById('sideNav');
    const wrap = document.querySelector('.wrap');

    function isMobile() {
        return window.matchMedia('(max-width: 768px)').matches;
    }

    function toggleSideNav() {
        if (!sideNav) return;
        if (isMobile()) {
            sideNav.classList.toggle('show');
        } else {
            sideNav.classList.toggle('is-collapsed');
            const collapsed = sideNav.classList.contains('is-collapsed');
            if (wrap) wrap.classList.toggle('is-collapsed', collapsed);
        }
    }

    if (toggleBtn && sideNav) {
        toggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            toggleSideNav();
        });


        document.addEventListener('click', (e) => {
            if (
                isMobile() &&
                sideNav.classList.contains('show') &&
                !sideNav.contains(e.target) &&
                !toggleBtn.contains(e.target)
            ) {
                sideNav.classList.remove('show');
            }
        });
    }


    const currentPath = window.location.pathname;
    document.querySelectorAll('.nav-btn').forEach(link => {
        const href = link.getAttribute('href') || '';
        if (href && currentPath.includes(href.split('/').pop())) {
            link.classList.add('active');
        }
    });


    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity .5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });

})();