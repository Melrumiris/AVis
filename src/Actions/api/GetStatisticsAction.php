<?php

declare(strict_types=1);

require_once ROOT . '/src/Domain/AccidentDomain.php';
require_once ROOT . '/src/Core/JwtAuth.php';
require_once ROOT . '/src/Responders/JsonResponder.php';
require_once ROOT . '/src/Responders/ErrorResponder.php';

class GetStatisticsAction implements Action
{
    private const ALLOWED_GROUP_BY = ['severity', 'year', 'month', 'day', 'location', 'city', 'county', 'weather_condition', 'sunrise_sunset'];
    private const ALLOWED_REGION = ['ALL', 'NE', 'NW', 'SE', 'SW'];
    private const ALLOWED_FILTERS = [
        'sdate', 'fdate', 'severity', 'region', 'city', 'county', 
        'weather_condition', 'temperature', 'visibility', 
        'crossing', 'junction', 'traffic_signal', 'sunrise_sunset'
    ];

    public function execute(?string $param): void
    {
        if (!empty($param)) {
            (new ErrorResponder())->send(404, 'Invalid endpoint');
        }

        $payload = (new JwtAuth())->authenticateApiRequest();

        $filters = [];
        foreach (self::ALLOWED_FILTERS as $key) {
            if (isset($_GET[$key]) && $_GET[$key] !== '') {
                $filters[$key] = trim((string)$_GET[$key]);
            }
        }

        $groupBy = trim((string)($_GET['group_by'] ?? 'severity'));

        if (!empty($filters['sdate']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['sdate'])) {
            (new ErrorResponder())->send(400, 'Invalid sdate format. Expected YYYY-MM-DD');
        }
        if (!empty($filters['fdate']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['fdate'])) {
            (new ErrorResponder())->send(400, 'Invalid fdate format. Expected YYYY-MM-DD');
        }
        if (!empty($filters['region']) && !in_array($filters['region'], self::ALLOWED_REGION, true)) {
            (new ErrorResponder())->send(400, 'Invalid region value');
        }
        if (!in_array($groupBy, self::ALLOWED_GROUP_BY, true)) {
            (new ErrorResponder())->send(400, 'Invalid group_by value');
        }

        try {
            $domain  = new AccidentDomain();
            $results = $domain->getStatistics($filters, $groupBy);
        } catch (PDOException $e) {
            (new ErrorResponder())->send(500, 'Database error: ' . $e->getMessage());
        }

        (new JsonResponder())->send([
            'success' => true,
            'data'    => $results,
        ]);
    }
}
