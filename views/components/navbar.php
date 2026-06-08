<?php
/**
 * views/components/navbar.php
 *
 * Dynamic navbar: shows different links based on authentication state.
 * Reads $_COOKIE['token'] to determine auth status and role.
 */

$isAuthenticated = false;
$userRole = '';
$userInitial = 'U';

$navToken = $_COOKIE['token'] ?? '';
if (!empty($navToken)) {
    $tokenParts = explode('.', $navToken);
    if (count($tokenParts) === 3) {
        $payloadJson = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
        $navPayload = json_decode($payloadJson);
        if ($navPayload && isset($navPayload->exp) && $navPayload->exp > time()) {
            $isAuthenticated = true;
            $userRole = $navPayload->role ?? '';
            if (isset($navPayload->email)) {
                $userInitial = strtoupper(substr($navPayload->email, 0, 1));
            }
        }
    }
}
?>

<nav id="main-navbar" class="navbar">
    <a href="/" class="navbar-brand">AVis</a>
    <div class="nav-right-actions">
        <ul class="navbar-links">
            <?php if ($isAuthenticated): ?>
                <li><a href="/about" class="nav-link">About</a></li>
                <li><a href="/home" class="nav-link">Home</a></li>
                <?php if ($userRole === 'admin'): ?>
                    <li><a href="/admin" class="nav-link">Admin</a></li>
                <?php endif; ?>
                <li><a href="#" id="btn-logout" class="nav-link" onclick="return false;" style="color: var(--color-error);">Logout</a></li>
            <?php else: ?>
                <li><a href="/about" class="nav-link">About</a></li>
                <li><a href="/login" class="nav-link">Login</a></li>
                <li><a href="/register" class="btn btn-primary">Register</a></li>
            <?php endif; ?>
        </ul>
        
        <button id="theme-toggle-btn" class="theme-toggle" aria-label="Toggle theme">
            <script>
                document.write(document.documentElement.getAttribute('data-theme') === 'dark' ? '☀️' : '🌙');
            </script>
        </button>

        <?php if ($isAuthenticated): ?>
            <a href="/account" class="profile-pic" aria-label="My Account" title="My Account">
                <?= htmlspecialchars($userInitial) ?>
            </a>
        <?php endif; ?>
    </div>
</nav>

<?php if ($isAuthenticated): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var logoutBtn = document.getElementById('btn-logout');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            try {
                await fetch('/api/v0/auth/logout', {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json' }
                });
            } catch (err) {
                // Ignore network errors — we clear state anyway
            }
            if (typeof ApiHandler !== 'undefined') {
                ApiHandler.clearAuth();
            }
            window.location.href = '/login';
        });
    }
});
</script>
<?php endif; ?>