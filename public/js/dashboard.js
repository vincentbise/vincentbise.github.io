/* dashboard.js — Live clock & UI helpers */

(function () {
    'use strict';


    const clockEl = document.getElementById('clock');
    if (clockEl) {
        function updateClock() {
            const now = new Date();
            clockEl.textContent = now.toLocaleTimeString('en-PH', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            });
        }
        updateClock();
        setInterval(updateClock, 1000);
    }


    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', async (e) => {
            e.preventDefault();
            const ok = await VRS.confirm.show(el.dataset.confirm);
            if (!ok) return;

            if (el.tagName === 'A' && el.href) {
                window.location.href = el.href;
                return;
            }

            const form = el.closest('form');
            if (form) form.submit();
        });
    });


    document.querySelectorAll('.stat .num').forEach(el => {
        const target = parseInt(el.textContent, 10);
        if (isNaN(target)) return;
        let current  = 0;
        const step   = Math.ceil(target / 40);
        const timer  = setInterval(() => {
            current += step;
            if (current >= target) { current = target; clearInterval(timer); }
            el.textContent = current;
        }, 25);
    });

})();
