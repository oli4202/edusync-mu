<?php
// admin/manage-attendance.php — Admin-only bulk attendance management
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
$user = currentUser();
$db   = getDB();

// Ensure attendance table exists
$db->exec("CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    class_date DATE NOT NULL,
    status ENUM('present','absent','late','excused') DEFAULT 'present',
    notes VARCHAR(255),
    marked_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_att (user_id, course_id, class_date)
)");

// Try adding marked_by column if not exists
try { $db->exec("ALTER TABLE attendance ADD COLUMN marked_by INT NULL"); } catch(Exception $e) {}

$msg = $err = '';

// Handle bulk attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'bulk_mark') {
        $courseId  = (int)$_POST['course_id'];
        $classDate = clean($_POST['class_date']);
        $statuses  = $_POST['status'] ?? [];
        $notes     = $_POST['note'] ?? [];

        if (!$courseId || !$classDate) {
            $err = 'Select a course and date.';
        } else {
            $count = 0;
            $stmt = $db->prepare("INSERT INTO attendance (user_id, course_id, class_date, status, notes, marked_by) 
                VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status), notes=VALUES(notes), marked_by=VALUES(marked_by)");
            foreach ($statuses as $userId => $status) {
                $status = clean($status);
                $note   = clean($notes[$userId] ?? '');
                if (in_array($status, ['present','absent','late','excused'])) {
                    $stmt->execute([(int)$userId, $courseId, $classDate, $status, $note, $user['id']]);
                    $count++;
                }
            }
            $msg = "Attendance marked for $count students!";
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['att_id'];
        $db->prepare("DELETE FROM attendance WHERE id=?")->execute([$id]);
        $msg = 'Record deleted.';
    }
}

// Get all courses
$courses = $db->query("SELECT * FROM courses ORDER BY year, semester, name")->fetchAll();

// Get all students
$students = $db->query("SELECT id, name, email, student_id, batch, semester FROM users WHERE role='student' ORDER BY name")->fetchAll();

// Selected filters
$selCourse = (int)($_GET['course_id'] ?? ($_POST['course_id'] ?? 0));
$selDate   = clean($_GET['class_date'] ?? ($_POST['class_date'] ?? date('Y-m-d')));

// If course selected, get existing attendance for that date
$existingAtt = [];
if ($selCourse && $selDate) {
    $stmt = $db->prepare("SELECT user_id, status, notes FROM attendance WHERE course_id=? AND class_date=?");
    $stmt->execute([$selCourse, $selDate]);
    foreach ($stmt->fetchAll() as $row) {
        $existingAtt[$row['user_id']] = $row;
    }
}

