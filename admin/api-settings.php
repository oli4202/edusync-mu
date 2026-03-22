<?php
// admin/api-settings.php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
$user = currentUser();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $claude = $_POST['claude'] ?? '';
    $gemini = $_POST['gemini'] ?? '';
    $hf = $_POST['hf'] ?? '';

    // Update the api-keys.php file
    $content = "<?php\n\n\$api_keys = [\n    'CLAUDE_API_KEY' => '" . addslashes($claude) . "',\n    'GEMINI_API_KEY' => '" . addslashes($gemini) . "',\n    'HF_API_KEY' => '" . addslashes($hf) . "',\n];";
    file_put_contents(__DIR__ . '/../config/api-keys.php', $content);
    $message = 'API keys updated successfully!';
}

// Read current keys
include __DIR__ . '/../config/api-keys.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>API Settings — EduSync MU</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root{--bg:#0a0e1a;--card:#111827;--card2:#0f172a;--border:#1e2d45;--accent:#22d3ee;--accent2:#818cf8;--accent3:#34d399;--warn:#fbbf24;--danger:#f87171;--text:#e2e8f0;--muted:#64748b;}
*{margin:0;padding:0;box-sizing:border-box;}body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;padding:32px;}
.header{display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;}
.logo{font-family:'Syne',sans-serif;font-size:22px;font-weight:800;background:linear-gradient(135deg,var(--accent),var(--accent2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.badge-admin{background:rgba(248,113,113,.15);border:1px solid rgba(248,113,113,.3);color:var(--danger);font-size:12px;padding:4px 12px;border-radius:20px;}
.nav-link{color:var(--accent);text-decoration:none;font-size:14px;}
.form-card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:24px;max-width:600px;margin:0 auto;}
.form-group{margin-bottom:20px;}
.label{font-family:'Syne',sans-serif;font-size:14px;font-weight:600;margin-bottom:8px;display:block;}
.input{width:100%;background:var(--card2);border:1px solid var(--border);border-radius:8px;padding:12px;font-size:14px;color:var(--text);}
.btn{padding:12px 24px;border-radius:8px;font-size:14px;font-weight:600;font-family:'Syne',sans-serif;cursor:pointer;border:none;transition:all .2s;}
.btn-primary{background:var(--accent);color:#0a0e1a;}
.btn-primary:hover{background:#0c9cb1;}
.message{background:rgba(52,211,153,.15);border:1px solid rgba(52,211,153,.3);color:var(--accent3);padding:12px;border-radius:8px;margin-bottom:20px;}
</style>
</head>
<body>
<div class="header">
    <div>
        <div class="logo">EduSync API Settings</div>
        <div style="font-size:13px;color:var(--muted);margin-top:2px;">Metropolitan University Sylhet · SE Department</div>
    </div>
    <div style="display:flex;align-items:center;gap:14px;">
        <span class="badge-admin">🛡️ Admin</span>
        <a href="index.php" class="nav-link">← Back to Admin</a>
    </div>
</div>

<div class="form-card">
    <?php if ($message): ?>
    <div class="message">✅ <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label class="label" for="claude">Claude API Key</label>
            <input type="text" id="claude" name="claude" class="input" value="<?= htmlspecialchars($api_keys['CLAUDE_API_KEY']) ?>" placeholder="Enter Claude API Key">
        </div>
        <div class="form-group">
            <label class="label" for="gemini">Gemini API Key</label>
            <input type="text" id="gemini" name="gemini" class="input" value="<?= htmlspecialchars($api_keys['GEMINI_API_KEY']) ?>" placeholder="Enter Gemini API Key">
        </div>
        <div class="form-group">
            <label class="label" for="hf">Hugging Face API Key</label>
            <input type="text" id="hf" name="hf" class="input" value="<?= htmlspecialchars($api_keys['HF_API_KEY']) ?>" placeholder="Enter Hugging Face API Key">
        </div>
        <button type="submit" class="btn btn-primary">Save API Keys</button>
    </form>
</div>
</body>
</html>