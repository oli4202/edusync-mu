<?php
// pages/tasks.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$user = currentUser();
$db   = getDB();
$currentPage = 'tasks';

// Handle new task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $stmt = $db->prepare("INSERT INTO tasks (user_id, subject_id, title, description, priority, status, due_date) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([
            $user['id'],
            $_POST['subject_id'] ?: null,
            clean($_POST['title']),
            clean($_POST['description'] ?? ''),
            $_POST['priority'] ?? 'medium',
            'todo',
            $_POST['due_date'] ?: null
        ]);
        header('Location: tasks.php'); exit;
    }
    if ($_POST['action'] === 'status') {
        $stmt = $db->prepare("UPDATE tasks SET status=? WHERE id=? AND user_id=?");
        $stmt->execute([$_POST['status'], $_POST['task_id'], $user['id']]);
        header('Location: tasks.php'); exit;
    }
    if ($_POST['action'] === 'delete') {
        $stmt = $db->prepare("DELETE FROM tasks WHERE id=? AND user_id=?");
        $stmt->execute([$_POST['task_id'], $user['id']]);
        header('Location: tasks.php'); exit;
    }
}

// Fetch tasks
$tasks = $db->prepare("SELECT t.*, s.name AS subject_name, s.color FROM tasks t LEFT JOIN subjects s ON t.subject_id=s.id WHERE t.user_id=? ORDER BY FIELD(t.status,'todo','in_progress','done'), t.due_date ASC");
$tasks->execute([$user['id']]);
$allTasks = $tasks->fetchAll();

$todo = array_filter($allTasks, fn($t) => $t['status'] === 'todo');
$inProgress = array_filter($allTasks, fn($t) => $t['status'] === 'in_progress');
$done = array_filter($allTasks, fn($t) => $t['status'] === 'done');

