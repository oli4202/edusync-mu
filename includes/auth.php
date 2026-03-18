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
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// ── Require login — redirect if not ────────────────────────
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/pages/login.php');
        exit();
    }
}

// ── Require admin ────────────────────────────────────────────
function requireAdmin() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        header('Location: ' . SITE_URL . '/pages/dashboard.php');
        exit();
    }
}

// ── Get current user ─────────────────────────────────────────
function currentUser() {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// ── Register user ────────────────────────────────────────────
function registerUser($name, $email, $password, $studentId = '', $batch = '', $semester = 1) {
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
function loginUser($email, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        // Update streak & last_active
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $streak = $user['streak'];
        if ($user['last_active'] === $yesterday) {
            $streak++;
        } elseif ($user['last_active'] !== $today) {
            $streak = 1;
        }
        $db->prepare("UPDATE users SET streak=?, last_active=? WHERE id=?")
           ->execute([$streak, $today, $user['id']]);
        return ['success' => true, 'user' => $user];
    }
    return ['success' => false, 'message' => 'Invalid email or password.'];
}

// ── Logout ───────────────────────────────────────────────────
function logoutUser() {
    session_destroy();
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit();
}

// ── Sanitize input ───────────────────────────────────────────
function clean($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// ── Flash messages ───────────────────────────────────────────
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ── Claude AI API Call ───────────────────────────────────────
function callClaudeAPI($prompt, $systemPrompt = '') {
    $apiKey = CLAUDE_API_KEY;
    
    // Provide a simulated response if no real API key is configured
    if (empty($apiKey) || $apiKey === 'YOUR_CLAUDE_API_KEY_HERE') {
        sleep(1); // Simulate network delay
        if (stripos($prompt, 'Summarize these notes') !== false) {
            return ['success' => true, 'text' => "📌 Key Points\n• This is a simulated AI summary.\n• Please configure CLAUDE_API_KEY in config/database.php to use the real AI.\n\n🧠 Core Concepts\n• AI Integration\n• UI/UX Testing\n\n⚡ Quick Facts to Remember\n• The Note Summarizer UI is fully functional! Add your Anthropic key to get real summaries."];
        }
        return ['success' => true, 'text' => "This is a simulated AI response.\nPlease add your actual CLAUDE_API_KEY in config/database.php to enable real AI processing by Claude 3.5 Sonnet.\n\nYour prompt length was: " . strlen($prompt) . " characters."];
    }
    
    $data = [
        'model'      => 'claude-3-5-sonnet-20241022',
        'max_tokens' => 1500,
        'messages'   => [['role' => 'user', 'content' => $prompt]]
    ];
    if ($systemPrompt) {
        $data['system'] = $systemPrompt;
    }
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'x-api-key: ' . $apiKey,
            'anthropic-version: 2023-06-01'
        ],
        CURLOPT_POSTFIELDS     => json_encode($data),
        CURLOPT_TIMEOUT        => 30,
    ]);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) return ['success' => false, 'text' => 'Curl Error: ' . $error];
    
    $decoded = json_decode($response, true);
    if (isset($decoded['content'][0]['text'])) {
        return ['success' => true, 'text' => $decoded['content'][0]['text']];
    }
    
    // Provide the actual API error if it fails (helps with debugging)
    $errorMsg = $decoded['error']['message'] ?? 'AI request failed.';
    return ['success' => false, 'text' => "API Error: " . $errorMsg];
}

// ── Time ago helper ──────────────────────────────────────────
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    if ($time < 60)      return 'just now';
    if ($time < 3600)    return floor($time/60) . ' min ago';
    if ($time < 86400)   return floor($time/3600) . ' hr ago';
    if ($time < 604800)  return floor($time/86400) . ' days ago';
    return date('M j, Y', strtotime($datetime));
}
