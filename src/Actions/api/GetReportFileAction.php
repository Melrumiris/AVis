<?php

declare(strict_types=1);

require_once ROOT . '/src/Domain/AccidentDomain.php';
require_once ROOT . '/src/Core/JwtAuth.php';
require_once ROOT . '/src/Responders/FileResponder.php';
require_once ROOT . '/src/Responders/ErrorResponder.php';

class GetReportFileAction implements Action
{
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

        // Use cookie-based auth because window.location.href cannot send Authorization headers
        $token = $_COOKIE['token'] ?? '';
        if (empty($token) || !(new JwtAuth())->verify(new JWT($token))) {
            (new ErrorResponder())->send(401, 'Authentication required');
        }

        $filters = [];
        foreach (self::ALLOWED_FILTERS as $key) {
            if (isset($_GET[$key]) && $_GET[$key] !== '') {
                $filters[$key] = trim((string)$_GET[$key]);
            }
        }

        if (!empty($filters['sdate']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['sdate'])) {
            (new ErrorResponder())->send(400, 'Invalid sdate format. Expected YYYY-MM-DD');
        }
        if (!empty($filters['fdate']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['fdate'])) {
            (new ErrorResponder())->send(400, 'Invalid fdate format. Expected YYYY-MM-DD');
        }

        try {
            $domain = new AccidentDomain();
            $rows   = $domain->getReportData($filters);
        } catch (PDOException $e) {
            (new ErrorResponder())->send(500, 'Database error: ' . $e->getMessage());
        }

        $format = strtolower(trim($_GET['format'] ?? 'csv'));

        if ($format === 'json') {
            $jsonData = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            (new FileResponder())->send($jsonData, 'accidents.json', 'application/json', false, true);
        } else {
            $fp = fopen('php://temp', 'r+');
            
            // Header row mapping the 14 columns
            fputcsv($fp, [
                'Date_Time', 'Severity', 'Latitude', 'Longitude', 'State',
                'City', 'County', 'Weather_Condition', 'Temperature', 'Visibility',
                'Crossing', 'Junction', 'Traffic_Signal', 'Sunrise_Sunset'
            ]);
            
            foreach ($rows as $row) {
                $crossing = isset($row['crossing']) ? ($row['crossing'] ? 'true' : 'false') : '';
                $junction = isset($row['junction']) ? ($row['junction'] ? 'true' : 'false') : '';
                $trafficSignal = isset($row['traffic_signal']) ? ($row['traffic_signal'] ? 'true' : 'false') : '';
                
                fputcsv($fp, [
                    $row['date_time'] ?? '',
                    $row['severity'] ?? '',
                    $row['latitude'] ?? '',
                    $row['longitude'] ?? '',
                    $row['state'] ?? '',
                    $row['city'] ?? '',
                    $row['county'] ?? '',
                    $row['weather_condition'] ?? '',
                    $row['temperature'] !== null && $row['temperature'] !== '' ? (string)$row['temperature'] : '',
                    $row['visibility'] !== null && $row['visibility'] !== '' ? (string)$row['visibility'] : '',
                    $crossing,
                    $junction,
                    $trafficSignal,
                    $row['sunrise_sunset'] ?? ''
                ]);
            }
            
            rewind($fp);
            $csv = stream_get_contents($fp);
            fclose($fp);
            
            (new FileResponder())->send($csv, 'accidents.csv', 'text/csv', false, true);
        }
    }
}
