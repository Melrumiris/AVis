<?php

namespace src\Responders;

use JetBrains\PhpStorm\NoReturn;

class JsonResponder
{
    #[NoReturn]
    public function send(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json', true, $statusCode);
        echo json_encode($data);
        exit;
    }
}