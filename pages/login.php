<?php
// pages/login.php
require_once __DIR__ . '/../includes/auth.php';
if (isLoggedIn()) { header('Location: dashboard.php'); exit(); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) {
        $error = 'Please fill in all fields.';
    } else {
        $result = loginUser($email, $password);
        if ($result['success']) {
            header('Location: dashboard.php');
            exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — EduSync MU</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root {
    --bg: #0a0e1a;
    --card: #111827;
    --border: #1e2d45;
    --accent: #22d3ee;
    --accent2: #818cf8;
    --text: #e2e8f0;
    --muted: #64748b;
    --error: #f87171;
    --success: #34d399;
}
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.bg-grid {
    position: fixed; inset: 0; z-index: 0;
    background-image:
        linear-gradient(rgba(34,211,238,.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(34,211,238,.04) 1px, transparent 1px);
    background-size: 40px 40px;
}
.glow {
    position: fixed;
    width: 600px; height: 600px;
    border-radius: 50%;
    filter: blur(120px);
    opacity: .15;
    pointer-events: none;
}
.glow-1 { background: var(--accent); top: -200px; left: -200px; }
.glow-2 { background: var(--accent2); bottom: -200px; right: -200px; }

.card {
    position: relative; z-index: 1;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 48px 40px;
    width: 100%; max-width: 440px;
    box-shadow: 0 25px 60px rgba(0,0,0,.5);
    animation: fadeUp .5s ease both;
}
@keyframes fadeUp {
    from { opacity:0; transform: translateY(24px); }
    to   { opacity:1; transform: translateY(0); }
}
.logo {
    font-family: 'Syne', sans-serif;
    font-size: 28px; font-weight: 800;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 4px;
}
.subtitle { color: var(--muted); font-size: 14px; margin-bottom: 36px; }
.badge {
    display: inline-block;
    background: rgba(34,211,238,.1);
    border: 1px solid rgba(34,211,238,.2);
    color: var(--accent);
    font-size: 11px;
    padding: 3px 10px;
    border-radius: 20px;
    margin-bottom: 12px;
    letter-spacing: .5px;
}
label { font-size: 13px; color: var(--muted); display: block; margin-bottom: 6px; }
input {
    width: 100%;
    background: rgba(255,255,255,.03);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 13px 16px;
    color: var(--text);
    font-size: 15px;
    font-family: inherit;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
    margin-bottom: 18px;
}
input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(34,211,238,.1);
}
.field { margin-bottom: 4px; }
button[type=submit] {
    width: 100%;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    border: none;
    border-radius: 10px;
    padding: 14px;
    color: #0a0e1a;
    font-size: 16px;
    font-weight: 700;
    font-family: 'Syne', sans-serif;
    cursor: pointer;
    margin-top: 8px;
    transition: opacity .2s, transform .1s;
}
button[type=submit]:hover { opacity: .9; transform: translateY(-1px); }
.error-box {
    background: rgba(248,113,113,.1);
    border: 1px solid rgba(248,113,113,.3);
    color: var(--error);
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 14px;
    margin-bottom: 20px;
}
.link-row { text-align: center; margin-top: 24px; font-size: 14px; color: var(--muted); }
.link-row a { color: var(--accent); text-decoration: none; font-weight: 500; }
.link-row a:hover { text-decoration: underline; }
.divider { border: none; border-top: 1px solid var(--border); margin: 24px 0; }
</style>
</head>
<body>
<div class="bg-grid"></div>
<div class="glow glow-1"></div>
<div class="glow glow-2"></div>

<div class="card">
    <div class="badge">MU SYLHET · SE DEPT</div>
    <div class="logo">EduSync</div>
    <div class="subtitle">Sign in to your student dashboard</div>

    <?php if ($error): ?>
    <div class="error-box">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <div class="field">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email"
                placeholder="you@student.mu.ac.bd"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                placeholder="••••••••" required>
        </div>
        <button type="submit">Sign In →</button>
    </form>

    <hr class="divider">
    <div class="link-row">
        Don't have an account? <a href="signup.php">Create one free</a>
    </div>
</div>
</body>
</html>
