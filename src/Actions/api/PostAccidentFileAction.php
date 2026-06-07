<?php

declare(strict_types=1);

require_once ROOT . '/src/Domain/AccidentDomain.php';
require_once ROOT . '/src/Core/JwtAuth.php';
require_once ROOT . '/src/Responders/JsonResponder.php';
require_once ROOT . '/src/Responders/ErrorResponder.php';

class PostAccidentFileAction implements Action
{
    private const MAX_FILE_SIZE  = 52_428_800;
    private const ALLOWED_MIME   = ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'];

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
        $auth     = new JwtAuth();

        if (!$auth->verify(new JWT($rawToken))) {
            (new ErrorResponder())->send(401, 'Invalid or expired access token');
        }

        $parts = (new JWT($rawToken))->split();
        $role  = $parts['payload']->role ?? '';

        if ($role !== 'admin') {
            (new ErrorResponder())->send(403, 'Forbidden: admin access required');
        }

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
        if ($extension !== 'csv') {
            (new ErrorResponder())->send(415, 'Only .csv files are accepted');
        }

        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            (new ErrorResponder())->send(500, 'Could not open uploaded file');
        }

        fgetcsv($handle);

        $rows = [];
        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        if (empty($rows)) {
            (new ErrorResponder())->send(400, 'CSV file contains no data rows');
        }

        try {
            $domain  = new AccidentDomain();
            $count   = $domain->insertAccidentsBatch($rows);
        } catch (PDOException $e) {
            (new ErrorResponder())->send(500, 'Database error: ' . $e->getMessage());
        }

        (new JsonResponder())->send([
            'success' => true,
            'data'    => ['inserted' => $count],
        ], 201);
    }
}
