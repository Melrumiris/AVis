<?php

require_once ROOT . '/src/Responders/HtmlResponder.php';
require_once ROOT . '/src/Responders/JsonResponder.php';
require_once ROOT . '/src/Responders/ErrorResponder.php';
require_once ROOT . '/src/Responders/RedirectResponder.php';

class ViewLoginAction implements Action
{
    public function execute(?string $param): void
    {
        $viewPath = ROOT . '/views/layouts/auth.php';
        $pageTemplate = ROOT . '/views/pages/login.php';
        (new HtmlResponder())->send($viewPath, $pageTemplate, 'Login');
    }
}