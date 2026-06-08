<?php

declare(strict_types=1);

require_once ROOT . '/src/Core/JwtAuth.php';
require_once ROOT . '/src/Actions/Action.php';
require_once ROOT . '/src/Responders/HtmlResponder.php';

/**
 * ViewIndexAction — handles the root `/` route.
 *
 * Renders the home dashboard for authenticated users.
 * Shows the About/landing page for unauthenticated visitors.
 */
class ViewIndexAction implements Action
{
    public function execute(?string $param): void
    {
        $token = $_COOKIE['token'] ?? '';
        if (!empty($token) && (new JwtAuth())->verify(new JWT($token))) {
            (new HtmlResponder())->send(
                ROOT . '/views/layouts/main.php',
                ROOT . '/views/pages/home.php',
                'Home'
            );
            return;
        }

        (new HtmlResponder())->send(
            ROOT . '/views/layouts/main.php',
            ROOT . '/views/pages/about.php',
            'AVis — Accident Visualizer'
        );
    }
}
