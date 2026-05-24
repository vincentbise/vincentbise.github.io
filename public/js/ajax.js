/**
 * ajax.js — Central AJAX utility for USeP VRS
 * Usage:
 *   VRS.ajax.post('api/accounts/store', formData).then(...)
 *   VRS.ajax.get('api/reports?type=daily').then(...)
 */

window.VRS = window.VRS || {};

VRS.ajax = (function () {
    'use strict';

    /** Get the CSRF token from the meta tag. */
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    /** Build the full URL relative to BASE_URL, routing through index.php. */
    function buildUrl(path) {
        const base = document.querySelector('meta[name="base-url"]');
        const baseUrl = base ? base.getAttribute('content') : '/';
        if (!path) return baseUrl;

        // Already a full URL
        if (/^https?:\/\//i.test(path)) return path;

        // Absolute path
        if (path.startsWith('/')) return window.location.origin + path;

        // Relative path — route through index.php for compatibility
        // (works with or without .htaccess mod_rewrite)
        if (!path.startsWith('index.php/') && path !== 'index.php') {
            path = 'index.php/' + path;
        }
        return baseUrl + path;
    }

    /**
     * POST data via AJAX (FormData or plain object).
     * @param {string} path - Route path (e.g. 'api/accounts/store')
     * @param {FormData|Object} data - Data to send
     * @returns {Promise<Object>} JSON response
     */
    async function post(path, data) {
        const url = buildUrl(path);
        const headers = {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': getCsrfToken(),
        };

        let body;
        if (data instanceof FormData) {
            data.append('csrf_token', getCsrfToken());
            body = data;
        } else {
            headers['Content-Type'] = 'application/x-www-form-urlencoded';
            const params = new URLSearchParams(data);
            params.append('csrf_token', getCsrfToken());
            body = params.toString();
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: headers,
                body: body,
                credentials: 'same-origin',
            });

            /* ── Guard: ensure the response is actually JSON ── */
            const contentType = response.headers.get('Content-Type') || '';
            if (!contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Expected JSON but received:', contentType, text.substring(0, 300));
                throw {
                    success: false,
                    message: 'Server returned an unexpected response. Please refresh the page and try again.',
                };
            }

            const json = await response.json();

            if (!response.ok) {
                if (response.status === 401) {
                    VRS.notify.error(json.message || 'Session expired.');
                    setTimeout(() => { window.location.href = buildUrl('login'); }, 1500);
                    throw json;
                }
                throw json;
            }

            return json;
        } catch (err) {
            if (err && err.message) throw err;
            throw { success: false, message: 'Network error. Please check your connection.' };
        }
    }

    /**
     * GET data via AJAX.
     * @param {string} path - Route path with query string
     * @returns {Promise<Object>} JSON response
     */
    async function get(path) {
        const url = buildUrl(path);
        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': getCsrfToken(),
                },
                credentials: 'same-origin',
            });

            /* ── Guard: ensure the response is actually JSON ── */
            const contentType = response.headers.get('Content-Type') || '';
            if (!contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Expected JSON but received:', contentType, text.substring(0, 300));
                throw {
                    success: false,
                    message: 'Server returned an unexpected response. Please refresh the page and try again.',
                };
            }

            return await response.json();
        } catch (err) {
            if (err && err.message) throw err;
            throw { success: false, message: 'Network error.' };
        }
    }

    /**
     * Submit a form via AJAX. Disables the submit button during request.
     * @param {HTMLFormElement} form - The form element
     * @param {Object} opts - { onSuccess, onError, submitBtn }
     */
    async function submitForm(form, opts = {}) {
        const btn = opts.submitBtn || form.querySelector('button[type="submit"]');
        const originalText = btn ? btn.textContent : '';

        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Processing…';
        }

        try {
            const formData = new FormData(form);
            const action = form.getAttribute('data-ajax-url') || form.action;

            /* ── Derive relative path from the action URL ── */
            const baseMeta = document.querySelector('meta[name="base-url"]');
            const baseUrl = baseMeta ? baseMeta.getAttribute('content') : '/';
            let path = action;

            // Strip the full base URL first (covers absolute URLs)
            if (path.startsWith(baseUrl)) {
                path = path.substring(baseUrl.length);
            } else {
                // Fallback: strip origin, then strip the path-only portion of baseUrl
                path = path.replace(window.location.origin, '');
                try {
                    const basePath = new URL(baseUrl).pathname;
                    if (path.startsWith(basePath)) {
                        path = path.substring(basePath.length);
                    }
                } catch (_) {
                    // baseUrl is already a relative path
                    if (path.startsWith(baseUrl)) {
                        path = path.substring(baseUrl.length);
                    }
                }
            }
            // Remove leading slashes to get a clean relative path
            path = path.replace(/^\/+/, '');

            const result = await post(path, formData);

            if (result.success) {
                if (opts.onSuccess) opts.onSuccess(result);
                else VRS.notify.success(result.message || 'Operation completed successfully.');
            } else {
                if (opts.onError) opts.onError(result);
                else VRS.notify.error(result.message || 'Something went wrong.');
            }

            return result;
        } catch (err) {
            const msg = err.message || 'An unexpected error occurred.';
            if (opts.onError) opts.onError(err);
            else VRS.notify.error(msg);
            return err;
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.textContent = originalText;
            }
        }
    }

    return { post, get, submitForm, getCsrfToken, buildUrl };
})();
