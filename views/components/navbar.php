<?php
/**
 * views/components/navbar.php
 *
 * Dynamic navbar: shows different links based on authentication state.
 * Reads $_COOKIE['token'] to determine auth status and role.
 */

$isAuthenticated = false;
$userRole = '';

$navToken = $_COOKIE['token'] ?? '';
if (!empty($navToken)) {
    $tokenParts = explode('.', $navToken);
    if (count($tokenParts) === 3) {
        $payloadJson = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
        $navPayload = json_decode($payloadJson);
        if ($navPayload && isset($navPayload->exp) && $navPayload->exp > time()) {
            $isAuthenticated = true;
            $userRole = $navPayload->role ?? '';
        }
    }
}
?>

<nav id="main-navbar" class="navbar" style="position: sticky; top: 0; z-index: 1000; background: white; border-bottom: 1px solid #e2e8f0; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center;">
    <a href="/" class="navbar-brand" style="font-weight: bold; font-size: 1.25rem; color: #0f172a; text-decoration: none;">AVis</a>
    <ul class="navbar-links" style="list-style: none; display: flex; gap: 1.5rem; margin: 0; padding: 0;">
        <?php if ($isAuthenticated): ?>
            <li><a href="/about" style="color: #475569; text-decoration: none;">About</a></li>
            <li><a href="/home" style="color: #475569; text-decoration: none;">Home</a></li>
            <li><a href="/account" style="color: #475569; text-decoration: none;">Account</a></li>
            <?php if ($userRole === 'admin'): ?>
                <li><a href="/admin" style="color: #475569; text-decoration: none;">Admin</a></li>
            <?php endif; ?>
            <li><a href="#" id="btn-logout" onclick="return false;" style="color: #e53e3e; text-decoration: none;">Logout</a></li>
        <?php else: ?>
            <li><a href="/about" style="color: #475569; text-decoration: none;">About</a></li>
            <li><a href="/login" style="color: #475569; text-decoration: none;">Login</a></li>
            <li><a href="/register" style="color: #2563eb; font-weight: bold; text-decoration: none;">Register</a></li>
        <?php endif; ?>
    </ul>
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