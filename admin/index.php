<?php
// admin/index.php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
$user = currentUser();
$db   = getDB();

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $type   = $_POST['type'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'approve' && $type === 'question') {
        $db->prepare("UPDATE questions SET is_approved=1, approved_by=?, approved_at=NOW() WHERE id=?")
           ->execute([$user['id'], $id]);
    } elseif ($action === 'reject' && $type === 'question') {
        $db->prepare("DELETE FROM questions WHERE id=?")->execute([$id]);
    } elseif ($action === 'approve' && $type === 'answer') {
        $db->prepare("UPDATE answers SET is_approved=1, approved_by=? WHERE id=?")->execute([$user['id'], $id]);
    } elseif ($action === 'reject' && $type === 'answer') {
        $db->prepare("DELETE FROM answers WHERE id=?")->execute([$id]);
    }
    header('Location: index.php'); exit();
}

// Pending questions
$pendingQ = $db->query("SELECT q.*, c.name AS course_name, c.code, u.name AS submitter FROM questions q JOIN courses c ON q.course_id=c.id LEFT JOIN users u ON q.submitted_by=u.id WHERE q.is_approved=0 ORDER BY q.created_at DESC")->fetchAll();

// Pending answers
$pendingA = $db->query("SELECT a.*, q.question_text, u.name AS author FROM answers a JOIN questions q ON a.question_id=q.id LEFT JOIN users u ON a.user_id=u.id WHERE a.is_approved=0 ORDER BY a.created_at DESC")->fetchAll();

