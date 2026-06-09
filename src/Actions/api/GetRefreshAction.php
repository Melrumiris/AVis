<?php

declare(strict_types=1);

require_once ROOT . '/src/Core/JwtAuth.php';
require_once ROOT . '/src/Core/UserRole.php';
require_once ROOT . '/src/Responders/JsonResponder.php';
require_once ROOT . '/src/Responders/ErrorResponder.php';

class GetRefreshAction implements Action
{

    public function execute(?string $param): void
    {
        if (!empty($param)) {
            (new ErrorResponder())->send(404, 'Invalid endpoint');
        }

        $auth = new JwtAuth();
        $token = $_COOKIE['token'] ?? '';
        if (empty($token) || !$auth->verify(new JWT($token))) {
            (new ErrorResponder())->send(401, 'Invalid or missing refresh token');
        }

        // Decode the refresh token to extract sub and role for access token generation
        $parts = (new JWT($token))->split();
        $sub  = $parts['payload']->sub ?? '';
        $roleStr = $parts['payload']->role ?? 'user';
        $role = UserRole::from($roleStr);

        $accessToken = $auth->generate($sub, $role, JwtType::Access);

        (new JsonResponder())->send([
            'success' => true,
            'message' => 'Access token retrieved',
            'data' => [
                'accessToken' => $accessToken->toString(),
            ],
        ], 200);
    }
}