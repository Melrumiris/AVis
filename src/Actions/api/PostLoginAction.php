<?php

declare(strict_types=1);

require_once ROOT . '/src/Domain/UserDomain.php';
require_once ROOT . '/src/Core/JwtAuth.php';
require_once ROOT . '/src/Responders/JsonResponder.php';
require_once ROOT . '/src/Responders/ErrorResponder.php';

class PostLoginAction implements Action
{
    public function execute(?string $param): void
    {
        if (!empty($param)) {
            (new ErrorResponder())->send(404, 'Invalid endpoint');
        }

        $jsonInput = file_get_contents('php://input');
        $input = json_decode($jsonInput, true);

        $email    = $input['email'] ?? null;
        $password = $input['password'] ?? null;

        if (empty($email) || empty($password)) {
            (new ErrorResponder())->send(400, 'Empty email or password');
        }

        try {
            $userDomain = new UserDomain();
            $result = $userDomain->verifyUser($email, $password);

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
                    'message' => 'Logged in successfully',
                    'data' => [
                        'token' => $accessToken->toString(),
                    ],
                ], 201);
            } else {
                (new ErrorResponder())->send(400, 'Invalid username or password');
            }
        } catch (PDOException $e) {
            (new ErrorResponder())->send(500, $e->getMessage());
        }
    }
}