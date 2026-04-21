<?php
// ============================================================
//  EduSync — Auth & Helper Functions
//  includes/auth.php
// ============================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/api-keys.php';

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

// ── Main AI Dispatcher — Gemini 2.5 Flash ────────────────────────────
function callAI($prompt, $systemPrompt = '')
{
    return callClaudeAPI($prompt, $systemPrompt);
}

function callClaudeAPI($prompt, $systemPrompt = '')
{
    // Only use Gemini 2.5 Flash
    global $api_keys;
    $geminiKey = $api_keys['GEMINI_API_KEY'] ?? '';
    if ($geminiKey && $geminiKey !== 'YOUR_GEMINI_API_KEY_HERE' && $geminiKey !== 'YOUR_NEW_GEMINI_API_KEY_HERE' && $geminiKey !== 'YOUR_ACTUAL_GEMINI_API_KEY_HERE') {
        $result = callGeminiAPI($prompt, $systemPrompt, $geminiKey);
        if ($result['success'])
            return $result;
    }

    // No fallback - only Gemini Flash 3.5
    return [
        'success' => false,
        'text' => "❌ Gemini 2.5 Flash API Error\n\n" .
        "Please check:\n" .
        "1. Your Gemini API key is correctly set\n" .
        "2. You have quota remaining\n" .
        "3. Your key is not expired\n\n" .
        "Get a new key at: https://makersuite.google.com/app/apikey"
    ];
}

// ── Google Gemini 2.5 Flash API ─────────────────────────────
function callGeminiAPI($prompt, $systemPrompt = '', $apiKey = '')
{
    $fullPrompt = $systemPrompt
        ? $systemPrompt . "\n\n" . $prompt
        : $prompt;

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

    // Gemini model priority list (newest first)
    $models = [
        'gemini-2.5-flash', // Gemini 2.5 Flash (latest)
    ];

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
            if ($text) {
                return ['success' => true, 'text' => $text];
            }
        }
    }

    // Parse error message from last API response
    $errMsg = '';
    $decoded = json_decode($lastBody, true);
    if (isset($decoded['error']['message'])) {
        $errMsg = $decoded['error']['message'];
    }

    return [
        'success' => false,
        'text' => "Gemini API error (HTTP $lastCode)" . ($errMsg ? ": $errMsg" : '') . "\nCheck your API key at aistudio.google.com/apikey"
    ];
}

// ── Hugging Face API (FREE Inference) ────────────────────────
function callHuggingFaceAPI($prompt, $systemPrompt = '', $apiKey = '')
{
    global $api_keys;
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