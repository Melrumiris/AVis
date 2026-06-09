<?php

declare(strict_types=1);

require_once ROOT . '/src/Actions/Action.php';
require_once ROOT . '/src/Core/JwtAuth.php';
require_once ROOT . '/src/Domain/UserDomain.php';
require_once ROOT . '/src/Domain/GeminiDomain.php';
require_once ROOT . '/src/Domain/AccidentDomain.php';
require_once ROOT . '/src/Responders/JsonResponder.php';

class QueryNlpAction implements Action
{
    public function execute(?string $param = null): void
    {
        try {
            $jwtAuth = new JwtAuth();
            $payload = $jwtAuth->authenticateApiRequest();
            
            $rawPrompt = file_get_contents('php://input');
            if (empty(trim($rawPrompt))) {
                throw new InvalidArgumentException('Prompt cannot be empty.');
            }

            $userDomain = new UserDomain();
            $profile = $userDomain->getProfile($payload->sub);
            
            $context = [];
            if ($profile) {
                $context = [
                    'username' => $profile['username'],
                    'email' => $profile['email'],
                    'user_lat' => $profile['user_lat'],
                    'user_lng' => $profile['user_lng'],
                ];
            }

            $geminiDomain = new GeminiDomain();
            $sql = $geminiDomain->generateSql($rawPrompt, $context);

            $accidentDomain = new AccidentDomain();
            $results = $accidentDomain->executeRawSelect($sql);

            (new JsonResponder())->send([
                'success' => true,
                'query' => $sql,
                'data' => $results,
            ]);
            
        } catch (Throwable $e) {
            (new JsonResponder())->send([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
