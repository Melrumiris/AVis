/**
 * public/js/ui/theme.js
 * Handles dark/light mode toggling.
 * Default: OS preference (prefers-color-scheme).
 * Override: sessionStorage for current session only.
 */

(function () {
    // Apply theme immediately (avoids FOUC on deferred load too)
    const sessionTheme = sessionStorage.getItem('avis_theme');
    const osDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const theme = sessionTheme ?? (osDark ? 'dark' : 'light');
    document.documentElement.setAttribute('data-theme', theme);
})();

document.addEventListener('DOMContentLoaded', () => {
    const themeToggleBtn = document.getElementById('theme-toggle-btn');
    if (!themeToggleBtn) return;

    const updateIcon = () => {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        themeToggleBtn.innerHTML = isDark ? '☀️' : '🌙';
    };

    updateIcon();

    themeToggleBtn.addEventListener('click', () => {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const newTheme = isDark ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        sessionStorage.setItem('avis_theme', newTheme);
        updateIcon();
    });
});
