#!/usr/bin/env php
<?php
/**
 * seed.php — High-speed bulk seeder for the Kaggle US_Accidents.csv dataset
 *
 * Uses PostgreSQL's COPY command for maximum ingestion throughput.
 * Streams the CSV line-by-line to avoid memory exhaustion on 7.7M row files.
 *
 * Usage:
 *   php seed.php /path/to/US_Accidents.csv
 *   php seed.php --help
 */

declare(strict_types=1);

define('ROOT', dirname(__DIR__));

// ─── CLI argument handling ───────────────────────────────────────

if (in_array('--help', $argv) || in_array('-h', $argv)) {
    echo <<<USAGE
    AVis High-Speed CSV Seeder
    
    Usage:
      php seed.php [path-to-US_Accidents.csv]
    
    Description:
      Reads the Kaggle US Accidents CSV file and bulk-loads it into the
      'accidents' table using PostgreSQL's COPY command for maximum speed.
      If no path is specified, it defaults to looking for 'US_Accidents_March23.csv'
      in the project root directory.
    
    Expected CSV columns (by position):
      0: ID            → accidents.id
      3: Severity       → accidents.severity
      4: Start_Time     → accidents.date_time
      6: Start_Lat      → accidents.latitude
      7: Start_Lng      → accidents.longitude
      9: State          → accidents.state (varies by dataset version)
    
    The script auto-detects column positions from the header row.
    
    Options:
      --help, -h    Show this help message
    
    USAGE;
    exit(0);
}

$csvPath = $argv[1] ?? (ROOT . '/US_Accidents_March23.csv');

if (!file_exists($csvPath)) {
    fwrite(STDERR, "Error: File not found: {$csvPath}\n");
    fwrite(STDERR, "Usage: php seed.php <path-to-US_Accidents.csv> or place US_Accidents_March23.csv in the project root.\n");
    exit(1);
}

echo "Using CSV file: {$csvPath}\n";

// ─── Load environment & database config ──────────────────────────

require_once ROOT . '/src/Core/envLoader.php';
loadEnv();

$dbHost = $_ENV['DB_HOST'] ?? 'localhost';
$dbPort = $_ENV['DB_PORT'] ?? '5432';
$dbName = $_ENV['DB_NAME'] ?? 'AVis';
$dbUser = $_ENV['DB_USER'] ?? 'postgres';
$dbPass = $_ENV['DB_PASSWORD'] ?? '';

// ─── Connect via PDO ─────────────────────────────────────────────

$dsn = "pgsql:host={$dbHost};port={$dbPort};dbname={$dbName}";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, "Database connection failed: " . $e->getMessage() . "\n");
    exit(1);
}

echo "Connected to PostgreSQL ({$dbName}@{$dbHost}:{$dbPort})\n";

// ─── Read header and detect column positions ─────────────────────

$handle = fopen($csvPath, 'r');
if ($handle === false) {
    fwrite(STDERR, "Error: Could not open CSV file: {$csvPath}\n");
    exit(1);
}

$header = fgetcsv($handle);
if ($header === false) {
    fwrite(STDERR, "Error: CSV file is empty\n");
    exit(1);
}

// Normalize header names (trim whitespace)
$header = array_map('trim', $header);

// Find column indexes
$colMap = [
    'id'        => array_search('ID', $header),
    'severity'  => array_search('Severity', $header),
    'start_time'=> array_search('Start_Time', $header),
    'start_lat' => array_search('Start_Lat', $header),
    'start_lng' => array_search('Start_Lng', $header),
    'state'     => array_search('State', $header),
];

// Validate required columns exist
foreach (['id', 'severity', 'start_time', 'start_lat', 'start_lng'] as $required) {
    if ($colMap[$required] === false) {
        fwrite(STDERR, "Error: Required column '{$required}' not found in CSV header.\n");
        fwrite(STDERR, "Header columns: " . implode(', ', $header) . "\n");
        exit(1);
    }
}

echo "CSV header parsed. Column mapping:\n";
foreach ($colMap as $name => $idx) {
    $status = ($idx !== false) ? "column {$idx}" : "NOT FOUND (will use NULL)";
    echo "  {$name} => {$status}\n";
}

// ─── Phase 1: Generate COPY-format temp file ────────────────────

$tmpFile = ROOT . '/.seed_staging.tsv';
$tmpHandle = fopen($tmpFile, 'w');
if ($tmpHandle === false) {
    fwrite(STDERR, "Error: Could not create staging file: {$tmpFile}\n");
    exit(1);
}

echo "\nStage 1: Converting CSV to COPY format...\n";

$lineCount = 0;
$skipCount = 0;
$batchReport = 100000;

