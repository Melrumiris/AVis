<?php

namespace src\Responders;

use JetBrains\PhpStorm\NoReturn;

class HtmlResponder
{
    #[NoReturn]
    public function send(string $viewPath, string $pageTemplate, string $title, array $data = []): void
    {
        extract($data);

        if (file_exists($pageTemplate) && file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            new ErrorResponder()->send(501, 'Not Implemented: view page not found');
        }
        exit;
    }
}