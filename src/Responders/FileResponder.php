<?php

use JetBrains\PhpStorm\NoReturn;

class FileResponder
{
    /**
     * @param string $data        Either the raw string content (e.g., CSV data) OR the physical file path.
     * @param string $fileName    The name the browser will see.
     * @param string $contentType The MIME type (e.g., 'text/csv', 'image/png').
     * @param bool   $isFile      Set to true if $data is a path to a file on the server.
     * @param bool   $download    Set to true to force a download (attachment), false to display (inline).
     */
    #[NoReturn]
    public function send(string $data, string $fileName, string $contentType, bool $isFile = false, bool $download = true): void
    {
        if ($isFile && !file_exists($data)) {
            (new ErrorResponder())->send(404, 'File not found');
        }

        if (ob_get_length()) {
            ob_clean();
        }

        $disposition = $download ? 'attachment' : 'inline';

        header('Content-Type: ' . $contentType);
        header('Content-Disposition: ' . $disposition . '; filename="' . $fileName . '"');
        
        if ($isFile) {
            header('Content-Length: ' . filesize($data));
            header('Cache-Control: public, max-age=86400'); 
            readfile($data);
        } else {
            header('Content-Length: ' . strlen($data));
            header('Cache-Control: no-cache, no-store, must-revalidate'); 
            echo $data;
        }
        
        exit;
    }
}