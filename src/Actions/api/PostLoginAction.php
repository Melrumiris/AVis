<?php

namespace src\Actions\api;

use src\Actions\Action;
use src\Core\JwtAuth;
use src\Core\JwtType;
use src\Domain\UserDomain;
use src\Responders\JsonResponder;

require_once ROOT . '/src/Domain/UserDomain.php';
require_once ROOT . '/src/Core/JwtAuth.php';
require_once ROOT . '/src/Responders/HtmlResponder.php';
require_once ROOT . '/src/Responders/JsonResponder.php';
require_once ROOT . '/src/Responders/ErrorResponder.php';
require_once ROOT . '/src/Responders/RedirectResponder.php';

class PostLoginAction implements Action
{
    public function execute(?string $param): void
    {
        $responder = new JsonResponder();

        if (!empty($param))
            $responder->send([
                'success' => false,
                'error' => 'Invalid endpoint',
            ], 404);

        $jsonInput = file_get_contents('php://input');
        extract(json_decode($jsonInput, true));

        if (empty($username) || empty($password))
            $responder->send([
                'success' => false,
                'error' => 'Empty username or password',
            ], 400);

        try {
            $userDomain = new UserDomain();
            $result = $userDomain->verifyUser($username, $password);

            if ($result) {
                $auth = new JwtAuth();
                $token = $auth->generate($result, 'user', JwtType::Refresh);
                setcookie('token', $token->toString(), [
                    'httponly' => true,
                    'samesite' => 'Strict',
                    'secure' => true,
                    'expires' => $auth->getRefreshExpiration(),
                ]);
                $data = [
                    'token' => $token->toString(),
                ];

                $responder->send([
                    'success' => true,
                    'message' => 'Logged in successfully',
                    'data' => $data,
                ], 201);
            } else {
                $responder->send([
                    'success' => false,
                    'error' => 'Invalid username or password',
                ], 400);
            }
        } catch (
        PDOException $e
        ) {
            $responder->send([
                'success' => false,
                'error' => 'Database error',
            ], 500);
        }
    }
}