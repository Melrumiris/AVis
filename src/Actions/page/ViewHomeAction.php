<?php

require_once ROOT . '/src/Core/JwtAuth.php';
require_once ROOT . '/src/Responders/HtmlResponder.php';
require_once ROOT . '/src/Responders/JsonResponder.php';
require_once ROOT . '/src/Responders/ErrorResponder.php';
require_once ROOT . '/src/Responders/RedirectResponder.php';

class ViewHomeAction implements Action
{

    public function execute(?string $param): void
    {
        $token = $_COOKIE['token'] ?? '';
        if (empty($token) || !(new JwtAuth())->verify(new JWT($token))) (new RedirectResponder())->send('/login');

        $viewPath = ROOT . '/views/layouts/main.php';
        $pageTemplate = ROOT . '/views/pages/home.php';
        (new HtmlResponder())->send($viewPath, $pageTemplate, 'Home');
    }
}