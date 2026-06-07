<?php

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
            (new ErrorResponder())->send(404, 'Invalid endpoint');

        $jsonInput = file_get_contents('php://input');
        $input = json_decode($jsonInput, true);

        $username = $input['username'] ?? null;
        $password = $input['password'] ?? null;

        if (empty($username) || empty($password))
            (new ErrorResponder())->send(400, 'Empty username or password');

        try {
            $userDomain = new UserDomain();
            $result = $userDomain->verifyUser($username, $password);

            if ($result) {
                $auth = new JwtAuth();
                $token = $auth->generate($result, 'user', JwtType::Refresh);

                $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

                setcookie('token', $token->toString(), [
                    'expires' => $auth->getRefreshExpiration(),
                    'path' => '/',
                    'domain' => '',
                    'secure' => $isSecure,
                    'httponly' => true,
                    'samesite' => 'Strict',
                ]);

                $accessToken = $auth->generate($result, 'user', JwtType::Access);

                $data = [
                    'token' => $accessToken->toString(),
                ];

                $responder->send([
                    'success' => true,
                    'message' => 'Logged in successfully',
                    'data' => $data,
                ], 201);
            } else {
                (new ErrorResponder())->send(400, 'Invalid username or password');
            }
        } catch (PDOException $e) {
            (new ErrorResponder())->send(500, $e->getMessage());
        }
    }
}