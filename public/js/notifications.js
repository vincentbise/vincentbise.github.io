/**
 * notifications.js — Toast notification system for USeP VRS
 * Usage:
 *   VRS.notify.success('Reservation submitted!');
 *   VRS.notify.error('Something went wrong.');
 *   VRS.notify.warning('Please check your input.');
 *   VRS.notify.info('Your session will expire soon.');
 */

window.VRS = window.VRS || {};

VRS.notify = (function () {
    'use strict';

    let container = null;

    const ICONS = {
        success: '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="10" fill="#16a34a"/><path d="M6 10l3 3 5-6" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        error:   '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="10" fill="#dc2626"/><path d="M7 7l6 6M13 7l-6 6" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>',
        warning: '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="10" fill="#d97706"/><path d="M10 6v5M10 13v1" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>',
        info:    '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="10" fill="#2563eb"/><path d="M10 9v5M10 6v1" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>',
    };

    function ensureContainer() {
        if (container && document.body.contains(container)) return;
        container = document.createElement('div');
        container.id = 'vrs-notifications';
        container.className = 'vrs-toast-container';
        document.body.appendChild(container);
    }

    /**
     * Show a toast notification.
     * @param {string} type - success | error | warning | info
     * @param {string} message - The notification message
     * @param {number} duration - Auto-dismiss time in ms (default 4000)
     */
    function show(type, message, duration = 4000) {
        ensureContainer();

        const toast = document.createElement('div');
        toast.className = `vrs-toast vrs-toast--${type}`;

        toast.innerHTML = `
            <div class="vrs-toast__icon">${ICONS[type] || ICONS.info}</div>
            <div class="vrs-toast__body">
                <span class="vrs-toast__title">${capitalize(type)}</span>
                <span class="vrs-toast__message">${escapeHtml(message)}</span>
            </div>
            <button class="vrs-toast__close" aria-label="Close">&times;</button>
            <div class="vrs-toast__progress"></div>
        `;


        toast.querySelector('.vrs-toast__close').addEventListener('click', () => dismiss(toast));

        container.appendChild(toast);


        requestAnimationFrame(() => {
            toast.classList.add('vrs-toast--visible');
        });


        const progressBar = toast.querySelector('.vrs-toast__progress');
        progressBar.style.animationDuration = duration + 'ms';
        progressBar.classList.add('vrs-toast__progress--running');


        const timer = setTimeout(() => dismiss(toast), duration);
        toast._timer = timer;

        return toast;
    }

    function dismiss(toast) {
        if (toast._timer) clearTimeout(toast._timer);
        toast.classList.remove('vrs-toast--visible');
        toast.classList.add('vrs-toast--exit');
        toast.addEventListener('animationend', () => {
            toast.remove();
        }, { once: true });
        // Fallback if animationend doesn't fire
        setTimeout(() => {
            if (toast.parentNode) toast.remove();
        }, 400);
    }

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    return {
        success: (msg, dur) => show('success', msg, dur),
        error:   (msg, dur) => show('error',   msg, dur),
        warning: (msg, dur) => show('warning', msg, dur),
        info:    (msg, dur) => show('info',    msg, dur),
        show:    show,
        dismiss: dismiss,
    };
})();

VRS.confirm = (function () {
    'use strict';

    let backdrop = null;
    let titleEl = null;
    let messageEl = null;
    let confirmBtn = null;
    let cancelBtn = null;

    function ensureModal() {
        if (backdrop && document.body.contains(backdrop)) return;

        backdrop = document.createElement('div');
        backdrop.className = 'vrs-confirm-backdrop';
        backdrop.innerHTML = `
            <div class="vrs-confirm" role="dialog" aria-modal="true" aria-labelledby="vrs-confirm-title" aria-describedby="vrs-confirm-message">
                <div class="vrs-confirm__icon" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 7v5"></path>
                        <path d="M12 16h.01"></path>
                    </svg>
                </div>
                <div class="vrs-confirm__body">
                    <h3 id="vrs-confirm-title">Confirm action</h3>
                    <p id="vrs-confirm-message"></p>
                </div>
                <div class="vrs-confirm__actions">
                    <button type="button" class="btn-outline" data-action="cancel">Cancel</button>
                    <button type="button" class="btn-primary" data-action="confirm">Confirm</button>
                </div>
            </div>
        `;
        document.body.appendChild(backdrop);

        titleEl = backdrop.querySelector('#vrs-confirm-title');
        messageEl = backdrop.querySelector('#vrs-confirm-message');
        confirmBtn = backdrop.querySelector('[data-action="confirm"]');
        cancelBtn = backdrop.querySelector('[data-action="cancel"]');
    }

    function show(message, opts = {}) {
        ensureModal();

        const title = opts.title || 'Confirm action';
        const confirmText = opts.confirmText || 'Confirm';
        const cancelText = opts.cancelText || 'Cancel';

        titleEl.textContent = title;
        messageEl.textContent = message || 'Are you sure you want to continue?';
        confirmBtn.textContent = confirmText;
        cancelBtn.textContent = cancelText;

        backdrop.classList.add('is-visible');

        return new Promise((resolve) => {
            function cleanup(result) {
                backdrop.classList.remove('is-visible');
                confirmBtn.removeEventListener('click', onConfirm);
                cancelBtn.removeEventListener('click', onCancel);
                backdrop.removeEventListener('click', onBackdrop);
                document.removeEventListener('keydown', onKeydown);
                resolve(result);
            }

            function onConfirm() { cleanup(true); }
            function onCancel() { cleanup(false); }
            function onBackdrop(e) {
                if (e.target === backdrop) cleanup(false);
            }
            function onKeydown(e) {
                if (e.key === 'Escape') cleanup(false);
                if (e.key === 'Enter') cleanup(true);
            }

            confirmBtn.addEventListener('click', onConfirm);
            cancelBtn.addEventListener('click', onCancel);
            backdrop.addEventListener('click', onBackdrop);
            document.addEventListener('keydown', onKeydown);

            setTimeout(() => confirmBtn.focus(), 0);
        });
    }

    return { show };
})();
