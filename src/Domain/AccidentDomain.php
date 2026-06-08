<?php

declare(strict_types=1);

require_once ROOT . '/src/Core/Database.php';

class AccidentDomain
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getMapPoints(string $sdate, string $fdate): array
    {
        $sql  = 'SELECT latitude AS lat, longitude AS lng, severity
                 FROM   accidents
                 WHERE  latitude  IS NOT NULL
                   AND  longitude IS NOT NULL';
        $params = [];

        if ($sdate !== '') {
            $sql       .= ' AND date_time >= :sdate';
            $params[':sdate'] = $sdate . ' 00:00:00';
        }
        if ($fdate !== '') {
            $sql       .= ' AND date_time <= :fdate';
            $params[':fdate'] = $fdate . ' 23:59:59';
        }

        $sql .= ' LIMIT 1500';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatistics(
        string $sdate,
        string $fdate,
        string $severity,
        string $region,
        string $groupBy
    ): array {
        $where  = 'WHERE 1=1';
        $params = [];

        if ($sdate !== '') {
            $where           .= ' AND date_time >= :sdate';
            $params[':sdate'] = $sdate . ' 00:00:00';
        }
        if ($fdate !== '') {
            $where           .= ' AND date_time <= :fdate';
            $params[':fdate'] = $fdate . ' 23:59:59';
        }
        if ($severity !== 'ALL') {
            $where              .= ' AND severity = :sev';
            $params[':sev']      = (int) $severity;
        }

        $where .= match ($region) {
            'NE'    => ' AND latitude >= 39.8 AND longitude >= -98.5',
            'NW'    => ' AND latitude >= 39.8 AND longitude <  -98.5',
            'SE'    => ' AND latitude <  39.8 AND longitude >= -98.5',
            'SW'    => ' AND latitude <  39.8 AND longitude <  -98.5',
            default => '',
        };

        $sql = match ($groupBy) {
            'year'     => "SELECT EXTRACT(YEAR  FROM date_time)::text AS label,
                                  COUNT(*) AS total
                           FROM   accidents {$where}
                           GROUP  BY EXTRACT(YEAR  FROM date_time)
                           ORDER  BY label",

            'month'    => "SELECT EXTRACT(MONTH FROM date_time)::text AS label,
                                  COUNT(*) AS total
                           FROM   accidents {$where}
                           GROUP  BY EXTRACT(MONTH FROM date_time)
                           ORDER  BY label",

            'day'      => "SELECT EXTRACT(DOW   FROM date_time)::text AS label,
                                  COUNT(*) AS total
                           FROM   accidents {$where}
                           GROUP  BY EXTRACT(DOW   FROM date_time)
                           ORDER  BY label",

            'location' => "SELECT CASE
                                    WHEN latitude >= 39.8 AND longitude >= -98.5 THEN 'North-East'
                                    WHEN latitude >= 39.8 AND longitude <  -98.5 THEN 'North-West'
                                    WHEN latitude <  39.8 AND longitude >= -98.5 THEN 'South-East'
                                    WHEN latitude <  39.8 AND longitude <  -98.5 THEN 'South-West'
                                    ELSE 'Unknown'
                                  END AS label,
                                  COUNT(*) AS total
                           FROM   accidents {$where}
                           GROUP  BY label",

            default    => "SELECT severity::text AS label,
                                  COUNT(*) AS total
                           FROM   accidents {$where}
                           GROUP  BY severity
                           ORDER  BY severity",
        };

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReportData(string $sdate, string $fdate): array
    {
        $sql    = 'SELECT date_time, severity, latitude, longitude, state
                   FROM   accidents
                   WHERE  1=1';
        $params = [];

        if ($sdate !== '') {
            $sql             .= ' AND date_time >= :sdate';
            $params[':sdate'] = $sdate . ' 00:00:00';
        }
        if ($fdate !== '') {
            $sql             .= ' AND date_time <= :fdate';
            $params[':fdate'] = $fdate . ' 23:59:59';
        }

        $sql .= ' ORDER BY date_time DESC LIMIT 10000';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertAccident(
        string  $dateTime,
        int     $severity,
        float   $lat,
        float   $lng,
        ?string $state = null
    ): bool {
        $stmt = $this->db->prepare(
            'INSERT INTO accidents (id, date_time, severity, latitude, longitude, state)
             VALUES (:id, :date_time, :severity, :latitude, :longitude, :state)'
        );

        return $stmt->execute([
            ':id'         => $this->generateAccidentId(),
            ':date_time'  => $dateTime,
            ':severity'   => $severity,
            ':latitude'   => $lat,
            ':longitude'  => $lng,
            ':state'      => $state,
        ]);
    }

    /**
     * Inserts a batch of accident rows inside a database transaction.
     * Rolls back on any failure to prevent partial CSV ingestions.
     */
    public function insertAccidentsBatch(array $rows): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO accidents (id, date_time, severity, latitude, longitude, state)
             VALUES (:id, :date_time, :severity, :latitude, :longitude, :state)'
        );

        $this->db->beginTransaction();

        try {
            $count = 0;
            foreach ($rows as $row) {
                if (count($row) < 4) {
                    continue;
                }

                $stmt->execute([
                    ':id'         => $this->generateAccidentId(),
                    ':date_time'  => $row[0],
                    ':severity'   => (int) $row[1],
                    ':latitude'   => (float) $row[2],
                    ':longitude'  => (float) $row[3],
                    ':state'      => isset($row[4]) ? strtoupper(trim($row[4])) : null,
                ]);

                ++$count;
            }

            $this->db->commit();
            return $count;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Generates a unique accident ID in format A-XXXXXXXX (UUID-based).
     */
    private function generateAccidentId(): string
    {
        return 'A-' . substr(bin2hex(random_bytes(4)), 0, 8);
    }
}
