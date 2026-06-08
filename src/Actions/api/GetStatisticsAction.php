<?php

declare(strict_types=1);

require_once ROOT . '/src/Domain/AccidentDomain.php';
require_once ROOT . '/src/Core/JwtAuth.php';
require_once ROOT . '/src/Responders/JsonResponder.php';
require_once ROOT . '/src/Responders/ErrorResponder.php';

class GetStatisticsAction implements Action
{
    private const ALLOWED_GROUP_BY = ['severity', 'year', 'month', 'day', 'location'];
    private const ALLOWED_SEVERITY = ['ALL', '1', '2', '3', '4'];
    private const ALLOWED_REGION = ['ALL', 'NE', 'NW', 'SE', 'SW'];

    public function execute(?string $param): void
    {
        if (!empty($param)) {
            (new ErrorResponder())->send(404, 'Invalid endpoint');
        }

        $payload = (new JwtAuth())->authenticateApiRequest();

        $sdate    = trim($_GET['sdate']    ?? '');
        $fdate    = trim($_GET['fdate']    ?? '');
        $severity = trim($_GET['severity'] ?? 'ALL');
        $region   = trim($_GET['region']   ?? 'ALL');
        $groupBy  = trim($_GET['group_by'] ?? 'severity');

        if ($sdate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $sdate)) {
            (new ErrorResponder())->send(400, 'Invalid sdate format. Expected YYYY-MM-DD');
        }
        if ($fdate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fdate)) {
            (new ErrorResponder())->send(400, 'Invalid fdate format. Expected YYYY-MM-DD');
        }
        if (!in_array($severity, self::ALLOWED_SEVERITY, true)) {
            (new ErrorResponder())->send(400, 'Invalid severity value');
        }
        if (!in_array($region, self::ALLOWED_REGION, true)) {
            (new ErrorResponder())->send(400, 'Invalid region value');
        }
        if (!in_array($groupBy, self::ALLOWED_GROUP_BY, true)) {
            (new ErrorResponder())->send(400, 'Invalid group_by value');
        }

        try {
            $domain  = new AccidentDomain();
            $results = $domain->getStatistics($sdate, $fdate, $severity, $region, $groupBy);
        } catch (PDOException $e) {
            (new ErrorResponder())->send(500, 'Database error: ' . $e->getMessage());
        }

        (new JsonResponder())->send([
            'success' => true,
            'data'    => $results,
        ]);
    }
}
