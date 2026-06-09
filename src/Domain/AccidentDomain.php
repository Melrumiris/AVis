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

    // ─── Dynamic WHERE builder ────────────────────────────────────────────────

    /**
     * Builds a safe WHERE clause from a validated $filters array.
     * Returns [whereString, pdoParams].
     * Column names are never interpolated from user input — only values go through PDO params.
     * Region is a special geo-box filter; boolean columns get literal 'true'/'false'.
     *
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function buildWhere(array $filters): array
    {
        $clauses = ['1=1'];
        $params  = [];

        // Date range
        if (!empty($filters['sdate'])) {
            $clauses[]       = 'date_time >= :sdate';
            $params[':sdate'] = $filters['sdate'] . ' 00:00:00';
        }
        if (!empty($filters['fdate'])) {
            $clauses[]       = 'date_time <= :fdate';
            $params[':fdate'] = $filters['fdate'] . ' 23:59:59';
        }

        // Severity
        if (!empty($filters['severity']) && $filters['severity'] !== 'ALL') {
            $clauses[]          = 'severity = :severity';
            $params[':severity'] = (int) $filters['severity'];
        }

        // Region (geo-box — no PDO param; safe because value is validated by Action allowlist)
        if (!empty($filters['region']) && $filters['region'] !== 'ALL') {
            $regionClause = match ($filters['region']) {
                'NE'    => 'latitude >= 39.8 AND longitude >= -98.5',
                'NW'    => 'latitude >= 39.8 AND longitude <  -98.5',
                'SE'    => 'latitude <  39.8 AND longitude >= -98.5',
                'SW'    => 'latitude <  39.8 AND longitude <  -98.5',
                default => null,
            };
            if ($regionClause !== null) {
                $clauses[] = $regionClause;
            }
        }

        // String columns (ILIKE for case-insensitive match)
        foreach (['city', 'county', 'weather_condition', 'sunrise_sunset'] as $col) {
            if (!empty($filters[$col])) {
                $clauses[]       = "{$col} ILIKE :{$col}";
                $params[":{$col}"] = $filters[$col];
            }
        }

        // Numeric columns (exact match — frontend can extend to ranges later)
        foreach (['temperature', 'visibility'] as $col) {
            if (isset($filters[$col]) && $filters[$col] !== '') {
                $clauses[]       = "{$col} = :{$col}";
                $params[":{$col}"] = (float) $filters[$col];
            }
        }

        // Boolean columns
        foreach (['crossing', 'junction', 'traffic_signal'] as $col) {
            if (isset($filters[$col]) && $filters[$col] !== '') {
                $clauses[]       = "{$col} = :{$col}";
                $params[":{$col}"] = in_array(strtolower((string) $filters[$col]), ['true', '1', 'yes'], true)
                    ? 'true' : 'false';
            }
        }

        return ['WHERE ' . implode(' AND ', $clauses), $params];
    }

    // ─── Public query methods ─────────────────────────────────────────────────

    public function getMapPoints(array $filters): array
    {
        [$where, $params] = $this->buildWhere($filters);

        $sql = "SELECT latitude AS lat, longitude AS lng, severity
                FROM   accidents
                {$where}
                  AND  latitude  IS NOT NULL
                  AND  longitude IS NOT NULL
                LIMIT 1500";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatistics(array $filters, string $groupBy): array
    {
        [$where, $params] = $this->buildWhere($filters);

        $sql = match ($groupBy) {
            'year'  => "SELECT EXTRACT(YEAR  FROM date_time)::text AS label,
                               COUNT(*) AS total
                        FROM   accidents {$where}
                        GROUP  BY EXTRACT(YEAR  FROM date_time)
                        ORDER  BY label",

            'month' => "SELECT EXTRACT(MONTH FROM date_time)::text AS label,
                               COUNT(*) AS total
                        FROM   accidents {$where}
                        GROUP  BY EXTRACT(MONTH FROM date_time)
                        ORDER  BY label",

            'day'   => "SELECT EXTRACT(DOW   FROM date_time)::text AS label,
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
                           FROM    accidents {$where}
                           GROUP   BY label",

            'city'  => "SELECT city AS label, COUNT(*) AS total
                        FROM   accidents {$where} AND city IS NOT NULL
                        GROUP  BY city
                        ORDER  BY total DESC
                        LIMIT  25",

            'county' => "SELECT county AS label, COUNT(*) AS total
                         FROM   accidents {$where} AND county IS NOT NULL
                         GROUP  BY county
                         ORDER  BY total DESC
                         LIMIT  25",

            'weather_condition' => "SELECT weather_condition AS label, COUNT(*) AS total
                                    FROM   accidents {$where} AND weather_condition IS NOT NULL
                                    GROUP  BY weather_condition
                                    ORDER  BY total DESC
                                    LIMIT  25",

            'sunrise_sunset' => "SELECT sunrise_sunset AS label, COUNT(*) AS total
                                 FROM   accidents {$where} AND sunrise_sunset IS NOT NULL
                                 GROUP  BY sunrise_sunset
                                 ORDER  BY label",

            default => "SELECT severity::text AS label,
                               COUNT(*) AS total
                        FROM   accidents {$where}
                        GROUP  BY severity
                        ORDER  BY severity",
        };

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReportData(array $filters): array
    {
        [$where, $params] = $this->buildWhere($filters);

        $sql = "SELECT date_time, severity, latitude, longitude, state,
                       city, county, weather_condition,
                       temperature, visibility,
                       crossing, junction, traffic_signal, sunrise_sunset
                FROM   accidents
                {$where}
                ORDER  BY date_time DESC
                LIMIT  10000";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentAccidents(int $limit = 100): array
    {
        $sql = "SELECT id, date_time, severity, city, state
                FROM   accidents
                ORDER  BY date_time DESC
                LIMIT  :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─── Write methods ────────────────────────────────────────────────────────

    public function insertAccident(
        string  $dateTime,
        int     $severity,
        float   $lat,
        float   $lng,
        ?string $state = null,
        ?string $city = null,
        ?string $county = null,
        ?string $weatherCondition = null,
        ?float  $temperature = null,
        ?float  $visibility = null,
        ?bool   $crossing = null,
        ?bool   $junction = null,
        ?bool   $trafficSignal = null,
        ?string $sunriseSunset = null
    ): bool {
        $stmt = $this->db->prepare(
            'INSERT INTO accidents (id, date_time, severity, latitude, longitude, state, city, county, weather_condition, temperature, visibility, crossing, junction, traffic_signal, sunrise_sunset)
             VALUES (:id, :date_time, :severity, :latitude, :longitude, :state, :city, :county, :weather_condition, :temperature, :visibility, :crossing, :junction, :traffic_signal, :sunrise_sunset)'
        );

        return $stmt->execute([
            ':id'        => $this->generateAccidentId(),
            ':date_time' => $dateTime,
            ':severity'  => $severity,
            ':latitude'  => $lat,
            ':longitude' => $lng,
            ':state'     => $state,
            ':city'      => $city,
            ':county'    => $county,
            ':weather_condition' => $weatherCondition,
            ':temperature' => $temperature,
            ':visibility'  => $visibility,
            ':crossing'    => $crossing,
            ':junction'    => $junction,
            ':traffic_signal' => $trafficSignal,
            ':sunrise_sunset' => $sunriseSunset,
        ]);
    }

    /**
     * Inserts a batch of accident rows inside a database transaction.
     * Expects CSV column order: date_time, severity, lat, lng, state,
     * city, county, weather_condition, temperature, visibility,
     * crossing, junction, traffic_signal, sunrise_sunset  (all optional after col 4).
     */
    public function insertAccidentsBatch(array $rows): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO accidents
             (id, date_time, severity, latitude, longitude, state,
              city, county, weather_condition, temperature, visibility,
              crossing, junction, traffic_signal, sunrise_sunset)
             VALUES
             (:id, :date_time, :severity, :latitude, :longitude, :state,
              :city, :county, :weather_condition, :temperature, :visibility,
              :crossing, :junction, :traffic_signal, :sunrise_sunset)'
        );

        $this->db->beginTransaction();

        try {
            $count = 0;
            foreach ($rows as $row) {
                if (count($row) < 4) {
                    continue;
                }

                $stmt->execute([
                    ':id'                => $this->generateAccidentId(),
                    ':date_time'         => $row[0],
                    ':severity'          => (int) $row[1],
                    ':latitude'          => (float) $row[2],
                    ':longitude'         => (float) $row[3],
                    ':state'             => isset($row[4])  ? strtoupper(trim($row[4]))  : null,
                    ':city'              => $row[5]  ?? null,
                    ':county'            => $row[6]  ?? null,
                    ':weather_condition' => $row[7]  ?? null,
                    ':temperature'       => isset($row[8])  && $row[8]  !== '' ? (float) $row[8]  : null,
                    ':visibility'        => isset($row[9])  && $row[9]  !== '' ? (float) $row[9]  : null,
                    ':crossing'          => isset($row[10]) && $row[10] !== '' ? (filter_var($row[10], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false') : null,
                    ':junction'          => isset($row[11]) && $row[11] !== '' ? (filter_var($row[11], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false') : null,
                    ':traffic_signal'    => isset($row[12]) && $row[12] !== '' ? (filter_var($row[12], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false') : null,
                    ':sunrise_sunset'    => $row[13] ?? null,
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
     * Atomically replaces accidents within [startTime, endTime] with the given rows.
     */
    public function replaceAccidentsBatch(string $startTime, string $endTime, array $rows): int
    {
        $this->db->beginTransaction();

        try {
            $del = $this->db->prepare(
                'DELETE FROM accidents WHERE date_time >= :start AND date_time <= :end'
            );
            $del->execute([':start' => $startTime, ':end' => $endTime]);

            // Reuse the batch insert logic by delegating to insertAccidentsBatch
            // but inside the same transaction — so we call the inner loop directly.
            $ins = $this->db->prepare(
                'INSERT INTO accidents (id, date_time, severity, latitude, longitude, state, city, county, weather_condition, temperature, visibility, crossing, junction, traffic_signal, sunrise_sunset)
                 VALUES (:id, :date_time, :severity, :latitude, :longitude, :state, :city, :county, :weather_condition, :temperature, :visibility, :crossing, :junction, :traffic_signal, :sunrise_sunset)'
            );

            $count = 0;
            foreach ($rows as $row) {
                if (count($row) < 4) {
                    continue;
                }
                $ins->execute([
                    ':id'                => $this->generateAccidentId(),
                    ':date_time'         => $row[0],
                    ':severity'          => (int) $row[1],
                    ':latitude'          => (float) $row[2],
                    ':longitude'         => (float) $row[3],
                    ':state'             => isset($row[4])  ? strtoupper(trim($row[4]))  : null,
                    ':city'              => $row[5]  ?? null,
                    ':county'            => $row[6]  ?? null,
                    ':weather_condition' => $row[7]  ?? null,
                    ':temperature'       => isset($row[8])  && $row[8]  !== '' ? (float) $row[8]  : null,
                    ':visibility'        => isset($row[9])  && $row[9]  !== '' ? (float) $row[9]  : null,
                    ':crossing'          => isset($row[10]) && $row[10] !== '' ? (filter_var($row[10], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false') : null,
                    ':junction'          => isset($row[11]) && $row[11] !== '' ? (filter_var($row[11], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false') : null,
                    ':traffic_signal'    => isset($row[12]) && $row[12] !== '' ? (filter_var($row[12], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false') : null,
                    ':sunrise_sunset'    => $row[13] ?? null,
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

    // ─── Execute Raw SQL (Nlp Feature) ────────────────────────────────────────────────────────

    public function executeRawSelect(string $sql): array
    {
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException('Database execution error: ' . $e->getMessage());
        }
    }

    private function generateAccidentId(): string
    {
        return 'A-' . substr(bin2hex(random_bytes(4)), 0, 8);
    }
}
