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
$subjects = $db->prepare("SELECT id, name, code, year, semester FROM subjects WHERE user_id=? ORDER BY year ASC, semester ASC, name ASC");
$subjects->execute([$user['id']]);
$subjectList = $subjects->fetchAll();
$subjectMeta = [];
foreach ($subjectList as $subject) {
    $subjectMeta[] = [
        'id' => (int) $subject['id'],
        'name' => $subject['name'],
        'code' => $subject['code'] ?? '',
        'year' => isset($subject['year']) ? (int) $subject['year'] : 0,
        'semester' => isset($subject['semester']) ? (int) $subject['semester'] : 0,
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tasks & Kanban - EduSync MU</title>
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
.modal { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:28px; width:100%; max-width:520px; }
.modal h3 { font-family:'Syne',sans-serif; font-size:18px; font-weight:700; margin-bottom:20px; }
.form-group { margin-bottom:16px; }
.suggestion-row { display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; }
.suggestion-chip { border:1px solid var(--border); background:rgba(255,255,255,.03); color:var(--text); border-radius:999px; padding:6px 10px; font-size:11px; cursor:pointer; transition:all .2s; }
.suggestion-chip:hover { border-color:var(--accent); color:var(--accent); }
.suggestion-note { color:var(--muted); font-size:11px; margin-top:8px; }
@media(max-width:900px) { .kanban { grid-template-columns:1fr; } }
</style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">Tasks & Kanban</div>
            <div class="page-sub">Organize your assignments, projects, and study tasks</div>
        </div>
        <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('active')">+ New Task</button>
    </div>

    <div class="kanban">
        <div class="kanban-col">
            <div class="kanban-header">To Do <span class="kanban-count"><?= count($todo) ?></span></div>
            <?php foreach ($todo as $t): ?>
            <div class="task-card">
                <div class="task-card-title"><?= htmlspecialchars($t['title']) ?></div>
                <div class="task-card-meta">
                    <span class="priority-dot priority-<?= $t['priority'] ?>"></span> <?= ucfirst($t['priority']) ?>
                    <?php if ($t['subject_name']): ?>
                        <span style="color:<?= htmlspecialchars($t['color'] ?? 'var(--accent)') ?>">&#9679; <?= htmlspecialchars($t['subject_name']) ?></span>
                    <?php endif; ?>
                    <?php if ($t['due_date']): ?>
                        <span><?= date('M j', strtotime($t['due_date'])) ?></span>
                    <?php endif; ?>
                </div>
                <div class="task-card-actions">
                    <form method="POST"><input type="hidden" name="action" value="status"><input type="hidden" name="task_id" value="<?= $t['id'] ?>"><input type="hidden" name="status" value="in_progress"><button type="submit">Start</button></form>
                    <form method="POST"><input type="hidden" name="action" value="status"><input type="hidden" name="task_id" value="<?= $t['id'] ?>"><input type="hidden" name="status" value="done"><button type="submit">Done</button></form>
                    <form method="POST" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="task_id" value="<?= $t['id'] ?>"><button type="submit" class="del-btn">Delete</button></form>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($todo)): ?><div style="color:var(--muted);font-size:13px;text-align:center;padding:20px 0;">No tasks here yet</div><?php endif; ?>
        </div>

        <div class="kanban-col">
            <div class="kanban-header">In Progress <span class="kanban-count"><?= count($inProgress) ?></span></div>
            <?php foreach ($inProgress as $t): ?>
            <div class="task-card">
                <div class="task-card-title"><?= htmlspecialchars($t['title']) ?></div>
                <div class="task-card-meta">
                    <span class="priority-dot priority-<?= $t['priority'] ?>"></span> <?= ucfirst($t['priority']) ?>
                    <?php if ($t['subject_name']): ?>
                        <span style="color:<?= htmlspecialchars($t['color'] ?? 'var(--accent)') ?>">&#9679; <?= htmlspecialchars($t['subject_name']) ?></span>
                    <?php endif; ?>
                    <?php if ($t['due_date']): ?>
                        <span><?= date('M j', strtotime($t['due_date'])) ?></span>
                    <?php endif; ?>
                </div>
                <div class="task-card-actions">
                    <form method="POST"><input type="hidden" name="action" value="status"><input type="hidden" name="task_id" value="<?= $t['id'] ?>"><input type="hidden" name="status" value="todo"><button type="submit">Back</button></form>
                    <form method="POST"><input type="hidden" name="action" value="status"><input type="hidden" name="task_id" value="<?= $t['id'] ?>"><input type="hidden" name="status" value="done"><button type="submit">Done</button></form>
                    <form method="POST" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="task_id" value="<?= $t['id'] ?>"><button type="submit" class="del-btn">Delete</button></form>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($inProgress)): ?><div style="color:var(--muted);font-size:13px;text-align:center;padding:20px 0;">No tasks in progress</div><?php endif; ?>
        </div>

        <div class="kanban-col">
            <div class="kanban-header">Done <span class="kanban-count"><?= count($done) ?></span></div>
            <?php foreach ($done as $t): ?>
            <div class="task-card" style="opacity:.7;">
                <div class="task-card-title" style="text-decoration:line-through;"><?= htmlspecialchars($t['title']) ?></div>
                <div class="task-card-meta">
                    <?php if ($t['subject_name']): ?>
                        <span style="color:<?= htmlspecialchars($t['color'] ?? 'var(--accent)') ?>">&#9679; <?= htmlspecialchars($t['subject_name']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="task-card-actions">
                    <form method="POST"><input type="hidden" name="action" value="status"><input type="hidden" name="task_id" value="<?= $t['id'] ?>"><input type="hidden" name="status" value="todo"><button type="submit">Reopen</button></form>
                    <form method="POST" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="task_id" value="<?= $t['id'] ?>"><button type="submit" class="del-btn">Delete</button></form>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($done)): ?><div style="color:var(--muted);font-size:13px;text-align:center;padding:20px 0;">Complete tasks to see them here</div><?php endif; ?>
        </div>
    </div>
