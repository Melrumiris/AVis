<?php
/**
 * views/pages/about.php
 *
 * Public landing page fragment — injected into views/layouts/main.php.
 */
?>

<link rel="alternate" type="application/rss+xml" title="AVis Recent Accidents Feed" href="/rss">
<section id="about-page" class="page-section" style="max-width: 800px; margin: 0 auto; text-align: center;">
    <h1 class="page-title" style="font-size: 3rem; margin-bottom: 1rem;">Welcome to AVis</h1>
    <h2 style="color: var(--text-muted); font-weight: 400; margin-bottom: 2rem;">Accident Visualizer Dashboard</h2>

    <div class="island-card" style="margin-bottom: 2rem; text-align: left;">
        <h3 style="margin-bottom: 1rem;">About the Project</h3>
        <p style="color: var(--text-muted); line-height: 1.6; margin-bottom: 1.5rem;">
            AVis is a high-performance analytical dashboard designed to explore and visualize large-scale traffic accident data.
            It provides interactive maps, comprehensive statistical charts, and detailed data reports with multiple export options.
        </p>

        <h3 style="margin-bottom: 1rem;">Data Attribution</h3>
        <p style="color: var(--text-muted); line-height: 1.6; margin-bottom: 0;">
            This project utilizes the <strong>"US Accidents" dataset</strong> from Kaggle, containing millions of traffic accident records.
            We extend our gratitude to the dataset creators and contributors for making this data publicly available for research and analysis.
        </p>
    </div>

    <?php if (!$isAuthenticated): ?>
    <p style="margin-top: 2rem; font-size: 1.1rem; color: var(--text-muted);">
        Ready to explore the data?
        <a href="/register" style="
            color: var(--color-primary);
            font-weight: 700;
            text-decoration: underline;
            text-underline-offset: 3px;
            padding: 0.1em 0.3em;
            border-radius: var(--radius-sm);
            background-color: var(--color-primary-light);
            transition: background-color 0.2s ease, color 0.2s ease;
        " onmouseover="this.style.backgroundColor='var(--color-primary)';this.style.color='var(--text-inverse)';"
           onmouseout="this.style.backgroundColor='var(--color-primary-light)';this.style.color='var(--color-primary)';">
            Get started →
        </a>
    </p>
    <?php endif; ?>

</section>
