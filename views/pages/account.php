<?php
/**
 * views/pages/account.php
 *
 * Account / Profile page fragment — injected into views/layouts/main.php.
 * Allows users to view and update their email and bio.
 * All data fetching is done client-side via ApiHandler.js → /api/v0/profile.
 */
?>

<section id="account-page" class="page-section">
    <h1 class="page-title">My Account</h1>

    <div id="profile-panel" class="account-panel">

        <div class="profile-info" id="profile-display">
            <div class="form-group">
                <label>Username</label>
                <p id="profile-username" class="profile-value">—</p>
            </div>

            <div class="form-group">
                <label>Role</label>
                <p id="profile-role" class="profile-value">—</p>
            </div>
        </div>

        <form id="form-profile" novalidate>
            <div class="form-group">
                <label for="profile-email">Email</label>
                <input type="email" id="profile-email" name="email" placeholder="your@email.com">
            </div>

            <div class="form-group">
                <label for="profile-bio">Bio</label>
                <textarea id="profile-bio" name="bio" rows="4" placeholder="Tell us about yourself..."></textarea>
            </div>

            <button type="submit" id="btn-save-profile" class="btn btn-primary">
                Save Profile
            </button>

            <p id="msg-profile" class="form-message" role="status" aria-live="polite"></p>
        </form>

    </div>
</section>

<script src="/js/api/ProfileApi.js"></script>
<script src="/js/ui/accountDom.js"></script>