</main>

<div class="modal-overlay" id="addModal">
    <div class="modal">
        <h3>New Task</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Task Title *</label>
                <input type="text" name="title" list="taskTitleSuggestions" required placeholder="e.g. Complete DSA assignment">
                <datalist id="taskTitleSuggestions"></datalist>
                <div class="suggestion-note">Choose a subject to see different work ideas for this task.</div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3" placeholder="Optional details..."></textarea>
                <div class="suggestion-row" id="descriptionSuggestions"></div>
                <div class="suggestion-note">Type your own description or tap a random suggestion.</div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label>Year</label>
                    <select id="taskYear"></select>
                </div>
                <div class="form-group">
                    <label>Priority</label>
                    <select name="priority">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label>Semester</label>
                    <select id="taskSemester"></select>
                </div>
                <div class="form-group">
                    <label>Subject</label>
                    <select name="subject_id" id="taskSubject"></select>
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

const subjectMeta = <?= json_encode($subjectMeta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const yearSelect = document.getElementById('taskYear');
const semesterSelect = document.getElementById('taskSemester');
const subjectSelect = document.getElementById('taskSubject');
const titleSuggestions = document.getElementById('taskTitleSuggestions');
const descriptionSuggestions = document.getElementById('descriptionSuggestions');
const descriptionInput = document.querySelector('textarea[name="description"]');

const genericTaskTitles = [
    'Review lecture notes',
    'Prepare quiz questions',
    'Make a short revision sheet',
    'Practice previous questions',
    'Organize study materials'
];

const genericDescriptions = [
    'Break the work into small steps and finish the most important part first.',
    'Collect notes, key formulas, and examples before starting the task.',
    'Spend one focused study session on this and review the final result.',
    'Check class slides and complete the work with proper formatting.',
    'Finish a draft today and leave time for revision at the end.'
];

function formatYearLabel(year) {
    if (year === 1) return '1st Year';
    if (year === 2) return '2nd Year';
    if (year === 3) return '3rd Year';
    return `${year}th Year`;
}

function getSubjectLabel(subject) {
    return subject.code ? `${subject.code} ${subject.name}` : subject.name;
}

function buildTaskTitles(subject) {
    if (!subject) return genericTaskTitles;
    const shortLabel = subject.code || subject.name;
    return [
        `Complete ${shortLabel} assignment`,
        `Revise ${shortLabel} class notes`,
        `Solve ${shortLabel} practice problems`,
        `Prepare ${shortLabel} presentation`,
        `Finish ${shortLabel} lab work`
    ];
}

function buildDescriptions(subject) {
    if (!subject) return genericDescriptions;
    const label = getSubjectLabel(subject);
    return [
        `Read the latest ${label} lecture materials and list the key topics to finish today.`,
        `Complete the pending ${label} work and double-check everything before submission.`,
        `Practice ${label} questions in one focused session and note down weak areas.`,
        `Prepare a clean summary for ${label} with formulas, definitions, and examples.`,
        `Finish the current ${label} task, then review the result and fix any missing parts.`
    ];
}

function fillTitleSuggestions(subject) {
    titleSuggestions.innerHTML = '';
    buildTaskTitles(subject).forEach((title) => {
        const option = document.createElement('option');
        option.value = title;
        titleSuggestions.appendChild(option);
    });
}

function fillDescriptionSuggestions(subject) {
    descriptionSuggestions.innerHTML = '';
    buildDescriptions(subject).forEach((description) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'suggestion-chip';
        button.textContent = description;
        button.addEventListener('click', () => {
            descriptionInput.value = description;
            descriptionInput.focus();
        });
        descriptionSuggestions.appendChild(button);
    });
}

