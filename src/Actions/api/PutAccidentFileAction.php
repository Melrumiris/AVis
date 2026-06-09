<?php

declare(strict_types=1);

require_once ROOT . '/src/Domain/AccidentDomain.php';
require_once ROOT . '/src/Core/JwtAuth.php';
require_once ROOT . '/src/Responders/JsonResponder.php';
require_once ROOT . '/src/Responders/ErrorResponder.php';

/**
 * PUT /api/v0/admin/accident/file/{start_time}/{end_time}
 *
 * Replaces all accident records within the given time period with the records
 * from the uploaded CSV file, wrapped in a single database transaction.
 * The $param received will be "{start_time}/{end_time}" (URL-encoded).
 */
class PutAccidentFileAction implements Action
{
    private const MAX_FILE_SIZE  = 52_428_800; // 50 MB
    private const ALLOWED_EXTS  = ['csv'];

    public function execute(?string $param): void
    {
        // Parse start_time and end_time from the URL param segment
        if (empty($param)) {
            (new ErrorResponder())->send(400, 'Missing start_time and end_time parameters');
        }

        $parts = explode('/', $param, 2);
        if (count($parts) !== 2) {
            (new ErrorResponder())->send(400, 'Expected format: {start_time}/{end_time}');
        }

        $startTime = urldecode($parts[0]);
        $endTime   = urldecode($parts[1]);

        // Basic datetime format validation (YYYY-MM-DD HH:MM:SS)
        $dtPattern = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';
        if (!preg_match($dtPattern, $startTime)) {
            (new ErrorResponder())->send(400, 'Invalid start_time format. Expected: YYYY-MM-DD HH:MM:SS');
        }
        if (!preg_match($dtPattern, $endTime)) {
            (new ErrorResponder())->send(400, 'Invalid end_time format. Expected: YYYY-MM-DD HH:MM:SS');
        }
        if ($startTime >= $endTime) {
            (new ErrorResponder())->send(400, 'start_time must be before end_time');
        }

        (new JwtAuth())->authenticateAdminRequest();

        if (
            !isset($_FILES['csv_file']) ||
            $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK
        ) {
            $uploadError = $_FILES['csv_file']['error'] ?? UPLOAD_ERR_NO_FILE;
            (new ErrorResponder())->send(400, 'File upload error code: ' . $uploadError);
        }

        $file = $_FILES['csv_file'];

        if ($file['size'] > self::MAX_FILE_SIZE) {
            (new ErrorResponder())->send(413, 'File exceeds maximum allowed size of 50 MB');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTS, true)) {
            (new ErrorResponder())->send(415, 'Only .csv files are accepted');
        }

        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            (new ErrorResponder())->send(500, 'Could not open uploaded file');
        }

        // Skip header row
        fgetcsv($handle);

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        if (empty($rows)) {
            (new ErrorResponder())->send(400, 'CSV file contains no data rows');
        }

        try {
            $domain = new AccidentDomain();
            $count  = $domain->replaceAccidentsBatch($startTime, $endTime, $rows);
        } catch (PDOException $e) {
            (new ErrorResponder())->send(500, 'Database error: ' . $e->getMessage());
        }

        (new JsonResponder())->send([
            'success' => true,
            'data'    => ['inserted' => $count],
        ], 201);
    }
}
