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

        if (!is_array($input) || empty($input)) {
            (new ErrorResponder())->send(400, 'Invalid or empty JSON body');
        }

        // Validate what's present — at least one field required
        $hasUsername   = array_key_exists('username', $input);
        $hasBio        = array_key_exists('bio', $input);
        $hasProfilePic = array_key_exists('profile_pic', $input);
        $hasUserLat    = array_key_exists('user_lat', $input);
        $hasUserLng    = array_key_exists('user_lng', $input);

        if (!$hasUsername && !$hasBio && !$hasProfilePic && !$hasUserLat && !$hasUserLng) {
            (new ErrorResponder())->send(400, 'No updatable fields provided');
        }

        if ($hasUsername) {
            $username = trim((string)$input['username']);
            if ($username === '') {
                (new ErrorResponder())->send(400, 'Username cannot be empty');
            }
            if (strlen($username) > 100) {
                (new ErrorResponder())->send(400, 'Username cannot exceed 100 characters');
            }
        }

        if ($hasProfilePic && $input['profile_pic'] !== null && strlen((string)$input['profile_pic']) > 500) {
            (new ErrorResponder())->send(400, 'Profile picture URL cannot exceed 500 characters');
        }

        try {
            $userDomain = new UserDomain();
            $success = $userDomain->updateProfilePartial(
                $payload->sub,
                $hasUsername   ? trim((string)$input['username']) : null,
                $hasBio        ? trim((string)$input['bio'])      : null,
                $hasProfilePic ? ($input['profile_pic'] !== null ? trim((string)$input['profile_pic']) : '') : null,
                $hasUserLat    ? ($input['user_lat'] !== null ? (float)$input['user_lat'] : 0.0) : null,
                $hasUserLng    ? ($input['user_lng'] !== null ? (float)$input['user_lng'] : 0.0) : null
            );
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