function getYears() {
    return [...new Set(subjectMeta.map((item) => item.year).filter(Boolean))];
}

function getSemesters(year) {
    return [...new Set(subjectMeta.filter((item) => item.year === year).map((item) => item.semester).filter(Boolean))];
}

function getSubjects(year, semester) {
    return subjectMeta.filter((item) => item.year === year && item.semester === semester);
}

function populateYearOptions() {
    yearSelect.innerHTML = '';
    const years = getYears();
    if (!years.length) {
        yearSelect.innerHTML = '<option value="">No years</option>';
        return;
    }
    years.forEach((year) => {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = formatYearLabel(year);
        yearSelect.appendChild(option);
    });
}

function populateSemesterOptions(year) {
    semesterSelect.innerHTML = '';
    const semesters = getSemesters(year);
    if (!semesters.length) {
        semesterSelect.innerHTML = '<option value="">No semester</option>';
        return;
    }
    semesters.forEach((semester) => {
        const option = document.createElement('option');
        option.value = semester;
        option.textContent = `Semester ${semester}`;
        semesterSelect.appendChild(option);
    });
}

function populateSubjectOptions(year, semester) {
    subjectSelect.innerHTML = '<option value="">-- None --</option>';
    getSubjects(year, semester).forEach((subject) => {
        const option = document.createElement('option');
        option.value = subject.id;
        option.textContent = subject.code ? `${subject.code}: ${subject.name}` : subject.name;
        subjectSelect.appendChild(option);
    });
}

function syncTaskSuggestions() {
    const selectedId = Number(subjectSelect.value || 0);
    const subject = subjectMeta.find((item) => item.id === selectedId) || null;
    fillTitleSuggestions(subject);
    fillDescriptionSuggestions(subject);
}

function syncTaskSelectors() {
    const year = Number(yearSelect.value || 0);
    populateSemesterOptions(year);
    const semesters = getSemesters(year);
    if (semesters.length) {
        semesterSelect.value = String(semesters[0]);
    }
    populateSubjectOptions(year, Number(semesterSelect.value || 0));
    syncTaskSuggestions();
}

function initializeTaskSelectors() {
    populateYearOptions();
    const firstSubject = subjectMeta[0] || null;
    if (!firstSubject) {
        semesterSelect.innerHTML = '<option value="">No semester</option>';
        subjectSelect.innerHTML = '<option value="">-- None --</option>';
        syncTaskSuggestions();
        return;
    }
    yearSelect.value = String(firstSubject.year);
    populateSemesterOptions(firstSubject.year);
    semesterSelect.value = String(firstSubject.semester);
    populateSubjectOptions(firstSubject.year, firstSubject.semester);
    syncTaskSuggestions();
}

yearSelect.addEventListener('change', syncTaskSelectors);
semesterSelect.addEventListener('change', () => {
    populateSubjectOptions(Number(yearSelect.value || 0), Number(semesterSelect.value || 0));
    syncTaskSuggestions();
});
subjectSelect.addEventListener('change', syncTaskSuggestions);
initializeTaskSelectors();
</script>
</body>
</html>
