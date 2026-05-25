(function () {
    'use strict';

    const toggleBtn = document.getElementById('togglePwd');
    const passwordInput = document.getElementById('password');
    const toggleIcon    = document.getElementById('toggle-icon');
    const BASE = document.querySelector('meta[name="base-url"]')?.getAttribute('content') || '';

    if (toggleBtn && passwordInput && toggleIcon) {
        toggleBtn.addEventListener('click', () => {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type  = isHidden ? 'text' : 'password';
            toggleIcon.src      = BASE + 'images/' + (isHidden ? 'invisible.png' : 'visible.png');
            toggleIcon.alt      = isHidden ? 'Hide password' : 'Show password';
        });
    }


    const form = document.getElementById('login-form');
    const submitBtn = document.getElementById('login-submit');

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const username = document.getElementById('username')?.value.trim();
            const password = document.getElementById('password')?.value;

            if (!username || !password) {
                VRS.notify.warning('Please fill in all fields.');
                return;
            }


            submitBtn.disabled = true;
            submitBtn.textContent = 'Signing in…';

            try {
                const result = await VRS.ajax.submitForm(form, {
                    submitBtn: submitBtn,
                    onSuccess: (data) => {
                        VRS.notify.success(data.message || 'Login successful!');
                        setTimeout(() => {
                            window.location.href = data.redirect || BASE;
                        }, 800);
                    },
                    onError: (err) => {
                        VRS.notify.error(err.message || 'Login failed. Please try again.');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Log In';
                    },
                });
            } catch (err) {
                VRS.notify.error('An unexpected error occurred.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Log In';
            }
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && form && !submitBtn.disabled) {
            form.dispatchEvent(new Event('submit', { cancelable: true }));
        }
    });

})();
