<?php

namespace src\Actions\api;

use src\Actions\Action;
use src\Core\JWT;
use src\Core\JwtAuth;
use src\Responders\JsonResponder;

class GetRefreshAction implements Action
{

    public function execute(?string $param): void
    {
        $responder = new JsonResponder();

        if (!empty($param))
            $responder->send([
                'success' => false,
                'error' => 'Invalid endpoint',
            ], 404);

        $auth = new JwtAuth();
        $token = $_COOKIE['token'] ?? '';
        if (empty($token) || !$auth->verify(new JWT($token)))
            $responder->send([
                'success' => false,
                'error' => 'Invalid or missing refresh token',
            ], 401);

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