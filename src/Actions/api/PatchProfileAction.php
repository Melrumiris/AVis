<?php

declare(strict_types=1);

require_once ROOT . '/src/Domain/UserDomain.php';
require_once ROOT . '/src/Core/JwtAuth.php';
require_once ROOT . '/src/Responders/JsonResponder.php';
require_once ROOT . '/src/Responders/ErrorResponder.php';

class PatchProfileAction implements Action
{
    public function execute(?string $param): void
    {
        if (!empty($param)) {
            (new ErrorResponder())->send(404, 'Invalid endpoint');
        }

        $payload = (new JwtAuth())->authenticateApiRequest();

        $jsonInput = file_get_contents('php://input');
        $input = json_decode($jsonInput, true);

        if (!is_array($input)) {
            (new ErrorResponder())->send(400, 'Invalid JSON body');
        }

        $email = trim($input['email'] ?? '');
        $bio   = trim($input['bio']   ?? '');

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            (new ErrorResponder())->send(400, 'Invalid email address');
        }

        try {
            $userDomain = new UserDomain();
            $success = $userDomain->updateProfile($payload->sub, $email, $bio);
        } catch (PDOException $e) {
            (new ErrorResponder())->send(500, 'Database error: ' . $e->getMessage());
        }

        if (!$success) {
            (new ErrorResponder())->send(500, 'Failed to update profile');
        }

        (new JsonResponder())->send([
            'success' => true,
            'message' => 'Profile updated successfully',
        ]);
    }
}
