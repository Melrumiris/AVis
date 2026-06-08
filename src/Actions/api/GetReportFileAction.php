<?php

declare(strict_types=1);

require_once ROOT . '/src/Domain/AccidentDomain.php';
require_once ROOT . '/src/Core/JwtAuth.php';
require_once ROOT . '/src/Responders/FileResponder.php';
require_once ROOT . '/src/Responders/ErrorResponder.php';

class GetReportFileAction implements Action
{
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
            $rows   = $domain->getReportData($sdate, $fdate);
        } catch (PDOException $e) {
            (new ErrorResponder())->send(500, 'Database error: ' . $e->getMessage());
        }

        // Build CSV string in memory
        $csv = "Date_Time,Severity,Latitude,Longitude,State\n";
        foreach ($rows as $row) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s\n",
                $row['date_time'] ?? '',
                $row['severity'] ?? '',
                $row['latitude'] ?? '',
                $row['longitude'] ?? '',
                $row['state'] ?? ''
            );
        }

        (new FileResponder())->send($csv, 'accidents.csv', 'text/csv', false, true);
    }
}
