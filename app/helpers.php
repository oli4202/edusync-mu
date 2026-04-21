<?php
// ============================================================
//  EduSync — Helper Functions
//  app/helpers.php
// ============================================================

/**
 * Generate URL with optional base path from environment.
 * Set BASE_PATH in .env for subdirectory deployments (e.g., /edusync).
 *
 * Examples:
 *   url('/login')            => '/login'           (no BASE_PATH)
 *   url('/login')            => '/edusync/login'   (BASE_PATH=/edusync)
 *   url('/assets/style.css') => '/edusync/assets/style.css'
 */
function url(string $path = ''): string
{
    $basePath = rtrim(getenv('BASE_PATH') ?: '', '/');
    $path = '/' . ltrim($path, '/');
    return $basePath . $path;
}

/**
 * Generate an asset URL.
 */
function asset(string $path): string
{
    return url('/assets/' . ltrim($path, '/'));
}

/**
 * Redirect to a URL and stop execution.
 */
function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

/**
 * Require the user to be logged in.
 * If not logged in, redirect to the login page and stop execution.
 *
 * @return array The logged-in user data from the session/DB
 */
function requireAuth(): array
{
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        redirect('/login');
    }
    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user) {
        \App\Core\Session::destroy();
        redirect('/login');
    }
    return $user;
}

/**
 * Require admin role.
 */
function requireAdmin(): array
{
    $user = requireAuth();
    if ($user['role'] !== 'admin') {
        redirect('/dashboard');
    }
    return $user;
}

/**
 * Check if user is logged in.
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Sanitize input.
 */
function clean(string $input): string
{
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Time ago helper.
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
 * Main AI Dispatcher — calls Gemini 2.5 Flash.
 */
function callAI(string $prompt, string $systemPrompt = ''): array
{
    $geminiKey = getenv('GEMINI_API_KEY') ?: '';
    if ($geminiKey && !in_array($geminiKey, ['', 'YOUR_GEMINI_API_KEY_HERE'])) {
        $result = callGeminiAPI($prompt, $systemPrompt, $geminiKey);
        if ($result['success']) return $result;
    }

    return [
        'success' => false,
        'text' => "❌ Gemini 2.5 Flash API Error\n\n" .
            "Please check:\n" .
            "1. Your Gemini API key is correctly set in .env\n" .
            "2. You have quota remaining\n" .
            "3. Your key is not expired\n\n" .
            "Get a new key at: https://makersuite.google.com/app/apikey"
    ];
}

/**
 * Google Gemini 2.5 Flash API.
 */
function callGeminiAPI(string $prompt, string $systemPrompt = '', string $apiKey = ''): array
{
    $fullPrompt = $systemPrompt ? $systemPrompt . "\n\n" . $prompt : $prompt;

    $data = [
        'contents' => [['parts' => [['text' => $fullPrompt]]]],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 2048,
            'topP' => 0.8,
            'topK' => 40,
        ]
    ];

    $models = ['gemini-2.5-flash'];
    $base = 'https://generativelanguage.googleapis.com/v1beta/models/';

    $lastCode = 0;
    $lastBody = '';

    foreach ($models as $model) {
        $url = $base . $model . ':generateContent?key=' . $apiKey;
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

        $lastCode = $httpCode;
        $lastBody = $response;

        if ($httpCode === 200) {
            $decoded = json_decode($response, true);
            $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if ($text) return ['success' => true, 'text' => $text];
        }
    }

    $errMsg = '';
    $decoded = json_decode($lastBody, true);
    if (isset($decoded['error']['message'])) $errMsg = $decoded['error']['message'];

    return [
        'success' => false,
        'text' => "Gemini API error (HTTP $lastCode)" . ($errMsg ? ": $errMsg" : '') . "\nCheck your API key at aistudio.google.com/apikey"
    ];
}

/**
 * Hugging Face API (FREE Inference).
 */
function callHuggingFaceAPI(string $prompt, string $systemPrompt = '', string $apiKey = ''): array
{
    if (!$apiKey || $apiKey === 'YOUR_HF_API_KEY_HERE') {
        return ['success' => false, 'text' => 'Hugging Face API key not set.'];
    }

    $fullPrompt = $systemPrompt ? "<s>[INST] $systemPrompt\n\n$prompt [/INST]" : "<s>[INST] $prompt [/INST]";

    $data = [
        'inputs' => $fullPrompt,
        'parameters' => [
            'max_new_tokens' => 1000,
            'temperature' => 0.7,
            'return_full_text' => false
        ]
    ];

    $url = 'https://api-inference.huggingface.co/models/mistralai/Mistral-7B-Instruct-v0.2';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $decoded = json_decode($response, true);
        $text = $decoded[0]['generated_text'] ?? '';
        if ($text) return ['success' => true, 'text' => trim($text)];
    }

    return ['success' => false, 'text' => 'Hugging Face API error: HTTP ' . $httpCode];
}
