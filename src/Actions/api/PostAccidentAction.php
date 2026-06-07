<?php

declare(strict_types=1);

require_once ROOT . '/src/Domain/AccidentDomain.php';
require_once ROOT . '/src/Core/JwtAuth.php';
require_once ROOT . '/src/Responders/JsonResponder.php';
require_once ROOT . '/src/Responders/ErrorResponder.php';
require_once ROOT . '/src/Core/RouteNode.php';

class PostAccidentAction extends RouteNode
{
    public function __construct()
    {
        parent::__construct(null, $this);
    }

    public function execute(?string $param): void
    {
        if (!empty($param)) {
            $param = explode('/', $param, 2);
            $this->routes[$param[0]]->execute($param[1] ?? null);
            return;
        }

        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($authHeader, 'Bearer ')) {
            (new ErrorResponder())->send(401, 'Missing or malformed Authorization header');
        }

        $rawToken = substr($authHeader, 7);
        $auth     = new JwtAuth();

        if (!$auth->verify(new JWT($rawToken))) {
            (new ErrorResponder())->send(401, 'Invalid or expired access token');
        }

        $parts = (new JWT($rawToken))->split();
        $role  = $parts['payload']->role ?? '';

        if ($role !== 'admin') {
            (new ErrorResponder())->send(403, 'Forbidden: admin access required');
        }

        $jsonInput = file_get_contents('php://input');
        $input     = json_decode($jsonInput, true);

        $dataOra    = trim($input['data_ora']    ?? '');
        $severity   = $input['severitate']       ?? null;
        $lat        = $input['latitudine']        ?? null;
        $lng        = $input['longitudine']       ?? null;

        if (empty($dataOra) || $severity === null || $lat === null || $lng === null) {
            (new ErrorResponder())->send(400, 'Missing required fields: data_ora, severitate, latitudine, longitudine');
        }

        if (!is_numeric($severity) || (int) $severity < 1 || (int) $severity > 4) {
            (new ErrorResponder())->send(400, 'Invalid severitate: must be an integer between 1 and 4');
        }

        if (!is_numeric($lat) || !is_numeric($lng)) {
            (new ErrorResponder())->send(400, 'Invalid latitudine or longitudine: must be numeric');
        }

        try {
            $domain  = new AccidentDomain();
            $success = $domain->insertAccident(
                $dataOra,
                (int) $severity,
                (float) $lat,
                (float) $lng
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
