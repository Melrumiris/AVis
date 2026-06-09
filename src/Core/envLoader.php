<?php

function loadEnv(): void
{
    $env = ROOT . '/.env';
    if (file_exists($env)) {
        $lines = file($env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if (!str_starts_with($line, '#') && str_contains($line, '=')) {

                list($key, $value) = explode('=', $line, 2);

                if (isset($value) && trim($value) !== '') {
                    $key = trim($key);
                    $value = trim($value);
                    $_ENV[$key] = $value;
                }
            }
        }
    }
}