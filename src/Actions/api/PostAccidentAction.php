<?php

declare(strict_types=1);

require_once ROOT . '/src/Domain/AccidentDomain.php';
require_once ROOT . '/src/Core/JwtAuth.php';
require_once ROOT . '/src/Responders/JsonResponder.php';
require_once ROOT . '/src/Responders/ErrorResponder.php';

class PostAccidentAction implements Action
{
    public function execute(?string $param): void
    {
        if (!empty($param)) {
            (new ErrorResponder())->send(404, 'Invalid endpoint');
        }

        $payload = (new JwtAuth())->authenticateAdminRequest();

        $jsonInput = file_get_contents('php://input');
        $input     = json_decode($jsonInput, true);

        $dateTime  = trim($input['date_time']   ?? '');
        $severity  = $input['severity']        ?? null;
        $lat       = $input['latitude']        ?? null;
        $lng       = $input['longitude']       ?? null;
        $state     = trim($input['state']          ?? '');

        if (empty($dateTime) || $severity === null || $lat === null || $lng === null) {
            (new ErrorResponder())->send(400, 'Missing required fields: date_time, severity, latitude, longitude');
        }

        if (!is_numeric($severity) || (int) $severity < 1 || (int) $severity > 4) {
            (new ErrorResponder())->send(400, 'Invalid severity: must be an integer between 1 and 4');
        }

        if (!is_numeric($lat) || !is_numeric($lng)) {
            (new ErrorResponder())->send(400, 'Invalid latitude or longitude: must be numeric');
        }

        if ($state !== '' && strlen($state) !== 2) {
            (new ErrorResponder())->send(400, 'Invalid state: must be a 2-letter US state code');
        }

        try {
            $domain  = new AccidentDomain();
            $success = $domain->insertAccident(
                $dateTime,
                (int) $severity,
                (float) $lat,
                (float) $lng,
                $state !== '' ? strtoupper($state) : null
            );
        } catch (PDOException $e) {
            (new ErrorResponder())->send(500, 'Database error: ' . $e->getMessage());
        }

        if (!$success) {
            (new ErrorResponder())->send(500, 'Failed to insert accident record');
        }

        (new JsonResponder())->send([
            'success' => true,
            'data'    => ['inserted' => 1],
        ], 201);
    }
}
