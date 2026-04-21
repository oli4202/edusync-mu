<?php
// pages/attendance.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$user = currentUser();
$db   = getDB();
$isAdmin = ($user['role'] ?? '') === 'admin';
$currentPage = 'attendance';

// Ensure attendance tables exist
$db->exec("CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    class_date DATE NOT NULL,
    status ENUM('present','absent','late','excused') DEFAULT 'present',
    notes VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_att (user_id, course_id, class_date)
)");

// Handle form actions (admin only)
$msg = $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $courseId  = (int)$_POST['course_id'];
        $classDate = clean($_POST['class_date']);
        $status    = clean($_POST['status']);
        $notes     = clean($_POST['notes'] ?? '');
        if (!$courseId || !$classDate || !$status) { $err = 'Fill in all required fields.'; }
        else {
            try {
                $db->prepare("INSERT INTO attendance (user_id,course_id,class_date,status,notes) VALUES (?,?,?,?,?)
                    ON DUPLICATE KEY UPDATE status=VALUES(status), notes=VALUES(notes)")
                   ->execute([$user['id'], $courseId, $classDate, $status, $notes]);
                $msg = 'Attendance recorded!';
            } catch(Exception $e) { $err = 'Could not save. '.$e->getMessage(); }
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['att_id'];
        $db->prepare("DELETE FROM attendance WHERE id=?")->execute([$id]);
        $msg = 'Record deleted.';
    }
}