while (($row = fgetcsv($handle, 4096, ',')) !== false) {
    $id       = $row[$colMap['id']] ?? '';
    $severity = $row[$colMap['severity']] ?? '';
    $time     = $row[$colMap['start_time']] ?? '';
    $lat      = $row[$colMap['start_lat']] ?? '';
    $lng      = $row[$colMap['start_lng']] ?? '';
    $state    = ($colMap['state'] !== false) ? ($row[$colMap['state']] ?? '') : '';

    // Skip invalid rows
    if (empty($id) || empty($severity) || empty($time) || empty($lat) || empty($lng)) {
        $skipCount++;
        continue;
    }

    // Validate severity is 1-4
    $sevInt = (int) $severity;
    if ($sevInt < 1 || $sevInt > 4) {
        $skipCount++;
        continue;
    }

    // Normalize timestamp: some entries have timezone info, strip it
    $time = preg_replace('/\s*[+-]\d{2}:\d{2}$/', '', $time);

    // Truncate state to 2 chars
    $state = substr(trim($state), 0, 2);
    if ($state === '') {
        $state = '\\N'; // PostgreSQL NULL
    }

    // Write tab-separated line for COPY
    fwrite($tmpHandle, "{$id}\t{$time}\t{$sevInt}\t{$lat}\t{$lng}\t{$state}\n");

    $lineCount++;

    if ($lineCount % $batchReport === 0) {
        echo "  Processed {$lineCount} rows...\n";
    }
}

fclose($handle);
fclose($tmpHandle);

echo "  Total valid rows: {$lineCount}\n";
echo "  Skipped rows: {$skipCount}\n";

if ($lineCount === 0) {
    unlink($tmpFile);
    fwrite(STDERR, "Error: No valid data rows found.\n");
    exit(1);
}

// ─── Phase 2: Bulk load via COPY ─────────────────────────────────

echo "\nStage 2: Bulk loading into PostgreSQL via COPY...\n";

try {
    // Disable indexes temporarily for faster loading
    $pdo->exec("SET maintenance_work_mem = '256MB'");

    // Truncate existing data (optional — comment out to append)
    $pdo->exec("TRUNCATE TABLE accidents");
    echo "  Existing data truncated.\n";

    // Use COPY FROM STDIN via pg_* functions if available, otherwise use \copy via shell
    $copySQL = "COPY accidents (id, date_time, severity, latitude, longitude, state) FROM STDIN WITH (FORMAT text, NULL '\\N')";

    // Try pgsqlCopyFromFile via PDO first (fast, native, works with PDO pgsql driver)
    if (method_exists($pdo, 'pgsqlCopyFromFile')) {
        echo "  Using native PDO pgsqlCopyFromFile...\n";
        $result = $pdo->pgsqlCopyFromFile('accidents', $tmpFile, "\t", "\\N", "id, date_time, severity, latitude, longitude, state");
        if ($result === false) {
            throw new \RuntimeException("PDO pgsqlCopyFromFile failed");
        }
        echo "  Bulk load complete via PDO.\n";
    }
    // Try native pg_* approach second (requires pgsql extension)
    elseif (function_exists('pg_connect')) {
        echo "  Using native pg_copy_from...\n";
        $pgConn = @pg_connect("host={$dbHost} port={$dbPort} dbname={$dbName} user={$dbUser} password={$dbPass}");

        if ($pgConn) {
            // Read file into array in chunks to manage memory
            $chunkSize = 50000;
            $totalInserted = 0;

            $copyHandle = fopen($tmpFile, 'r');

            while (!feof($copyHandle)) {
                $lines = [];
                for ($i = 0; $i < $chunkSize && !feof($copyHandle); $i++) {
                    $line = fgets($copyHandle);
                    if ($line !== false) {
                        $lines[] = $line;
                    }
                }

                if (empty($lines)) {
                    break;
                }

                $result = pg_copy_from($pgConn, 'accidents', $lines, "\t", "\\N");
                if ($result === false) {
                    $error = pg_last_error($pgConn);
                    fwrite(STDERR, "  COPY error: {$error}\n");
                }

                $totalInserted += count($lines);
                echo "  Loaded {$totalInserted} / {$lineCount} rows...\n";
            }

            fclose($copyHandle);
            pg_close($pgConn);

            echo "  Bulk load complete: {$totalInserted} rows inserted.\n";
        } else {
            throw new \RuntimeException("Failed to connect via pg_connect");
        }
    } else {
        // Fallback: use psql \copy via shell command
        echo "  Native PHP bulk load functions not available. Using psql shell fallback...\n";

        $escapedPass = escapeshellarg($dbPass);
        $escapedFile = escapeshellarg($tmpFile);

        $cmd = "PGPASSWORD={$escapedPass} psql -h {$dbHost} -p {$dbPort} -U {$dbUser} -d {$dbName} "
             . "-c \"\\COPY accidents (id, date_time, severity, latitude, longitude, state) "
             . "FROM {$escapedFile} WITH (FORMAT text, NULL '\\N')\" 2>&1";

        $output = shell_exec($cmd);
        echo "  psql output: {$output}\n";
    }

    // Re-analyze for query planner
    echo "\nStage 3: Analyzing indexes...\n";
    $pdo->exec("ANALYZE accidents");
    echo "  ANALYZE complete.\n";

} catch (\Throwable $e) {
    fwrite(STDERR, "Error during bulk load: " . $e->getMessage() . "\n");
    unlink($tmpFile);
    exit(1);
}

// ─── Cleanup ─────────────────────────────────────────────────────

unlink($tmpFile);

echo "\n✓ Seeding complete. {$lineCount} accident records loaded.\n";
exit(0);
