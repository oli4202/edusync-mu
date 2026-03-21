<?php
// ============================================================
//  EduSync — Auth & Helper Functions
//  includes/auth.php
// ============================================================

require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Check if logged in ──────────────────────────────────────
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// ── Require login — redirect if not ────────────────────────
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/pages/login.php');
        exit();
    }
}

// ── Require admin ────────────────────────────────────────────
function requireAdmin()
{
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        header('Location: ' . SITE_URL . '/pages/dashboard.php');
        exit();
    }
}

// ── Get current user ─────────────────────────────────────────
function currentUser()
{
    if (!isLoggedIn())
        return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// ── Register user ────────────────────────────────────────────
function registerUser($name, $email, $password, $studentId = '', $batch = '', $semester = 1)
{
    $db = getDB();
    // Check if email exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email already registered.'];
    }
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (name, email, password, student_id, batch, semester) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $hashed, $studentId, $batch, $semester]);
    return ['success' => true, 'id' => $db->lastInsertId()];
}

// ── Login user ───────────────────────────────────────────────
function loginUser($email, $password)
{
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        // Update streak & last_active
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $streak = $user['streak'];
        if ($user['last_active'] === $yesterday) {
            $streak++;
        }
        elseif ($user['last_active'] !== $today) {
            $streak = 1;
        }
        $db->prepare("UPDATE users SET streak=?, last_active=? WHERE id=?")
            ->execute([$streak, $today, $user['id']]);
        return ['success' => true, 'user' => $user];
    }
    return ['success' => false, 'message' => 'Invalid email or password.'];
}

// ── Logout ───────────────────────────────────────────────────
function logoutUser()
{
    session_destroy();
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit();
}

// ── Sanitize input ───────────────────────────────────────────
function clean($input)
{
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// ── Flash messages ───────────────────────────────────────────
function setFlash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ── Main AI Dispatcher ───────────────────────────────────────
function callAI($prompt, $systemPrompt = '')
{
    return callClaudeAPI($prompt, $systemPrompt);
}

// ── AI API — Gemini (free) + Claude (fallback) ────────────────
function callClaudeAPI($prompt, $systemPrompt = '')
{
    // 1. Try Gemini first — FREE (1500 req/day, no credit card)
    $geminiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
    if ($geminiKey && $geminiKey !== 'YOUR_GEMINI_API_KEY_HERE') {
        $result = callGeminiAPI($prompt, $systemPrompt, $geminiKey);
        if ($result['success'])
            return $result;
    }

    // 2. Try Hugging Face next (FREE)
    $hfKey = defined('HF_API_KEY') ? HF_API_KEY : '';
    if ($hfKey && $hfKey !== 'YOUR_HF_API_KEY_HERE') {
        $result = callHuggingFaceAPI($prompt, $systemPrompt, $hfKey);
        if ($result['success'])
            return $result;
    }

    // 3. Fallback to Claude (Paid/Key required)
    $claudeKey = defined('CLAUDE_API_KEY') ? CLAUDE_API_KEY : '';
    if (!$claudeKey || $claudeKey === 'YOUR_CLAUDE_API_KEY_HERE') {
        return [
            'success' => false,
            'text' => "⚠️ No Working AI API Key Configured.\n\n" .
            "To enable AI features for FREE:\n" .
            "1. Google Gemini (Recommended): aistudio.google.com/apikey\n" .
            "2. Hugging Face: huggingface.co/settings/tokens\n\n" .
            "Paste your key in `config/database.php` and you're good to go!"
        ];
    }

    // Claude API call
    $data = [
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1500,
        'messages' => [['role' => 'user', 'content' => $prompt]]
    ];
    if ($systemPrompt)
        $data['system'] = $systemPrompt;

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-api-key: ' . $claudeKey,
            'anthropic-version: 2023-06-01'
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 30,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    $decoded = json_decode($response, true);
    if (isset($decoded['content'][0]['text'])) {
        return ['success' => true, 'text' => $decoded['content'][0]['text']];
    }
    return ['success' => false, 'text' => 'AI request failed. Check your API key.'];
}

// ── Google Gemini API — tries multiple models until one works ─
function callGeminiAPI($prompt, $systemPrompt = '', $apiKey = '') {
    $fullPrompt = $systemPrompt
        ? $systemPrompt . "\n\n" . $prompt
        : $prompt;

    $data = [
        'contents' => [[
            'parts' => [['text' => $fullPrompt]]
        ]],
        'generationConfig' => [
            'temperature'     => 0.7,
            'maxOutputTokens' => 1500,
        ]
    ];

    // Try models in order — first working one wins
    $models = [
        'gemini-2.0-flash',
        'gemini-2.0-flash-lite',
        'gemini-1.5-flash-latest',
        'gemini-1.5-flash-8b',
        'gemini-pro',
    ];

    $base = 'https://generativelanguage.googleapis.com/v1beta/models/';

    foreach ($models as $model) {
        $url = $base . $model . ':generateContent?key=' . $apiKey;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $decoded = json_decode($response, true);
            $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if ($text) {
                return ['success' => true, 'text' => $text];
            }
        }
    }
    return [
        'success' => false,
        'text'    => 'Gemini API key is set but all models returned an error. ' .
                     'Please check your key at aistudio.google.com/apikey'
    ];
}

// ── Hugging Face API (FREE Inference) ────────────────────────
function callHuggingFaceAPI($prompt, $systemPrompt = '', $apiKey = '')
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

    // Using Mistral 7B as a reliable free model
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
        if ($text) {
            return ['success' => true, 'text' => trim($text)];
        }
    }

    return ['success' => false, 'text' => 'Hugging Face API error: HTTP ' . $httpCode];
}

// ── Time ago helper ──────────────────────────────────────────
function timeAgo($datetime)
{
    $time = time() - strtotime($datetime);
    if ($time < 60)
        return 'just now';
    if ($time < 3600)
        return floor($time / 60) . ' min ago';
    if ($time < 86400)
        return floor($time / 3600) . ' hr ago';
    if ($time < 604800)
        return floor($time / 86400) . ' days ago';
    return date('M j, Y', strtotime($datetime));
}