<?php
// pages/signup.php
require_once __DIR__ . '/../includes/auth.php';
if (isLoggedIn()) { header('Location: dashboard.php'); exit(); }

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = clean($_POST['name'] ?? '');
    $email     = clean($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm'] ?? '';
    $studentId = clean($_POST['student_id'] ?? '');
    $batch     = clean($_POST['batch'] ?? '');
    $semester  = (int)($_POST['semester'] ?? 1);

    if (!$name || !$email || !$password) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $result = registerUser($name, $email, $password, $studentId, $batch, $semester);
        if ($result['success']) {
            setFlash('success', 'Account created! Please log in.');
            header('Location: login.php');
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
<title>Sign Up — EduSync MU</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root {
    --bg:#0a0e1a; --card:#111827; --border:#1e2d45;
    --accent:#22d3ee; --accent2:#818cf8;
    --text:#e2e8f0; --muted:#64748b;
    --error:#f87171;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;}
.bg-grid{position:fixed;inset:0;z-index:0;background-image:linear-gradient(rgba(34,211,238,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(34,211,238,.04) 1px,transparent 1px);background-size:40px 40px;}
.glow{position:fixed;width:500px;height:500px;border-radius:50%;filter:blur(120px);opacity:.12;pointer-events:none;}
.glow-1{background:var(--accent);top:-150px;right:-100px;}
.glow-2{background:var(--accent2);bottom:-150px;left:-100px;}
.card{position:relative;z-index:1;background:var(--card);border:1px solid var(--border);border-radius:20px;padding:44px 40px;width:100%;max-width:520px;box-shadow:0 25px 60px rgba(0,0,0,.5);animation:fadeUp .5s ease both;}
@keyframes fadeUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
.logo{font-family:'Syne',sans-serif;font-size:26px;font-weight:800;background:linear-gradient(135deg,var(--accent),var(--accent2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:4px;}
.subtitle{color:var(--muted);font-size:14px;margin-bottom:28px;}
.badge{display:inline-block;background:rgba(34,211,238,.1);border:1px solid rgba(34,211,238,.2);color:var(--accent);font-size:11px;padding:3px 10px;border-radius:20px;margin-bottom:12px;letter-spacing:.5px;}
.row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
label{font-size:13px;color:var(--muted);display:block;margin-bottom:6px;}
input,select{width:100%;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:12px 14px;color:var(--text);font-size:14px;font-family:inherit;outline:none;transition:border-color .2s,box-shadow .2s;margin-bottom:16px;}
input:focus,select:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(34,211,238,.1);}
select option{background:var(--card);}
button{width:100%;background:linear-gradient(135deg,var(--accent),var(--accent2));border:none;border-radius:10px;padding:14px;color:#0a0e1a;font-size:16px;font-weight:700;font-family:'Syne',sans-serif;cursor:pointer;margin-top:4px;transition:opacity .2s,transform .1s;}
button:hover{opacity:.9;transform:translateY(-1px);}
.error-box{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:var(--error);border-radius:10px;padding:12px 16px;font-size:14px;margin-bottom:20px;}
.link-row{text-align:center;margin-top:20px;font-size:14px;color:var(--muted);}
.link-row a{color:var(--accent);text-decoration:none;font-weight:500;}
.section-title{font-size:11px;letter-spacing:1px;color:var(--muted);text-transform:uppercase;margin-bottom:14px;margin-top:4px;padding-top:16px;border-top:1px solid var(--border);}
</style>
</head>
<body>
<div class="bg-grid"></div>
<div class="glow glow-1"></div>
<div class="glow glow-2"></div>
<div class="card">
    <div class="badge">MU SYLHET · SE DEPT</div>
    <div class="logo">EduSync</div>
    <div class="subtitle">Create your free student account</div>

    <?php if ($error): ?>
    <div class="error-box">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <div class="row">
            <div>
                <label>Full Name *</label>
                <input type="text" name="name" placeholder="Your full name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>
            <div>
                <label>Email Address *</label>
                <input type="email" name="email" placeholder="you@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
        </div>
        <div class="row">
            <div>
                <label>Password *</label>
                <input type="password" name="password" placeholder="Min 6 characters" required>
            </div>
            <div>
                <label>Confirm Password *</label>
                <input type="password" name="confirm" placeholder="Repeat password" required>
            </div>
        </div>

        <div class="section-title">📋 Academic Info (Optional)</div>
        <div class="row">
            <div>
                <label>Student ID</label>
                <input type="text" name="student_id" placeholder="e.g. 2021-SE-001" value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>">
            </div>
            <div>
                <label>Batch</label>
                <input type="text" name="batch" placeholder="e.g. 2021" value="<?= htmlspecialchars($_POST['batch'] ?? '') ?>">
            </div>
        </div>
        <div>
            <label>Current Semester</label>
            <select name="semester">
                <?php for ($i = 1; $i <= 8; $i++): ?>
                <option value="<?= $i ?>" <?= (($_POST['semester'] ?? 1) == $i) ? 'selected' : '' ?>>
                    Semester <?= $i ?> (Year <?= ceil($i/2) ?>)
                </option>
                <?php endfor; ?>
            </select>
        </div>

        <button type="submit">Create Account →</button>
    </form>
    <div class="link-row">
        Already have an account? <a href="login.php">Sign in</a>
    </div>
</div>
</body>
</html>
