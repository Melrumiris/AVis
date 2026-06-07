<?php

use JetBrains\PhpStorm\NoReturn;

class ErrorResponder
{
    #[NoReturn]
    public function send(int $errorCode, string $errorMessage): void
    {
        $isJson = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');
        
        if ($isJson) {
            header('Content-Type: application/json', true, $errorCode);
            echo json_encode([
                'success' => false,
                'error' => $errorMessage,
            ]);
            exit;
        }

        header('Content-Type: text/html', true, $errorCode);
        $title = 'Error';
        $pageTemplate = ROOT . '/views/pages/error.php';
        require ROOT . '/views/layouts/auth.php';
        exit;
    }
}