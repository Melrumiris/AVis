/**
 * accountDom.js — Account / Profile page DOM controller
 *
 * Loads user profile on page load and handles profile update form submission.
 */
document.addEventListener('DOMContentLoaded', () => {
    const showMessage = (text, isError = false) => {
        const el = document.getElementById('msg-profile');
        el.textContent = text;
        el.style.color = isError ? '#e53e3e' : '#38a169';
    };

    // ─── Load Profile ────────────────────────────────────────────
    const loadProfile = async () => {
        try {
            const result = await ProfileApi.getProfile();
            if (!result.success) {
                showMessage('Failed to load profile.', true);
                return;
            }

            const data = result.data;
            document.getElementById('profile-username').textContent = data.username || '—';
            document.getElementById('profile-role').textContent     = data.role || '—';
            document.getElementById('profile-email').value          = data.email || '';
            document.getElementById('profile-bio').value            = data.bio || '';
        } catch (err) {
            console.error('Failed to load profile:', err);
            showMessage('Failed to load profile.', true);
        }
    };

    loadProfile();

    // ─── Save Profile ────────────────────────────────────────────
    document.getElementById('form-profile').addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = document.getElementById('btn-save-profile');
        btn.disabled = true;
        btn.textContent = 'Saving...';

        const email = document.getElementById('profile-email').value.trim();
        const bio   = document.getElementById('profile-bio').value.trim();

        try {
            const result = await ProfileApi.updateProfile(email, bio);
            if (result.success) {
                showMessage('Profile updated successfully.');
            } else {
                showMessage(`Error: ${result.error}`, true);
            }
        } catch (err) {
            showMessage('Failed to save profile.', true);
        } finally {
            btn.disabled = false;
            btn.textContent = 'Save Profile';
        }
    });
});
