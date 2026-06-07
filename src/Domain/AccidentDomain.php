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
        $sql  = 'SELECT latitudine AS lat, longitudine AS lng, severitate
                 FROM   accidente
                 WHERE  latitudine  IS NOT NULL
                   AND  longitudine IS NOT NULL';
        $params = [];

        if ($sdate !== '') {
            $sql       .= ' AND data_ora >= :sdate';
            $params[':sdate'] = $sdate . ' 00:00:00';
        }
        if ($fdate !== '') {
            $sql       .= ' AND data_ora <= :fdate';
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
            $where           .= ' AND data_ora >= :sdate';
            $params[':sdate'] = $sdate . ' 00:00:00';
        }
        if ($fdate !== '') {
            $where           .= ' AND data_ora <= :fdate';
            $params[':fdate'] = $fdate . ' 23:59:59';
        }
        if ($severity !== 'ALL') {
            $where              .= ' AND severitate = :sev';
            $params[':sev']      = (int) $severity;
        }

        $where .= match ($region) {
            'NE'    => ' AND latitudine >= 39.8 AND longitudine >= -98.5',
            'NW'    => ' AND latitudine >= 39.8 AND longitudine <  -98.5',
            'SE'    => ' AND latitudine <  39.8 AND longitudine >= -98.5',
            'SW'    => ' AND latitudine <  39.8 AND longitudine <  -98.5',
            default => '',
        };

        $sql = match ($groupBy) {
            'an'       => "SELECT EXTRACT(YEAR  FROM data_ora)::text AS eticheta,
                                  COUNT(*) AS total
                           FROM   accidente {$where}
                           GROUP  BY EXTRACT(YEAR  FROM data_ora)
                           ORDER  BY eticheta",

            'luna'     => "SELECT EXTRACT(MONTH FROM data_ora)::text AS eticheta,
                                  COUNT(*) AS total
                           FROM   accidente {$where}
                           GROUP  BY EXTRACT(MONTH FROM data_ora)
                           ORDER  BY eticheta",

            'ziua'     => "SELECT EXTRACT(DOW   FROM data_ora)::text AS eticheta,
                                  COUNT(*) AS total
                           FROM   accidente {$where}
                           GROUP  BY EXTRACT(DOW   FROM data_ora)
                           ORDER  BY eticheta",

            'location' => "SELECT CASE
                                    WHEN latitudine >= 39.8 AND longitudine >= -98.5 THEN 'North-East'
                                    WHEN latitudine >= 39.8 AND longitudine <  -98.5 THEN 'North-West'
                                    WHEN latitudine <  39.8 AND longitudine >= -98.5 THEN 'South-East'
                                    WHEN latitudine <  39.8 AND longitudine <  -98.5 THEN 'South-West'
                                    ELSE 'Unknown'
                                  END AS eticheta,
                                  COUNT(*) AS total
                           FROM   accidente {$where}
                           GROUP  BY eticheta",

            default    => "SELECT severitate::text AS eticheta,
                                  COUNT(*) AS total
                           FROM   accidente {$where}
                           GROUP  BY severitate
                           ORDER  BY severitate",
        };

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReportData(string $sdate, string $fdate): array
    {
        $sql    = 'SELECT data_ora, severitate, latitudine, longitudine
                   FROM   accidente
                   WHERE  1=1';
        $params = [];

        if ($sdate !== '') {
            $sql             .= ' AND data_ora >= :sdate';
            $params[':sdate'] = $sdate . ' 00:00:00';
        }
        if ($fdate !== '') {
            $sql             .= ' AND data_ora <= :fdate';
            $params[':fdate'] = $fdate . ' 23:59:59';
        }

        $sql .= ' ORDER BY data_ora DESC LIMIT 10000';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertAccident(
        string $dataOra,
        int    $severity,
        float  $lat,
        float  $lng
    ): bool {
        $stmt = $this->db->prepare(
            'INSERT INTO accidente (data_ora, severitate, latitudine, longitudine)
             VALUES (:data_ora, :severitate, :latitudine, :longitudine)'
        );

        return $stmt->execute([
            ':data_ora'    => $dataOra,
            ':severitate'  => $severity,
            ':latitudine'  => $lat,
            ':longitudine' => $lng,
        ]);
    }

    public function insertAccidentsBatch(array $rows): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO accidente (data_ora, severitate, latitudine, longitudine)
             VALUES (:data_ora, :severitate, :latitudine, :longitudine)'
        );

        $count = 0;
        foreach ($rows as $row) {
            if (count($row) < 4) {
                continue;
            }

            $stmt->execute([
                ':data_ora'    => $row[0],
                ':severitate'  => (int) $row[1],
                ':latitudine'  => (float) $row[2],
                ':longitudine' => (float) $row[3],
            ]);

            ++$count;
        }

        return $count;
    }
}