// Subjects for dropdown
$subjects = $db->prepare("SELECT id, name, color FROM subjects WHERE user_id=? ORDER BY name");
$subjects->execute([$user['id']]);
$subjectList = $subjects->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tasks & Kanban — EduSync MU</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.kanban { display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px; margin-top:20px; }
.kanban-col { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:18px; min-height:400px; }
.kanban-header { font-family:'Syne',sans-serif; font-size:14px; font-weight:700; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
.kanban-count { font-size:11px; background:rgba(34,211,238,.1); color:var(--accent); padding:2px 8px; border-radius:10px; }
.task-card { background:var(--card2); border:1px solid var(--border); border-radius:10px; padding:14px; margin-bottom:10px; transition:all .2s; }
.task-card:hover { border-color:var(--accent); }
.task-card-title { font-size:14px; font-weight:500; margin-bottom:6px; }
.task-card-meta { font-size:11px; color:var(--muted); display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.task-card-actions { display:flex; gap:6px; margin-top:10px; }
.task-card-actions form { display:inline; }
.task-card-actions button { font-size:11px; padding:4px 10px; border-radius:6px; border:1px solid var(--border); background:transparent; color:var(--text); cursor:pointer; transition:all .2s; }
.task-card-actions button:hover { border-color:var(--accent); color:var(--accent); }
.task-card-actions .del-btn:hover { border-color:var(--danger); color:var(--danger); }
.priority-dot { width:8px; height:8px; border-radius:50%; display:inline-block; }
.priority-high { background:#f87171; }
.priority-medium { background:#fbbf24; }
.priority-low { background:#34d399; }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:200; align-items:center; justify-content:center; }
.modal-overlay.active { display:flex; }
.modal { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:28px; width:100%; max-width:480px; }
.modal h3 { font-family:'Syne',sans-serif; font-size:18px; font-weight:700; margin-bottom:20px; }
.form-group { margin-bottom:16px; }
@media(max-width:900px) { .kanban { grid-template-columns:1fr; } }
</style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">✅ Tasks & Kanban</div>
            <div class="page-sub">Organize your assignments, projects, and study tasks</div>
        </div>
        <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('active')">+ New Task</button>
    </div>

    <div class="kanban">
        <div class="kanban-col">
            <div class="kanban-header">📋 To Do <span class="kanban-count"><?= count($todo) ?></span></div>
            <?php foreach ($todo as $t): ?>
            <div class="task-card">
                <div class="task-card-title"><?= htmlspecialchars($t['title']) ?></div>
                <div class="task-card-meta">
                    <span class="priority-dot priority-<?= $t['priority'] ?>"></span> <?= ucfirst($t['priority']) ?>
                    <?php if ($t['subject_name']): ?>
                        <span style="color:<?= htmlspecialchars($t['color'] ?? 'var(--accent)') ?>">● <?= htmlspecialchars($t['subject_name']) ?></span>
                    <?php endif; ?>
                    <?php if ($t['due_date']): ?>
                        <span>📅 <?= date('M j', strtotime($t['due_date'])) ?></span>
                    <?php endif; ?>
                </div>
                <div class="task-card-actions">
                    <form method="POST"><input type="hidden" name="action" value="status"><input type="hidden" name="task_id" value="<?= $t['id'] ?>"><input type="hidden" name="status" value="in_progress"><button type="submit">▶ Start</button></form>
                    <form method="POST"><input type="hidden" name="action" value="status"><input type="hidden" name="task_id" value="<?= $t['id'] ?>"><input type="hidden" name="status" value="done"><button type="submit">✓ Done</button></form>
                    <form method="POST" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="task_id" value="<?= $t['id'] ?>"><button type="submit" class="del-btn">🗑</button></form>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($todo)): ?><div style="color:var(--muted);font-size:13px;text-align:center;padding:20px 0;">No tasks here yet</div><?php endif; ?>
        </div>

        <div class="kanban-col">
            <div class="kanban-header">🔄 In Progress <span class="kanban-count"><?= count($inProgress) ?></span></div>
            <?php foreach ($inProgress as $t): ?>
            <div class="task-card">
                <div class="task-card-title"><?= htmlspecialchars($t['title']) ?></div>
                <div class="task-card-meta">
                    <span class="priority-dot priority-<?= $t['priority'] ?>"></span> <?= ucfirst($t['priority']) ?>
                    <?php if ($t['subject_name']): ?>
                        <span style="color:<?= htmlspecialchars($t['color'] ?? 'var(--accent)') ?>">● <?= htmlspecialchars($t['subject_name']) ?></span>
                    <?php endif; ?>
                    <?php if ($t['due_date']): ?>
                        <span>📅 <?= date('M j', strtotime($t['due_date'])) ?></span>
                    <?php endif; ?>
                </div>
                <div class="task-card-actions">
                    <form method="POST"><input type="hidden" name="action" value="status"><input type="hidden" name="task_id" value="<?= $t['id'] ?>"><input type="hidden" name="status" value="todo"><button type="submit">⏪ Back</button></form>
                    <form method="POST"><input type="hidden" name="action" value="status"><input type="hidden" name="task_id" value="<?= $t['id'] ?>"><input type="hidden" name="status" value="done"><button type="submit">✓ Done</button></form>
                    <form method="POST" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="task_id" value="<?= $t['id'] ?>"><button type="submit" class="del-btn">🗑</button></form>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($inProgress)): ?><div style="color:var(--muted);font-size:13px;text-align:center;padding:20px 0;">No tasks in progress</div><?php endif; ?>
        </div>

        <div class="kanban-col">
            <div class="kanban-header">✅ Done <span class="kanban-count"><?= count($done) ?></span></div>
            <?php foreach ($done as $t): ?>
            <div class="task-card" style="opacity:.7;">
                <div class="task-card-title" style="text-decoration:line-through;"><?= htmlspecialchars($t['title']) ?></div>
                <div class="task-card-meta">
                    <?php if ($t['subject_name']): ?>
                        <span style="color:<?= htmlspecialchars($t['color'] ?? 'var(--accent)') ?>">● <?= htmlspecialchars($t['subject_name']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="task-card-actions">
                    <form method="POST"><input type="hidden" name="action" value="status"><input type="hidden" name="task_id" value="<?= $t['id'] ?>"><input type="hidden" name="status" value="todo"><button type="submit">↩ Reopen</button></form>
                    <form method="POST" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="task_id" value="<?= $t['id'] ?>"><button type="submit" class="del-btn">🗑</button></form>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($done)): ?><div style="color:var(--muted);font-size:13px;text-align:center;padding:20px 0;">Complete tasks to see them here</div><?php endif; ?>
        </div>
    </div>
</main>

<!-- Add Task Modal -->
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <h3>➕ New Task</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Task Title *</label>
                <input type="text" name="title" required placeholder="e.g. Complete DSA assignment">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3" placeholder="Optional details..."></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label>Subject</label>
                    <select name="subject_id">
                        <option value="">— None —</option>
                        <?php foreach ($subjectList as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Priority</label>
                    <select name="priority">
                        <option value="low">🟢 Low</option>
                        <option value="medium" selected>🟡 Medium</option>
                        <option value="high">🔴 High</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Due Date</label>
                <input type="date" name="due_date">
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:10px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('addModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Task</button>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('addModal').addEventListener('click', function(e) { if (e.target === this) this.classList.remove('active'); });
<?php if (isset($_GET['new'])): ?>document.getElementById('addModal').classList.add('active');<?php endif; ?>
</script>
</body>
</html>
