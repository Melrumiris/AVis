<?php

declare(strict_types=1);

require_once ROOT . '/src/Domain/UserDomain.php';
require_once ROOT . '/src/Core/JwtAuth.php';
require_once ROOT . '/src/Responders/JsonResponder.php';
require_once ROOT . '/src/Responders/ErrorResponder.php';

class PostRegisterAction implements Action
{
    public function execute(?string $param): void
    {
        if (!empty($param)) {
            (new ErrorResponder())->send(404, 'Invalid endpoint');
        }

        $jsonInput = file_get_contents('php://input');
        $input = json_decode($jsonInput, true);

        $username = $input['username'] ?? null;
        $email    = $input['email'] ?? null;
        $password = $input['password'] ?? null;

        if (empty($username) || empty($email) || empty($password)) {
            (new ErrorResponder())->send(400, 'Empty username, email, or password');
        }

        try {
            $userDomain = new UserDomain();
            $result = $userDomain->createUser($username, $password, $email);

            if ($result) {
                $userId = $result['id'];
                $role   = $result['role'];

                $auth = new JwtAuth();
                $token = $auth->generate($userId, $role, JwtType::Refresh);

                $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

                setcookie('token', $token->toString(), [
                    'expires' => $auth->getRefreshExpiration(),
                    'path' => '/',
                    'domain' => '',
                    'secure' => $isSecure,
                    'httponly' => true,
                    'samesite' => 'Strict',
                ]);

                $accessToken = $auth->generate($userId, $role, JwtType::Access);

                (new JsonResponder())->send([
                    'success' => true,
                    'message' => 'User registered successfully',
                    'data' => [
                        'token' => $accessToken->toString(),
                    ],
                ], 201);
            } else {
                (new ErrorResponder())->send(409, 'Email already exists');
            }
        } catch (PDOException $e) {
            (new ErrorResponder())->send(500, $e->getMessage());
        }
    }
}