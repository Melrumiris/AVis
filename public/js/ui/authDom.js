document.addEventListener('DOMContentLoaded', () => {
    const bindForm = (formId, endpoint) => {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');

            btn.disabled = true;

            try {
                await AuthApi.authenticate(endpoint, {
                    username: form.username.value,
                    password: form.password.value
                });
                window.location.href = '/';
            } catch (error) {
                alert(`Auth failed: ${error.message}`);
                btn.disabled = false;
            }
        });
    };

    bindForm('login_form', '/api/auth/login');
    bindForm('register_form', '/api/auth/register');
});