<?php

declare(strict_types=1);

class GeminiDomain
{
    private string $apiKey;
    
    public function __construct()
    {
        $this->apiKey = $_ENV['GEMINI_API_KEY'] ?? $_ENV['GEM_API_KEY'] ?? '';
        if (empty($this->apiKey)) {
            throw new RuntimeException("GEMINI_API_KEY is not set in environment.");
        }
    }

    public function generateSql(string $userPrompt, array $userContext): string
    {
        $schema = "Table: accidents\nColumns: id, date_time, severity, latitude, longitude, state, city, county, weather_condition, temperature, visibility, crossing (bool), junction (bool), traffic_signal (bool), sunrise_sunset.";
        
        $contextString = "User Context:\n" . json_encode($userContext, JSON_PRETTY_PRINT);

        $systemPrompt = "You are a Natural Language to SQL converter. You must ONLY output a valid PostgreSQL SELECT statement inside a ```sql markdown block. 
Database Schema:
{$schema}

{$contextString}

Ensure the query does not contain any semicolons unless it is the very last character.
Do not include any explanation or other text.";

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite:generateContent?key=' . $this->apiKey;

        $payload = [
            'system_instruction' => [
                'parts' => [
                    ['text' => $systemPrompt]
                ]
            ],
            'contents' => [
                [
                    'parts' => [
                        ['text' => $userPrompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.0,
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode >= 400) {
            throw new RuntimeException('Failed to communicate with Gemini API: ' . $response);
        }

        $decoded = json_decode($response, true);
        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (empty($text)) {
            throw new RuntimeException('Gemini returned an empty or invalid response.');
        }

        return $this->extractAndValidateSql($text);
    }

    public function extractAndValidateSql(string $response): string
    {
        if (!preg_match('/```sql\s*(.*?)\s*```/is', $response, $matches)) {
            throw new RuntimeException('Failed to extract SQL from Gemini response.');
        }
        
        $sql = trim($matches[1]);
        
        if (stripos($sql, 'SELECT') !== 0) {
            throw new RuntimeException('Query must begin with SELECT.');
        }
        
        // Semicolon check: if semicolon exists and is not the last character
        $semicolonPos = strpos($sql, ';');
        if ($semicolonPos !== false && $semicolonPos !== strlen($sql) - 1) {
            throw new RuntimeException('Stacked queries are not allowed (semicolon found).');
        }
        
        return $sql;
    }
}