// Stats
$stats = [
    'users'     => $db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn(),
    'questions' => $db->query("SELECT COUNT(*) FROM questions WHERE is_approved=1")->fetchColumn(),
    'answers'   => $db->query("SELECT COUNT(*) FROM answers WHERE is_approved=1")->fetchColumn(),
    'groups'    => $db->query("SELECT COUNT(*) FROM study_groups")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel — EduSync MU</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root{--bg:#0a0e1a;--card:#111827;--card2:#0f172a;--border:#1e2d45;--accent:#22d3ee;--accent2:#818cf8;--accent3:#34d399;--warn:#fbbf24;--danger:#f87171;--text:#e2e8f0;--muted:#64748b;}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;padding:32px;}
.header{display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;}
.logo{font-family:'Syne',sans-serif;font-size:22px;font-weight:800;background:linear-gradient(135deg,var(--accent),var(--accent2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.badge-admin{background:rgba(248,113,113,.15);border:1px solid rgba(248,113,113,.3);color:var(--danger);font-size:12px;padding:4px 12px;border-radius:20px;}
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;}
.stat{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:18px;}
.stat-val{font-family:'Syne',sans-serif;font-size:28px;font-weight:800;color:var(--accent);}
.stat-lbl{font-size:12px;color:var(--muted);margin-top:4px;}
.section-title{font-family:'Syne',sans-serif;font-size:16px;font-weight:700;margin-bottom:16px;display:flex;align-items:center;gap:10px;}
.pending-badge{background:rgba(251,191,36,.15);color:var(--warn);font-size:12px;padding:2px 10px;border-radius:20px;}
.item-card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:18px;margin-bottom:12px;}
.item-meta{font-size:12px;color:var(--muted);margin-bottom:8px;}
.item-text{font-size:14px;line-height:1.6;margin-bottom:14px;}
.item-actions{display:flex;gap:10px;}
.btn{padding:8px 18px;border-radius:8px;font-size:13px;font-weight:600;font-family:'Syne',sans-serif;cursor:pointer;border:none;transition:all .2s;}
.btn-approve{background:rgba(52,211,153,.15);border:1px solid rgba(52,211,153,.3);color:var(--accent3);}
.btn-approve:hover{background:var(--accent3);color:#0a0e1a;}
.btn-reject{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:var(--danger);}
.btn-reject:hover{background:var(--danger);color:#fff;}
.empty{color:var(--muted);font-size:14px;text-align:center;padding:24px;background:var(--card);border:1px solid var(--border);border-radius:12px;}
.nav-link{color:var(--accent);text-decoration:none;font-size:14px;}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px;}
@media(max-width:700px){.stats-grid{grid-template-columns:repeat(2,1fr);}.grid-2{grid-template-columns:1fr;}}
</style>
</head>
<body>
<div class="header">
    <div>
        <div class="logo">EduSync Admin</div>
        <div style="font-size:13px;color:var(--muted);margin-top:2px;">Metropolitan University Sylhet · SE Department</div>
    </div>
    <div style="display:flex;align-items:center;gap:14px;">
        <span class="badge-admin">🛡️ Admin</span>
        <a href="manage-attendance.php" class="nav-link">📋 Attendance</a>
        <a href="api-settings.php" class="nav-link">API Settings</a>
        <a href="../pages/dashboard.php" class="nav-link">← Back to Dashboard</a>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat"><div class="stat-val"><?= $stats['users'] ?></div><div class="stat-lbl">Students</div></div>
    <div class="stat"><div class="stat-val"><?= $stats['questions'] ?></div><div class="stat-lbl">Approved Questions</div></div>
    <div class="stat"><div class="stat-val"><?= $stats['answers'] ?></div><div class="stat-lbl">Approved Answers</div></div>
    <div class="stat"><div class="stat-val"><?= $stats['groups'] ?></div><div class="stat-lbl">Study Groups</div></div>
</div>

<div class="grid-2">
    <!-- Pending Questions -->
    <div>
        <div class="section-title">
            📖 Pending Questions
            <span class="pending-badge"><?= count($pendingQ) ?> pending</span>
        </div>
        <?php if (empty($pendingQ)): ?>
        <div class="empty">✅ No pending questions.</div>
        <?php else: ?>
        <?php foreach ($pendingQ as $q): ?>
        <div class="item-card">
            <div class="item-meta">
                <strong><?= htmlspecialchars($q['code']) ?></strong> ·
                <?= htmlspecialchars($q['course_name']) ?> ·
                <?= $q['exam_year'] ? 'Year '.$q['exam_year'] : '' ?> <?= $q['exam_semester'] ? $q['exam_semester'].' Sem' : '' ?> ·
                <?= $q['marks'] ?> marks ·
                Submitted by <?= htmlspecialchars($q['submitter'] ?? 'Unknown') ?>
            </div>
            <div class="item-text"><?= htmlspecialchars(mb_substr($q['question_text'], 0, 250)) ?><?= strlen($q['question_text']) > 250 ? '...' : '' ?></div>
            <div class="item-actions">
                <form method="POST" style="display:inline">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="type" value="question">
                    <input type="hidden" name="id" value="<?= $q['id'] ?>">
                    <button type="submit" class="btn btn-approve">✅ Approve</button>
                </form>
                <form method="POST" style="display:inline" onsubmit="return confirm('Delete this question?')">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="type" value="question">
                    <input type="hidden" name="id" value="<?= $q['id'] ?>">
                    <button type="submit" class="btn btn-reject">🗑 Reject</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pending Answers -->
    <div>
        <div class="section-title">
            💬 Pending Answers
            <span class="pending-badge"><?= count($pendingA) ?> pending</span>
        </div>
        <?php if (empty($pendingA)): ?>
        <div class="empty">✅ No pending answers.</div>
        <?php else: ?>
        <?php foreach ($pendingA as $a): ?>
        <div class="item-card">
            <div class="item-meta">
                Answering: <em><?= htmlspecialchars(mb_substr($a['question_text'],0,80)) ?>...</em> ·
                By <?= htmlspecialchars($a['author'] ?? 'Unknown') ?>
            </div>
            <div class="item-text"><?= htmlspecialchars(mb_substr($a['answer_text'], 0, 200)) ?><?= strlen($a['answer_text']) > 200 ? '...' : '' ?></div>
            <div class="item-actions">
                <form method="POST" style="display:inline">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="type" value="answer">
                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                    <button type="submit" class="btn btn-approve">✅ Approve</button>
                </form>
                <form method="POST" style="display:inline" onsubmit="return confirm('Delete this answer?')">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="type" value="answer">
                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                    <button type="submit" class="btn btn-reject">🗑 Reject</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
