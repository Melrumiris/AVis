document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login_form');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = loginForm.querySelector('button[type="submit"]');
            btn.disabled = true;

            try {
                await AuthApi.authenticate(LOGIN_URL, {
                    email: loginForm.email.value,
                    password: loginForm.password.value
                });
                window.location.href = '/';
            } catch (error) {
                alert(`Auth failed: ${error.message}`);
                btn.disabled = false;
            }
        });
    }

    const registerForm = document.getElementById('register_form');
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (registerForm.password.value !== registerForm.confirm_password.value) {
                alert("Passwords do not match");
                return;
            }
            
            const btn = registerForm.querySelector('button[type="submit"]');
            btn.disabled = true;

            try {
                await AuthApi.authenticate(REGISTER_URL, {
                    username: registerForm.username.value,
                    email: registerForm.email.value,
                    password: registerForm.password.value
                });
                window.location.href = '/';
            } catch (error) {
                alert(`Auth failed: ${error.message}`);
                btn.disabled = false;
            }
        });
    }
});