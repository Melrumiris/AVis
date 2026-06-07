<?php

use JetBrains\PhpStorm\NoReturn;

class ErrorResponder
{
    #[NoReturn]
    public function send(int $errorCode, string $errorMessage): void
    {
        header('Content-Type: text/html', true, $errorCode);
        $pageTemplate = ROOT . '/views/pages/error.php';
        require ROOT . '/views/layouts/auth.php';
        exit;
    }
}