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

    // 2. Fallback to Claude if key is set
    $claudeKey = defined('CLAUDE_API_KEY') ? CLAUDE_API_KEY : '';
    if (!$claudeKey || $claudeKey === 'YOUR_CLAUDE_API_KEY_HERE') {
        return [
            'success' => false,
            'text' => "⚠️ No AI API key configured.\n\n" .
            "To enable AI features (FREE):\n" .
            "1. Go to: aistudio.google.com/apikey\n" .
            "2. Click 'Create API Key'\n" .
            "3. Copy the key\n" .
            "4. Open: config/database.php\n" .
            "5. Set: define('GEMINI_API_KEY', 'your-key-here');\n\n" .
            "Gemini API is completely FREE with 1500 requests/day!"
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

// ── Google Gemini API (FREE tier) ────────────────────────────
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
            'maxOutputTokens' => 1500,
        ]
    ];

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey;

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

    if ($httpCode !== 200) {
        return ['success' => false, 'text' => 'Gemini API error: HTTP ' . $httpCode];
    }

    $decoded = json_decode($response, true);
    $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';
    if ($text) {
        return ['success' => true, 'text' => $text];
    }
    return ['success' => false, 'text' => 'Gemini returned empty response.'];
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