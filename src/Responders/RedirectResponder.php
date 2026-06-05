<?php

namespace src\Responders;

use JetBrains\PhpStorm\NoReturn;

class RedirectResponder
{
    #[NoReturn]
    public function send(string $url, int $statusCode = 302): void
    {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }
}