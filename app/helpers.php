<?php

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
 * Resolve a user avatar path/url to a browser-safe URL.
 */
function avatarUrl(?string $avatar, string $name = 'Student'): string
{
    $avatar = trim((string)$avatar);
    if ($avatar !== '') {
        if (preg_match('#^https?://#i', $avatar)) {
            return $avatar;
        }
        return '/' . ltrim($avatar, '/');
    }

    $label = rawurlencode(trim($name) !== '' ? $name : 'Student');
    return "https://ui-avatars.com/api/?name={$label}&background=0f172a&color=22d3ee&size=128";
}

/**
 * Call AI API (Supports Groq for text and Gemini for images)
 */
function callAI(string $prompt, string $systemPrompt = '', string $base64Image = ''): array
{
    // Try environment variables or config
    $apiKeysPath = __DIR__ . '/../config/api-keys.php';
    $api_keys = [];
    if (file_exists($apiKeysPath)) {
        include $apiKeysPath;
    }

    $geminiKey = getenv('GEMINI_API_KEY') ?: ($api_keys['GEMINI_API_KEY'] ?? '');
    $groqKey = getenv('GROQ_API_KEY') ?: ($api_keys['GROQ_API_KEY'] ?? '');
    $preferred = getenv('PREFERRED_VISION_MODEL') ?: ($api_keys['PREFERRED_VISION_MODEL'] ?? 'groq');

    // 1. If image is present, follow the preference
    if ($base64Image) {
        if ($preferred === 'gemini' && $geminiKey && strpos($geminiKey, 'YOUR_') === false) {
            return callGeminiAI($prompt, $systemPrompt, $geminiKey, $base64Image);
        }
        if ($groqKey && strpos($groqKey, 'YOUR_') === false) {
            return callGroqAI_Internal($prompt, $systemPrompt, $groqKey, $base64Image);
        }
        // Fallback to Gemini if Groq failed or not set
        if ($geminiKey && strpos($geminiKey, 'YOUR_') === false) {
            return callGeminiAI($prompt, $systemPrompt, $geminiKey, $base64Image);
        }
    }

    // 2. Default Text Flow: Use Groq if available
    if ($groqKey && strpos($groqKey, 'YOUR_') === false) {
        return callGroqAI_Internal($prompt, $systemPrompt, $groqKey, '');
    }

    // 3. Last fallback
    if ($geminiKey && strpos($geminiKey, 'YOUR_') === false) {
        return callGeminiAI($prompt, $systemPrompt, $geminiKey, '');
    }

    return [
        'success' => false,
        'text' => "⚠️ AI API Key is not set.\n\nPlease go to **Admin > API Settings** and enter your Groq or Gemini API Key."
    ];
}

/**
 * Gemini API Implementation
 */
function callGeminiAI(string $prompt, string $systemPrompt, string $apiKey, string $base64Image = ''): array
{
    $fullPrompt = $systemPrompt ? $systemPrompt . "\n\n" . $prompt : $prompt;
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;

    $parts = [['text' => $fullPrompt]];

    if ($base64Image) {
        // Remove data URI scheme if present
        if (preg_match('/^data:([\w\/]+);base64,/', $base64Image, $matches)) {
            $mimeType = $matches[1];
            $data = substr($base64Image, strpos($base64Image, ',') + 1);
        } else {
            $mimeType = 'image/jpeg'; // Default
            $data = $base64Image;
        }

        $parts[] = [
            'inline_data' => [
                'mime_type' => $mimeType,
                'data' => $data
            ]
        ];
    }

    $payload = [
        'contents' => [[
            'parts' => $parts
        ]],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 2048,
        ]
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $decoded = json_decode($response, true);
        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';
        return ['success' => true, 'text' => $text];
    }

    $error = json_decode($response, true);
    $msg = $error['error']['message'] ?? "Unknown Error";
    return ['success' => false, 'text' => "Gemini API Error: $msg (HTTP $httpCode)"];
}

/**
 * Groq API Implementation (OpenAI compatible)
 */
function callGroqAI_Internal(string $prompt, string $systemPrompt, string $apiKey, string $base64Image = ''): array
{
    $url = 'https://api.groq.com/openai/v1/chat/completions';
    
    $content = [];
    $content[] = ['type' => 'text', 'text' => $prompt];

    if ($base64Image) {
        // Groq supports vision on some models
        if (!str_starts_with($base64Image, 'data:')) {
            $base64Image = 'data:image/jpeg;base64,' . $base64Image;
        }
        $content[] = [
            'type' => 'image_url',
            'image_url' => ['url' => $base64Image]
        ];
    }

    $payload = [
        'model' => $base64Image ? 'llama-3.2-90b-vision-preview' : 'llama-3.3-70b-versatile',
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt ?: 'You are a helpful academic assistant for Metropolitan University Sylhet Software Engineering students.'],
            ['role' => 'user', 'content' => $content]
        ],
        'temperature' => 0.7,
        'max_tokens' => 2048
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $decoded = json_decode($response, true);
        $text = $decoded['choices'][0]['message']['content'] ?? '';
        return ['success' => true, 'text' => $text];
    }

    $error = json_decode($response, true);
    $msg = $error['error']['message'] ?? "Unknown Error";

    return ['success' => false, 'text' => "Groq API Error: $msg (HTTP $httpCode)"];
}
