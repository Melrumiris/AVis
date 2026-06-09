/**
 * accountDom.js — Account / Profile page DOM controller
 *
 * Starts in view mode. Edit Profile button activates editing.
 * On save, only fields that changed are sent to the backend.
 */
document.addEventListener('DOMContentLoaded', () => {
    const showMessage = (text, isError = false) => {
        const el = document.getElementById('msg-profile');
        el.textContent = text;
        el.style.color = isError ? '#e53e3e' : '#38a169';
    };

    const clearMessage = () => {
        const el = document.getElementById('msg-profile');
        el.textContent = '';
    };

    const usernameInput = document.getElementById('profile-username');
    const bioInput      = document.getElementById('profile-bio');
    const editBtn       = document.getElementById('btn-edit-profile');
    const saveBtn       = document.getElementById('btn-save-profile');
    const cancelBtn     = document.getElementById('btn-cancel-profile');

    // Snapshot of original values to diff against on save
    let original = { username: '', bio: '' };

    const setEditMode = (editing) => {
        usernameInput.disabled = !editing;
        bioInput.disabled      = !editing;
        editBtn.style.display  = editing ? 'none' : 'inline-flex';
        saveBtn.style.display  = editing ? 'inline-flex' : 'none';
        cancelBtn.style.display = editing ? 'inline-flex' : 'none';
        if (!editing) clearMessage();
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
            usernameInput.value = data.username || '';
            document.getElementById('profile-role').value  = data.role || '';
            document.getElementById('profile-email').value = data.email || '';
            bioInput.value = data.bio || '';

            // Snapshot for dirty checking
            original = { username: data.username || '', bio: data.bio || '' };
        } catch (err) {
            console.error('Failed to load profile:', err);
            showMessage('Failed to load profile.', true);
        }
    };

    loadProfile();

    // ─── Edit / Cancel ───────────────────────────────────────────
    editBtn.addEventListener('click', () => setEditMode(true));

    cancelBtn.addEventListener('click', () => {
        // Restore original values
        usernameInput.value = original.username;
        bioInput.value      = original.bio;
        setEditMode(false);
    });

    // ─── Save Profile (only send changed fields) ─────────────────
    document.getElementById('form-profile').addEventListener('submit', async function (e) {
        e.preventDefault();
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        const currentUsername = usernameInput.value.trim();
        const currentBio      = bioInput.value.trim();

        // Build payload with only changed fields
        const payload = {};
        if (currentUsername !== original.username) payload.username = currentUsername;
        if (currentBio      !== original.bio)      payload.bio      = currentBio;

        if (Object.keys(payload).length === 0) {
            showMessage('No changes to save.');
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Profile';
            return;
        }

        // Validate username if it changed
        if ('username' in payload && payload.username === '') {
            showMessage('Username cannot be empty.', true);
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Profile';
            return;
        }

        try {
            const result = await ProfileApi.updateProfile(payload);
            if (result.success) {
                // Update snapshot to reflect saved state
                if ('username' in payload) original.username = payload.username;
                if ('bio'      in payload) original.bio      = payload.bio;
                showMessage('Profile updated successfully.');
                setEditMode(false);
            } else {
                showMessage(`Error: ${result.error}`, true);
            }
        } catch (err) {
            showMessage('Failed to save profile.', true);
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Profile';
        }
    });
});