// Recent attendance history
$recentHistory = [];
if ($selCourse) {
    $stmt = $db->prepare("SELECT a.*, u.name AS student_name, u.student_id AS sid 
        FROM attendance a JOIN users u ON a.user_id=u.id 
        WHERE a.course_id=? ORDER BY a.class_date DESC, u.name ASC LIMIT 100");
    $stmt->execute([$selCourse]);
    $recentHistory = $stmt->fetchAll();
}

// Stats
$totalRecords  = $db->query("SELECT COUNT(*) FROM attendance")->fetchColumn();
$todayRecords  = $db->prepare("SELECT COUNT(*) FROM attendance WHERE class_date=?");
$todayRecords->execute([date('Y-m-d')]);
$todayCount    = $todayRecords->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Attendance — EduSync Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root{--bg:#0a0e1a;--card:#111827;--card2:#0f172a;--border:#1e2d45;--accent:#22d3ee;--accent2:#818cf8;--accent3:#34d399;--warn:#fbbf24;--danger:#f87171;--text:#e2e8f0;--muted:#64748b;}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;padding:32px;}

.header{display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px;}
.logo{font-family:'Syne',sans-serif;font-size:22px;font-weight:800;background:linear-gradient(135deg,var(--accent),var(--accent2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.badge{font-size:12px;padding:4px 12px;border-radius:20px;}
.badge-admin{background:rgba(248,113,113,.15);border:1px solid rgba(248,113,113,.3);color:var(--danger);}
.nav-link{color:var(--accent);text-decoration:none;font-size:14px;}

.stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px;}
.stat{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:18px;}
.stat-val{font-family:'Syne',sans-serif;font-size:28px;font-weight:800;color:var(--accent);}
.stat-lbl{font-size:12px;color:var(--muted);margin-top:4px;}

.card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:24px;margin-bottom:20px;}
.card-title{font-family:'Syne',sans-serif;font-size:16px;font-weight:700;margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid var(--border);}

.filter-bar{display:flex;gap:14px;flex-wrap:wrap;align-items:flex-end;margin-bottom:20px;}
.filter-bar label{font-size:12px;color:var(--muted);display:block;margin-bottom:4px;text-transform:uppercase;letter-spacing:.4px;}
.filter-bar select, .filter-bar input{background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:8px;padding:10px 14px;color:var(--text);font-size:14px;font-family:inherit;outline:none;min-width:200px;}
.filter-bar select:focus, .filter-bar input:focus{border-color:var(--accent);}
.filter-bar select option{background:var(--card);color:var(--text);}

.btn{display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:700;font-family:'Syne',sans-serif;cursor:pointer;border:none;transition:all .2s;text-decoration:none;}
.btn-primary{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#0a0e1a;}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 4px 20px rgba(34,211,238,.3);}
.btn-sm{padding:6px 14px;font-size:12px;}
.btn-outline{background:transparent;border:1px solid var(--border);color:var(--text);}
.btn-danger{background:rgba(248,113,113,.15);border:1px solid rgba(248,113,113,.3);color:var(--danger);}

.alert-success{background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.2);color:var(--accent3);border-radius:10px;padding:14px;font-size:14px;margin-bottom:16px;}
.alert-error{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:var(--danger);border-radius:10px;padding:14px;font-size:14px;margin-bottom:16px;}

.student-table{width:100%;border-collapse:collapse;}
.student-table th{text-align:left;font-size:12px;color:var(--muted);padding:10px 12px;border-bottom:1px solid var(--border);text-transform:uppercase;letter-spacing:.5px;}
.student-table td{padding:12px;border-bottom:1px solid rgba(30,45,69,.5);font-size:14px;}
.student-table tr:hover{background:rgba(34,211,238,.03);}
.student-table select{background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:6px;padding:6px 10px;color:var(--text);font-size:13px;outline:none;}
.student-table select option{background:var(--card);}
.student-table input[type="text"]{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:6px;padding:6px 10px;color:var(--text);font-size:13px;width:100%;outline:none;}

.empty{color:var(--muted);text-align:center;padding:32px;font-size:14px;}

.bulk-actions{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;}
.bulk-btn{padding:6px 14px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;border:1px solid var(--border);background:rgba(255,255,255,.03);color:var(--text);transition:all .2s;}
.bulk-btn:hover{border-color:var(--accent);color:var(--accent);}
.bulk-btn.active{border-color:var(--accent);background:rgba(34,211,238,.1);color:var(--accent);}

.history-table{width:100%;border-collapse:collapse;font-size:13px;}
.history-table th{text-align:left;font-size:11px;color:var(--muted);padding:8px 10px;border-bottom:1px solid var(--border);text-transform:uppercase;}
.history-table td{padding:8px 10px;border-bottom:1px solid rgba(30,45,69,.4);}

.status-present{color:var(--accent3);} .status-absent{color:var(--danger);}
.status-late{color:var(--warn);} .status-excused{color:var(--accent2);}

@media(max-width:700px){.stats-grid{grid-template-columns:1fr;}.filter-bar{flex-direction:column;}}
</style>
</head>
<body>

<div class="header">
    <div>
        <div class="logo">📋 Manage Attendance</div>
        <div style="font-size:13px;color:var(--muted);margin-top:2px;">Mark attendance for students · MU Sylhet SE Dept</div>
    </div>
    <div style="display:flex;align-items:center;gap:14px;">
        <span class="badge badge-admin">🛡️ Admin</span>
        <a href="index.php" class="nav-link">Admin Panel</a>
        <a href="../pages/attendance.php" class="nav-link">← View Attendance</a>
    </div>
</div>

<?php if ($msg): ?><div class="alert-success">✅ <?= $msg ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert-error">⚠ <?= $err ?></div><?php endif; ?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat"><div class="stat-val"><?= $totalRecords ?></div><div class="stat-lbl">Total Records</div></div>
    <div class="stat"><div class="stat-val"><?= $todayCount ?></div><div class="stat-lbl">Marked Today</div></div>
    <div class="stat"><div class="stat-val"><?= count($students) ?></div><div class="stat-lbl">Total Students</div></div>
</div>

<!-- Course & Date Selector -->
<div class="card">
    <div class="card-title">📅 Select Course & Date</div>
    <form method="GET" class="filter-bar">
        <div>
            <label>Course</label>
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
                <option value="<?= $c['id'] ?>" <?= $selCourse == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['code'].' — '.$c['name']) ?></option>
                <?php endforeach; ?>
                <?php if (!empty($courses)) echo '</optgroup>'; ?>
            </select>
        </div>
        <div>
            <label>Class Date</label>
            <input type="date" name="class_date" value="<?= $selDate ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Load Students</button>
    </form>
</div>

<!-- Bulk Attendance Form -->
<?php if ($selCourse && !empty($students)): ?>
<div class="card">
    <div class="card-title">
        👥 Mark Attendance
        <span style="font-size:12px;color:var(--muted);font-weight:400;margin-left:10px;">
            <?php
            $selCourseName = '';
            foreach ($courses as $c) { if ($c['id'] == $selCourse) { $selCourseName = $c['code'].' — '.$c['name']; break; } }
            echo htmlspecialchars($selCourseName) . ' · ' . date('D, M j Y', strtotime($selDate));
            ?>
        </span>
    </div>

    <!-- Bulk action buttons -->
    <div class="bulk-actions">
        <button type="button" class="bulk-btn" onclick="setAll('present')">✅ All Present</button>
        <button type="button" class="bulk-btn" onclick="setAll('absent')">❌ All Absent</button>
        <button type="button" class="bulk-btn" onclick="setAll('late')">⏰ All Late</button>
    </div>

    <form method="POST">
        <input type="hidden" name="action" value="bulk_mark">
        <input type="hidden" name="course_id" value="<?= $selCourse ?>">
        <input type="hidden" name="class_date" value="<?= htmlspecialchars($selDate) ?>">
        
        <table class="student-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>ID</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($students as $s):
                    $existing = $existingAtt[$s['id']] ?? null;
                    $curStatus = $existing['status'] ?? 'present';
                    $curNote   = $existing['notes'] ?? '';
                ?>
                <tr>
                    <td style="color:var(--muted);font-size:12px;"><?= $i++ ?></td>
                    <td>
                        <div style="font-weight:500;"><?= htmlspecialchars($s['name']) ?></div>
                        <div style="font-size:11px;color:var(--muted);"><?= htmlspecialchars($s['email']) ?></div>
                    </td>
                    <td style="font-size:13px;color:var(--muted);"><?= htmlspecialchars($s['student_id'] ?: '—') ?></td>
                    <td>
                        <select name="status[<?= $s['id'] ?>]" class="att-select">
                            <option value="present" <?= $curStatus === 'present' ? 'selected' : '' ?>>✅ Present</option>
                            <option value="absent"  <?= $curStatus === 'absent'  ? 'selected' : '' ?>>❌ Absent</option>
                            <option value="late"    <?= $curStatus === 'late'    ? 'selected' : '' ?>>⏰ Late</option>
                            <option value="excused" <?= $curStatus === 'excused' ? 'selected' : '' ?>>📋 Excused</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" name="note[<?= $s['id'] ?>]" value="<?= htmlspecialchars($curNote) ?>" placeholder="Optional note...">
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top:18px;display:flex;gap:10px;">
            <button type="submit" class="btn btn-primary">💾 Save Attendance</button>
            <span style="font-size:13px;color:var(--muted);padding:10px;"><?= count($students) ?> students</span>
        </div>
    </form>
</div>
<?php elseif ($selCourse && empty($students)): ?>
<div class="card"><div class="empty">No students registered yet.</div></div>
<?php endif; ?>

<!-- Recent History -->
<?php if ($selCourse && !empty($recentHistory)): ?>
<div class="card">
    <div class="card-title">📊 Recent Attendance History</div>
    <table class="history-table">
        <thead>
            <tr><th>Date</th><th>Student</th><th>ID</th><th>Status</th><th>Notes</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php
            $lastDate = '';
            foreach ($recentHistory as $r):
                $dateStr = date('D, M j', strtotime($r['class_date']));
                $showDate = ($dateStr !== $lastDate);
                $lastDate = $dateStr;
            ?>
            <tr>
                <td><?= $showDate ? '<strong>'.$dateStr.'</strong>' : '' ?></td>
                <td><?= htmlspecialchars($r['student_name']) ?></td>
                <td style="color:var(--muted);"><?= htmlspecialchars($r['sid'] ?: '—') ?></td>
                <td class="status-<?= $r['status'] ?>" style="font-weight:600;"><?= ucfirst($r['status']) ?></td>
                <td style="color:var(--muted);"><?= htmlspecialchars($r['notes'] ?: '—') ?></td>
                <td>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Delete?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="att_id" value="<?= $r['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">✕</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<script>
function setAll(status) {
    document.querySelectorAll('.att-select').forEach(sel => sel.value = status);
    // Highlight active bulk btn
    document.querySelectorAll('.bulk-btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
}
</script>
</body>
</html>
