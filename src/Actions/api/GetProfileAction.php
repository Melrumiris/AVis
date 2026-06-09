<?php

declare(strict_types=1);

require_once ROOT . '/src/Domain/UserDomain.php';
require_once ROOT . '/src/Core/JwtAuth.php';
require_once ROOT . '/src/Responders/JsonResponder.php';
require_once ROOT . '/src/Responders/ErrorResponder.php';

class GetProfileAction implements Action
{
    public function execute(?string $param): void
    {
        if (!empty($param)) {
            (new ErrorResponder())->send(404, 'Invalid endpoint');
        }

        $payload = (new JwtAuth())->authenticateApiRequest();

        try {
            $userDomain = new UserDomain();
            $profile = $userDomain->getProfile($payload->sub);
        } catch (PDOException $e) {
            (new ErrorResponder())->send(500, 'Database error: ' . $e->getMessage());
        }

        if (!$profile) {
            (new ErrorResponder())->send(404, 'User not found');
        }

        (new JsonResponder())->send([
            'success' => true,
            'data'    => $profile,
        ]);
    }
}
