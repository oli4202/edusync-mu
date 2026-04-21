<?php

namespace App;

/**
 * Helper functions for the application
 */

/**
 * Get database connection
 */
function getDB(): \PDO
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $host    = getenv('DB_HOST') ?: '127.0.0.1';
            $dbname  = getenv('DB_NAME') ?: 'edusync_mu';
            $user    = getenv('DB_USER') ?: 'root';
            $pass    = getenv('DB_PASS') ?: '';
            $charset = getenv('DB_CHARSET') ?: 'utf8mb4';

            $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new \PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

/**
 * Sanitize user input
 */
function clean(string $input): string
{
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Format time as "time ago"
 */
function timeAgo(string $datetime): string
{
    $time = time() - strtotime($datetime);
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time / 60) . ' min ago';
    if ($time < 86400) return floor($time / 3600) . ' hr ago';
    if ($time < 604800) return floor($time / 86400) . ' days ago';
    return date('M j, Y', strtotime($datetime));
}

/**
 * Redirect to URL
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Get current site base URL
 */
function baseUrl(): string
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $protocol . $host;
}

/**
 * Call Google Gemini 2.5 Flash API for AI features
 */
function callAI(string $prompt, string $systemPrompt = ''): array
{
    $apiKey = getenv('GEMINI_API_KEY') ?: '';
    if (!$apiKey || $apiKey === 'YOUR_GEMINI_API_KEY_HERE') {
        return [
            'success' => false,
            'text' => "Gemini API key not configured"
        ];
    }

    $fullPrompt = $systemPrompt ? $systemPrompt . "\n\n" . $prompt : $prompt;

    $data = [
        'contents' => [[
            'parts' => [['text' => $fullPrompt]]
        ]],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 2048,
            'topP' => 0.8,
            'topK' => 40,
        ]
    ];

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $decoded = json_decode($response, true);
        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if ($text) {
            return ['success' => true, 'text' => $text];
        }
    }

    $decoded = json_decode($response, true);
    $errMsg = $decoded['error']['message'] ?? '';

    return [
        'success' => false,
        'text' => "Gemini API error (HTTP $httpCode)" . ($errMsg ? ": $errMsg" : "")
    ];
}
