<?php
// pages/subjects.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$user = currentUser();
$db   = getDB();
$currentPage = 'subjects';

// Handle add/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') === 'add') {
        $stmt = $db->prepare("INSERT INTO subjects (user_id, name, code, color, semester, target_hours_per_week) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$user['id'], clean($_POST['name']), clean($_POST['code'] ?? ''), $_POST['color'] ?? '#4f46e5', intval($_POST['semester'] ?? $user['semester']), floatval($_POST['target_hours'] ?? 5)]);
        header('Location: subjects.php'); exit;
    }
    if (($_POST['action'] ?? '') === 'delete') {
        $db->prepare("DELETE FROM subjects WHERE id=? AND user_id=?")->execute([$_POST['subject_id'], $user['id']]);
        header('Location: subjects.php'); exit;
    }
}

// Fetch subjects with stats
$stmt = $db->prepare("
    SELECT s.*,
        (SELECT COUNT(*) FROM tasks WHERE subject_id=s.id AND status!='done') AS pending_tasks,
        (SELECT COUNT(*) FROM tasks WHERE subject_id=s.id AND status='done') AS done_tasks,
        (SELECT COALESCE(SUM(hours),0) FROM study_logs WHERE subject_id=s.id AND WEEK(logged_date)=WEEK(NOW())) AS week_hours
    FROM subjects s WHERE s.user_id=? ORDER BY s.name
");
$stmt->execute([$user['id']]);
$subjects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Subjects — EduSync MU</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.subjects-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:20px; margin-top:20px; }
.subject-card { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:22px; position:relative; overflow:hidden; transition:all .2s; }
.subject-card:hover { border-color:var(--accent); }
.subject-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; }
.subject-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
.subject-name { font-family:'Syne',sans-serif; font-size:16px; font-weight:700; }
.subject-code { font-size:11px; padding:3px 10px; border-radius:10px; background:rgba(34,211,238,.1); color:var(--accent); }
.subject-stats { display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-top:14px; }
.subject-stat { text-align:center; }
.subject-stat-val { font-family:'Syne',sans-serif; font-size:18px; font-weight:700; }
.subject-stat-label { font-size:10px; color:var(--muted); margin-top:2px; }
.progress-bar { height:6px; background:rgba(255,255,255,.05); border-radius:3px; margin-top:14px; overflow:hidden; }
.progress-fill { height:100%; border-radius:3px; transition:width .3s; }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:200; align-items:center; justify-content:center; }
.modal-overlay.active { display:flex; }
.modal { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:28px; width:100%; max-width:440px; }
.modal h3 { font-family:'Syne',sans-serif; font-size:18px; font-weight:700; margin-bottom:20px; }
.form-group { margin-bottom:16px; }
.color-options { display:flex; gap:8px; flex-wrap:wrap; }
.color-opt { width:28px; height:28px; border-radius:50%; cursor:pointer; border:2px solid transparent; transition:all .2s; }
.color-opt:hover, .color-opt.selected { border-color:#fff; transform:scale(1.1); }
</style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">📚 Subjects</div>
            <div class="page-sub">Manage your courses and track study progress</div>
        </div>
        <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('active')">+ Add Subject</button>
    </div>

    <?php if (empty($subjects)): ?>
    <div class="card" style="text-align:center;padding:60px 20px;">
        <div style="font-size:48px;margin-bottom:16px;">📚</div>
        <div style="font-family:'Syne',sans-serif;font-size:18px;font-weight:700;margin-bottom:8px;">No subjects yet</div>
        <div style="color:var(--muted);font-size:14px;margin-bottom:20px;">Add your current semester courses to start tracking.</div>
        <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('active')">+ Add Your First Subject</button>
    </div>
    <?php else: ?>
    <div class="subjects-grid">
        <?php foreach ($subjects as $s):
            $totalTasks = $s['pending_tasks'] + $s['done_tasks'];
            $pct = $totalTasks > 0 ? round($s['done_tasks'] / $totalTasks * 100) : 0;
        ?>
        <div class="subject-card" style="--sc:<?= htmlspecialchars($s['color']) ?>">
            <div style="position:absolute;top:0;left:0;right:0;height:3px;background:<?= htmlspecialchars($s['color']) ?>"></div>
            <div class="subject-header">
                <div class="subject-name"><?= htmlspecialchars($s['name']) ?></div>
                <?php if ($s['code']): ?><span class="subject-code"><?= htmlspecialchars($s['code']) ?></span><?php endif; ?>
            </div>
            <div style="font-size:12px;color:var(--muted);margin-bottom:4px;">Semester <?= $s['semester'] ?> · Target: <?= $s['target_hours_per_week'] ?>h/week</div>
            <div class="subject-stats">
                <div class="subject-stat">
                    <div class="subject-stat-val" style="color:var(--warn)"><?= $s['pending_tasks'] ?></div>
                    <div class="subject-stat-label">Pending</div>
                </div>
                <div class="subject-stat">
                    <div class="subject-stat-val" style="color:var(--accent3)"><?= $s['done_tasks'] ?></div>
                    <div class="subject-stat-label">Done</div>
                </div>
                <div class="subject-stat">
                    <div class="subject-stat-val" style="color:var(--accent)"><?= number_format($s['week_hours'],1) ?></div>
                    <div class="subject-stat-label">Hours</div>
                </div>
            </div>
            <div class="progress-bar"><div class="progress-fill" style="width:<?= $pct ?>%;background:<?= htmlspecialchars($s['color']) ?>"></div></div>
            <div style="display:flex;justify-content:flex-end;margin-top:14px;">
                <form method="POST" onsubmit="return confirm('Delete this subject?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="subject_id" value="<?= $s['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline" style="font-size:11px;">🗑 Remove</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>

<div class="modal-overlay" id="addModal">
    <div class="modal">
        <h3>📚 Add Subject</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Subject Name *</label>
                <input type="text" name="name" required placeholder="e.g. Data Structures & Algorithms">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label>Course Code</label>
                    <input type="text" name="code" placeholder="e.g. SWE201">
                </div>
                <div class="form-group">
                    <label>Semester</label>
                    <input type="number" name="semester" min="1" max="8" value="<?= $user['semester'] ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Target Hours/Week</label>
                <input type="number" name="target_hours" step="0.5" min="0" max="40" value="5">
            </div>
            <div class="form-group">
                <label>Color</label>
                <input type="hidden" name="color" id="colorInput" value="#22d3ee">
                <div class="color-options">
                    <?php foreach(['#22d3ee','#818cf8','#34d399','#fbbf24','#f87171','#a78bfa','#f472b6','#fb923c'] as $c): ?>
                    <div class="color-opt" style="background:<?= $c ?>" onclick="document.getElementById('colorInput').value='<?= $c ?>';document.querySelectorAll('.color-opt').forEach(e=>e.classList.remove('selected'));this.classList.add('selected')"></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:10px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('addModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Subject</button>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('addModal').addEventListener('click',function(e){if(e.target===this)this.classList.remove('active');});
document.querySelector('.color-opt').classList.add('selected');
</script>
</body>
</html>
