<?php

require_once ROOT . '/src/Responders/JsonResponder.php';
require_once ROOT . '/src/Responders/ErrorResponder.php';

class GetRefreshAction implements Action
{

    public function execute(?string $param): void
    {
        $responder = new JsonResponder();

        if (!empty($param))
            (new ErrorResponder())->send(404, 'Invalid endpoint');

        $auth = new JwtAuth();
        $token = $_COOKIE['token'] ?? '';
        if (empty($token) || !$auth->verify(new JWT($token)))
            (new ErrorResponder())->send(401, 'Invalid or missing refresh token');

        $data = [
            'token' => $token,
        ];

        $responder->send([
            'success' => true,
            'message' => 'Access token retrieved',
            'data' => $data,

        ], 200);
    }
}