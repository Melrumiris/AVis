<?php

declare(strict_types=1);

require_once ROOT . '/src/Core/JwtAuth.php';
require_once ROOT . '/src/Responders/HtmlResponder.php';
require_once ROOT . '/src/Responders/ErrorResponder.php';
require_once ROOT . '/src/Responders/RedirectResponder.php';

class ViewUploadAction implements Action
{
    public function execute(?string $param): void
    {
        $token = $_COOKIE['token'] ?? '';
        if (empty($token) || !(new JwtAuth())->verify(new JWT($token))) {
            (new RedirectResponder())->send('/login');
        }

        $parts = (new JWT($token))->split();
        $role  = $parts['payload']->role ?? '';

        if ($role !== 'admin') {
            (new ErrorResponder())->send(403, 'Forbidden: admin access required');
        }

        $viewPath     = ROOT . '/views/layouts/main.php';
        $pageTemplate = ROOT . '/views/pages/upload.php';

        (new HtmlResponder())->send($viewPath, $pageTemplate, 'Upload Data');
    }
}
