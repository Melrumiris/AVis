<?php
/**
 * views/components/navbar.php
 *
 * Dynamic navbar: shows different links based on authentication state.
 * Reads $_COOKIE['token'] to determine auth status and role.
 * Includes hamburger menu toggle for mobile responsiveness.
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

    <!-- Hamburger toggle — visible only on mobile via CSS -->
    <button id="navbar-hamburger-btn" class="navbar-hamburger" aria-label="Toggle navigation menu" aria-expanded="false">
        <span class="hamburger-icon">☰</span>
    </button>

    <!-- Persistent actions: theme toggle + profile — always visible -->
    <div class="nav-persistent-actions">
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

    <!-- Collapsible nav links — full-width dropdown on mobile -->
    <ul id="navbar-links" class="navbar-links">
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
</nav>

<!-- Hamburger Menu Toggle Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var hamburgerBtn = document.getElementById('navbar-hamburger-btn');
    var navLinks = document.getElementById('navbar-links');

    if (hamburgerBtn && navLinks) {
        hamburgerBtn.addEventListener('click', function() {
            var isActive = navLinks.classList.toggle('active');
            hamburgerBtn.setAttribute('aria-expanded', isActive ? 'true' : 'false');
            // Swap icon: ☰ for open, ✕ for close
            hamburgerBtn.querySelector('.hamburger-icon').textContent = isActive ? '✕' : '☰';
        });

        // Close menu when a nav link is tapped (mobile UX)
        navLinks.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                navLinks.classList.remove('active');
                hamburgerBtn.setAttribute('aria-expanded', 'false');
                hamburgerBtn.querySelector('.hamburger-icon').textContent = '☰';
            });
        });
    }
});
</script>

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