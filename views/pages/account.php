<?php
/**
 * views/pages/account.php
 *
 * Account / Profile page fragment — injected into views/layouts/main.php.
 * Allows users to view and update their username and bio.
 * All data fetching is done client-side via ApiHandler.js → /api/v0/profile.
 */
?>

<section id="account-page" class="page-section">
    <h1 class="page-title">My Account</h1>

    <div id="profile-panel" class="account-panel island-card">

        <form id="form-profile" novalidate>
            <div class="form-group">
                <label for="profile-username" class="form-label">Username</label>
                <input type="text" id="profile-username" name="username" class="form-control" placeholder="Your username" disabled>
            </div>

            <div class="form-group">
                <label for="profile-email" class="form-label">Email</label>
                <input type="email" id="profile-email" name="email" class="form-control" placeholder="your@email.com" readonly disabled>
            </div>

            <div class="form-group">
                <label for="profile-role" class="form-label">Role</label>
                <input type="text" id="profile-role" name="role" class="form-control" placeholder="User role" readonly disabled>
            </div>

            <div class="form-group">
                <label for="profile-bio" class="form-label">Bio</label>
                <textarea id="profile-bio" name="bio" rows="4" class="form-control" placeholder="Tell us about yourself..." disabled></textarea>
            </div>

            <div style="display: flex; gap: var(--spacing-md); align-items: center;">
                <button type="button" id="btn-edit-profile" class="btn btn-primary">
                    Edit Profile
                </button>
                <button type="submit" id="btn-save-profile" class="btn btn-primary" style="display: none;">
                    Save Profile
                </button>
                <button type="button" id="btn-cancel-profile" class="btn btn-secondary" style="display: none;">
                    Cancel
                </button>
            </div>

            <p id="msg-profile" class="form-message" role="status" aria-live="polite"></p>
        </form>

    </div>
</section>

<script src="/js/api/ProfileApi.js"></script>
<script src="/js/ui/accountDom.js"></script>