// Get user's enrolled courses (from subjects or all courses)
$myCourses = $db->prepare("SELECT DISTINCT c.* FROM courses c
    LEFT JOIN subjects s ON s.user_id=? AND s.code=c.code
    ORDER BY c.year, c.semester, c.name");
$myCourses->execute([$user['id']]);
$courses = $myCourses->fetchAll();
if (empty($courses)) {
    // fallback: show all courses
    $courses = $db->query("SELECT * FROM courses ORDER BY year, semester, name")->fetchAll();
}

// Filter
$filterCourse = (int)($_GET['course'] ?? 0);
$filterMonth  = clean($_GET['month'] ?? date('Y-m'));

// Attendance records
$where = ["a.user_id = ?"];
$params = [$user['id']];
if ($filterCourse) { $where[] = "a.course_id = ?"; $params[] = $filterCourse; }
if ($filterMonth)  { $where[] = "DATE_FORMAT(a.class_date,'%Y-%m') = ?"; $params[] = $filterMonth; }

$records = $db->prepare("SELECT a.*, c.name AS course_name, c.code AS course_code
    FROM attendance a JOIN courses c ON a.course_id=c.id
    WHERE ".implode(' AND ',$where)." ORDER BY a.class_date DESC");
$records->execute($params);
$attendance = $records->fetchAll();

// Per-course stats
$stats = $db->prepare("SELECT c.name, c.code, c.id AS course_id,
    COUNT(*) AS total,
    SUM(a.status='present') AS present,
    SUM(a.status='absent') AS absent,
    SUM(a.status='late') AS late,
    ROUND(SUM(a.status IN ('present','late'))/COUNT(*)*100,1) AS pct
    FROM attendance a JOIN courses c ON a.course_id=c.id
    WHERE a.user_id=? GROUP BY a.course_id ORDER BY pct ASC");
$stats->execute([$user['id']]);
$courseStats = $stats->fetchAll();

// Overall
$totalClasses = array_sum(array_column($courseStats, 'total'));
$totalPresent = array_sum(array_column($courseStats, 'present'));
$overallPct   = $totalClasses > 0 ? round($totalPresent/$totalClasses*100,1) : 0;
$lowAttCourses = array_filter($courseStats, fn($s) => $s['pct'] < 75);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Attendance — EduSync MU</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.att-bar { height: 8px; background: var(--border); border-radius: 4px; margin-top: 8px; overflow: hidden; }
.att-fill { height: 100%; border-radius: 4px; transition: width .6s ease; }
.status-present { color: var(--accent3); } .status-absent { color: var(--danger); }
.status-late    { color: var(--warn); }    .status-excused { color: var(--accent2); }
.dot-present { background: var(--accent3); } .dot-absent { background: var(--danger); }
.dot-late    { background: var(--warn); }   .dot-excused { background: var(--accent2); }
.status-dot { width:8px; height:8px; border-radius:50%; display:inline-block; margin-right:6px; }
.warn-box { background:rgba(248,113,113,.08); border:1px solid rgba(248,113,113,.2); border-radius:12px; padding:14px 18px; margin-bottom:20px; }
.warn-box-title { color:var(--danger); font-weight:600; font-size:13px; margin-bottom:8px; }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(156, 60, 60, 0.7); z-index:999; align-items:center; justify-content:center; }
.modal-overlay.open { display:flex; }
.modal { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:28px; width:100%; max-width:460px; animation:fadeUp .3s ease; }
@keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
.modal-title { font-family:'Syne',sans-serif; font-size:16px; font-weight:700; margin-bottom:20px; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.modal select {
    background: rgba(0, 0, 0, 0.1);
    border: 1px solid var(--border);
    color: var(--text);
    padding: 8px 12px;
    border-radius: 8px;
    width: 100%;
}
.modal select option {
    background: var(--card);
    color: var(--text);
}
optgroup {
    background: rgba(34, 211, 238, 0.1) !important;
    color:#102c40 !important;
}

</style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">🗓 Attendance Tracker</div>
            <div class="page-sub"><?= $isAdmin ? 'Manage attendance records' : 'View your class attendance across all subjects' ?></div>
        </div>
        <div style="display:flex;gap:10px;align-items:center;">
            <?php if ($isAdmin): ?>
                <a href="../admin/manage-attendance.php" class="btn btn-primary">📋 Manage Attendance</a>
            <?php else: ?>
                <span style="font-size:13px;color:var(--muted);background:rgba(34,211,238,.08);border:1px solid rgba(34,211,238,.15);border-radius:10px;padding:8px 14px;">📌 Attendance is managed by your teacher</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($msg): ?><div class="alert-success">✅ <?= $msg ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert-error">⚠ <?= $err ?></div><?php endif; ?>

    <!-- Warning for low attendance -->
    <?php if (!empty($lowAttCourses)): ?>
    <div class="warn-box">
        <div class="warn-box-title">⚠️ Low Attendance Warning</div>
        <?php foreach ($lowAttCourses as $lc): ?>
        <div style="font-size:13px;color:var(--text);margin-bottom:4px;">
            <strong><?= htmlspecialchars($lc['code']) ?></strong> — <?= htmlspecialchars($lc['name']) ?>:
            <span style="color:var(--danger);font-weight:600;"><?= $lc['pct'] ?>% attendance</span>
            (minimum 75% required)
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Overall Stats -->
    <div class="grid-4" style="margin-bottom:24px;">
        <div class="stat-card <?= $overallPct >= 75 ? 'green' : 'red' ?>">
            <div class="stat-icon">📊</div>
            <div class="stat-value"><?= $overallPct ?>%</div>
            <div class="stat-label">Overall Attendance</div>
        </div>
        <div class="stat-card cyan">
            <div class="stat-icon">✅</div>
            <div class="stat-value"><?= $totalPresent ?></div>
            <div class="stat-label">Classes Attended</div>
        </div>
        <div class="stat-card red">
            <div class="stat-icon">❌</div>
            <div class="stat-value"><?= array_sum(array_column($courseStats,'absent')) ?></div>
            <div class="stat-label">Classes Missed</div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-icon">⏰</div>
            <div class="stat-value"><?= array_sum(array_column($courseStats,'late')) ?></div>
            <div class="stat-label">Late Arrivals</div>
        </div>
    </div>

    <!-- Per-course breakdown -->
    <?php if (!empty($courseStats)): ?>
    <div class="card" style="margin-bottom:24px;">
        <div class="card-title">📚 Per-Course Breakdown</div>
        <?php foreach ($courseStats as $cs): ?>
        <div style="margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                <div>
                    <strong style="font-size:14px;"><?= htmlspecialchars($cs['code']) ?></strong>
                    <span style="font-size:13px;color:var(--muted);margin-left:8px;"><?= htmlspecialchars($cs['name']) ?></span>
                </div>
                <div style="display:flex;gap:14px;font-size:12px;">
                    <span class="status-present">✅ <?= $cs['present'] ?></span>
                    <span class="status-absent">❌ <?= $cs['absent'] ?></span>
                    <span class="status-late">⏰ <?= $cs['late'] ?></span>
                    <strong style="color:<?= $cs['pct']>=75?'var(--accent3)':'var(--danger)' ?>"><?= $cs['pct'] ?>%</strong>
                </div>
            </div>
            <div class="att-bar">
                <div class="att-fill" style="width:<?= $cs['pct'] ?>%;background:<?= $cs['pct']>=75?'var(--accent3)':($cs['pct']>=60?'var(--warn)':'var(--danger)') ?>"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Filter + Records -->
    <div class="card">
        <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;align-items:flex-end;">
            <div>
                <label>Course</label>
                <select name="course" style="min-width:220px;">
                    <option value="">All Courses</option>
                    <?php
                    $currentYear = null;
                    $currentSemester = null;
                    foreach ($courses as $c):
                        if ($c['year'] != $currentYear || $c['semester'] != $currentSemester):
                            if ($currentYear !== null) echo '</optgroup>';
                            $yearLabel = $c['year'] . 'st Year';
                            if ($c['year'] == 2) $yearLabel = '2nd Year';
                            if ($c['year'] == 3) $yearLabel = '3rd Year';
                            if ($c['year'] >= 4) $yearLabel = $c['year'] . 'th Year';
                            echo '<optgroup label="' . $yearLabel . ' - Semester ' . $c['semester'] . '">';
                            $currentYear = $c['year'];
                            $currentSemester = $c['semester'];
                        endif;
                    ?>
                    <option value="<?= $c['id'] ?>" <?= $filterCourse==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['code'].' — '.$c['name']) ?></option>
                    <?php endforeach; ?>
                    <?php if (!empty($courses)) echo '</optgroup>'; ?>
                </select>
            </div>
            <div>
                <label>Month</label>
                <input type="month" name="month" value="<?= $filterMonth ?>" style="width:160px;">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <a href="attendance.php" class="btn btn-outline btn-sm">Clear</a>
        </form>

        <?php if (empty($attendance)): ?>
        <div style="text-align:center;padding:32px;color:var(--muted);">
            <div style="font-size:36px;margin-bottom:10px;">📋</div>
            No records found. <button class="btn btn-primary btn-sm" onclick="document.getElementById('addModal').classList.add('open')">+ Log First Attendance</button>
        </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr><th>Date</th><th>Course</th><th>Status</th><th>Notes</th><?php if ($isAdmin): ?><th>Action</th><?php endif; ?></tr>
            </thead>
            <tbody>
                <?php foreach ($attendance as $a): ?>
                <tr>
                    <td><?= date('D, M j Y', strtotime($a['class_date'])) ?></td>
                    <td><strong><?= htmlspecialchars($a['course_code']) ?></strong> <span style="color:var(--muted);font-size:12px;"><?= htmlspecialchars($a['course_name']) ?></span></td>
                    <td>
                        <span class="status-dot dot-<?= $a['status'] ?>"></span>
                        <span class="status-<?= $a['status'] ?>" style="font-weight:600;font-size:13px;"><?= ucfirst($a['status']) ?></span>
                    </td>
                    <td style="color:var(--muted);font-size:13px;"><?= htmlspecialchars($a['notes'] ?: '—') ?></td>
                    <?php if ($isAdmin): ?>
                    <td>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this record?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="att_id" value="<?= $a['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</main>

<?php if ($isAdmin): ?>
<!-- Add Attendance Modal (Admin Only) -->
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-title">🗓 Log Attendance</div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="field">
                <label>Course *</label>
                <select name="course_id" required>
                    <option value="">Select course...</option>
                    <?php
                    $currentYear = null;
                    $currentSemester = null;
                    foreach ($courses as $c):
                        if ($c['year'] != $currentYear || $c['semester'] != $currentSemester):
                            if ($currentYear !== null) echo '</optgroup>';
                            $yearLabel = $c['year'] . 'st Year';
                            if ($c['year'] == 2) $yearLabel = '2nd Year';
                            if ($c['year'] == 3) $yearLabel = '3rd Year';
                            if ($c['year'] >= 4) $yearLabel = $c['year'] . 'th Year';
                            echo '<optgroup label="' . $yearLabel . ' - Semester ' . $c['semester'] . '">';
                            $currentYear = $c['year'];
                            $currentSemester = $c['semester'];
                        endif;
                    ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['code'].' — '.$c['name']) ?></option>
                    <?php endforeach; ?>
                    <?php if (!empty($courses)) echo '</optgroup>'; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>Date *</label>
                    <input type="date" name="class_date" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="field">
                    <label>Status *</label>
                    <select name="status" required>
                        <option value="present">✅ Present</option>
                        <option value="absent">❌ Absent</option>
                        <option value="late">⏰ Late</option>
                        <option value="excused">📋 Excused</option>
                    </select>
                </div>
            </div>
            <div class="field">
                <label>Notes (optional)</label>
                <input type="text" name="notes" list="attendanceNoteList" placeholder="e.g. Sick, transport issue...">
                <datalist id="attendanceNoteList">
                    <option value="Present for full class">
                    <option value="Late due to traffic">
                    <option value="Lab class attended">
                    <option value="Missed class due to illness">
                    <option value="Excused for university work">
                </datalist>
            </div>
            <div style="display:flex;gap:10px;margin-top:8px;">
                <button type="submit" class="btn btn-primary">Save Record</button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('addModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});
</script>
<?php endif; ?>
</body>
</html>
