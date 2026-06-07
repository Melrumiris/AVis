<?php

declare(strict_types=1);

require_once ROOT . '/src/Domain/AccidentDomain.php';
require_once ROOT . '/src/Core/JwtAuth.php';
require_once ROOT . '/src/Responders/JsonResponder.php';
require_once ROOT . '/src/Responders/ErrorResponder.php';

class GetMapDataAction implements Action
{
    public function execute(?string $param): void
    {
        if (!empty($param)) {
            (new ErrorResponder())->send(404, 'Invalid endpoint');
        }

        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($authHeader, 'Bearer ')) {
            (new ErrorResponder())->send(401, 'Missing or malformed Authorization header');
        }

        $rawToken = substr($authHeader, 7);
        if (!(new JwtAuth())->verify(new JWT($rawToken))) {
            (new ErrorResponder())->send(401, 'Invalid or expired access token');
        }

        $sdate = trim($_GET['sdate'] ?? '');
        $fdate = trim($_GET['fdate'] ?? '');

        if ($sdate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $sdate)) {
            (new ErrorResponder())->send(400, 'Invalid sdate format. Expected YYYY-MM-DD');
        }
        if ($fdate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fdate)) {
            (new ErrorResponder())->send(400, 'Invalid fdate format. Expected YYYY-MM-DD');
        }

        try {
            $domain = new AccidentDomain();
            $points = $domain->getMapPoints($sdate, $fdate);
        } catch (PDOException $e) {
            (new ErrorResponder())->send(500, 'Database error: ' . $e->getMessage());
        }

        (new JsonResponder())->send([
            'success' => true,
            'data'    => $points,
        ]);
    }
}
