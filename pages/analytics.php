<?php
// pages/analytics.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$user = currentUser();
$db   = getDB();
$currentPage = 'analytics';

// Study hours per day (last 30 days)
$daily = $db->prepare("SELECT DATE(logged_date) as d, SUM(hours) as total FROM study_logs WHERE user_id=? AND logged_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(logged_date) ORDER BY d");
$daily->execute([$user['id']]); $dailyData = $daily->fetchAll();

// Hours per subject (all time)
$perSubject = $db->prepare("SELECT s.name, s.color, COALESCE(SUM(sl.hours),0) as total FROM subjects s LEFT JOIN study_logs sl ON sl.subject_id=s.id WHERE s.user_id=? GROUP BY s.id ORDER BY total DESC");
$perSubject->execute([$user['id']]); $subjectData = $perSubject->fetchAll();

// Task stats
$taskStats = $db->prepare("SELECT status, COUNT(*) as cnt FROM tasks WHERE user_id=? GROUP BY status");
$taskStats->execute([$user['id']]); $taskRows = $taskStats->fetchAll();
$taskMap = ['todo'=>0,'in_progress'=>0,'done'=>0];
foreach ($taskRows as $r) $taskMap[$r['status']] = $r['cnt'];

// Grade average
$gradeAvg = $db->prepare("SELECT COALESCE(AVG(score/max_score*100),0) as avg_pct FROM grades WHERE user_id=?");
$gradeAvg->execute([$user['id']]); $avgGrade = round($gradeAvg->fetchColumn(), 1);

// Weekly total
$weekTotal = $db->prepare("SELECT COALESCE(SUM(hours),0) FROM study_logs WHERE user_id=? AND WEEK(logged_date)=WEEK(NOW())");
$weekTotal->execute([$user['id']]); $thisWeek = $weekTotal->fetchColumn();

// Monthly total
$monthTotal = $db->prepare("SELECT COALESCE(SUM(hours),0) FROM study_logs WHERE user_id=? AND MONTH(logged_date)=MONTH(NOW()) AND YEAR(logged_date)=YEAR(NOW())");
$monthTotal->execute([$user['id']]); $thisMonth = $monthTotal->fetchColumn();

// Weekly chart data
$weekChart = $db->prepare("SELECT DAYNAME(logged_date) as day, SUM(hours) as total FROM study_logs WHERE user_id=? AND logged_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(logged_date), DAYNAME(logged_date)");
$weekChart->execute([$user['id']]); $weekRows = $weekChart->fetchAll();
$days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
$weekMap = [];
foreach ($weekRows as $r) $weekMap[substr($r['day'],0,3)] = (float)$r['total'];
$weekValues = array_map(fn($d) => $weekMap[$d] ?? 0, $days);

// Log study hours
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'log') {
    $stmt = $db->prepare("INSERT INTO study_logs (user_id, subject_id, hours, notes, logged_date) VALUES (?,?,?,?,?)");
    $stmt->execute([$user['id'], $_POST['subject_id'] ?: null, floatval($_POST['hours']), clean($_POST['notes'] ?? ''), $_POST['logged_date'] ?: date('Y-m-d')]);
    header('Location: analytics.php'); exit;
}

$subjectList = $db->prepare("SELECT id, name, code, year, semester FROM subjects WHERE user_id=? ORDER BY year ASC, semester ASC, name ASC");
$subjectList->execute([$user['id']]); $subs = $subjectList->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Analytics — EduSync MU</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.chart-card { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:22px; }
.chart-wrap { height:220px; position:relative; }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:200; align-items:center; justify-content:center; }
.modal-overlay.active { display:flex; }
.modal { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:28px; width:100%; max-width:440px; }
.modal h3 { font-family:'Syne',sans-serif; font-size:18px; font-weight:700; margin-bottom:20px; }
.form-group { margin-bottom:16px; }
</style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">📊 Analytics</div>
            <div class="page-sub">Track your study hours, task progress, and performance</div>
        </div>
        <button class="btn btn-primary" onclick="document.getElementById('logModal').classList.add('active')">+ Log Study Hours</button>
    </div>

    <!-- Stats -->
    <div class="grid-4" style="margin-bottom:24px;">
        <div class="stat-card cyan">
            <div class="stat-icon">⏱</div>
            <div class="stat-value"><?= number_format($thisWeek,1) ?>h</div>
            <div class="stat-label">This Week</div>
        </div>
        <div class="stat-card purple">
            <div class="stat-icon">📅</div>
            <div class="stat-value"><?= number_format($thisMonth,1) ?>h</div>
            <div class="stat-label">This Month</div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon">✅</div>
            <div class="stat-value"><?= $taskMap['done'] ?>/<?= array_sum($taskMap) ?></div>
            <div class="stat-label">Tasks Completed</div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-icon">🎓</div>
            <div class="stat-value"><?= $avgGrade ?>%</div>
            <div class="stat-label">Avg Grade</div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid-2" style="margin-bottom:24px;">
        <div class="chart-card">
            <div class="card-title">📈 Weekly Study Hours</div>
            <div class="chart-wrap"><canvas id="weekChart"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="card-title">📊 Tasks Overview</div>
            <div class="chart-wrap"><canvas id="taskChart"></canvas></div>
        </div>
    </div>

    <div class="grid-2">
        <div class="chart-card">
            <div class="card-title">📚 Hours by Subject</div>
            <div class="chart-wrap"><canvas id="subjectChart"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="card-title">🔥 Study Streak</div>
            <div style="text-align:center;padding:30px 0;">
                <div style="font-size:56px;">🔥</div>
                <div style="font-family:'Syne',sans-serif;font-size:42px;font-weight:800;color:var(--warn);"><?= $user['streak'] ?></div>
                <div style="color:var(--muted);font-size:14px;margin-top:4px;">Day Streak</div>
                <div style="margin-top:16px;font-size:12px;color:var(--muted);">Keep logging in and studying daily!</div>
            </div>
        </div>
    </div>
</main>

<!-- Log Modal -->
<div class="modal-overlay" id="logModal">
    <div class="modal">
        <h3>⏱ Log Study Hours</h3>
        <form method="POST">
            <input type="hidden" name="action" value="log">
            <div class="form-group">
                <label>Subject</label>
                <select name="subject_id">
                    <option value="">— General —</option>
                    <?php
                    $currentYear = null;
                    $currentSemester = null;
                    foreach ($subs as $s):
                        if ($s['year'] != $currentYear || $s['semester'] != $currentSemester):
                            if ($currentYear !== null) echo '</optgroup>';
                            $yearLabel = $s['year'] . 'st Year';
                            if ($s['year'] == 2) $yearLabel = '2nd Year';
                            if ($s['year'] == 3) $yearLabel = '3rd Year';
                            if ($s['year'] >= 4) $yearLabel = $s['year'] . 'th Year';
                            echo '<optgroup label="' . $yearLabel . ' - Semester ' . $s['semester'] . '">';
                            $currentYear = $s['year'];
                            $currentSemester = $s['semester'];
                        endif;
                    ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['code'] ? $s['code'] . ': ' . $s['name'] : $s['name']) ?></option>
                    <?php endforeach; ?>
                    <?php if (!empty($subs)) echo '</optgroup>'; ?>
                </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label>Hours *</label>
                    <input type="number" name="hours" step="0.5" min="0.5" max="16" required placeholder="e.g. 2.5">
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="logged_date" value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Notes</label>
                <input type="text" name="notes" placeholder="What did you study?">
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:10px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('logModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary">Log Hours</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('logModal').addEventListener('click',function(e){if(e.target===this)this.classList.remove('active');});

// Weekly chart
new Chart(document.getElementById('weekChart'),{type:'bar',data:{labels:<?= json_encode($days) ?>,datasets:[{label:'Hours',data:<?= json_encode($weekValues) ?>,backgroundColor:'rgba(34,211,238,.25)',borderColor:'rgba(34,211,238,1)',borderWidth:2,borderRadius:6}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{color:'rgba(255,255,255,.05)'},ticks:{color:'#64748b'}},y:{grid:{color:'rgba(255,255,255,.05)'},ticks:{color:'#64748b'},beginAtZero:true}}}});

// Task donut
new Chart(document.getElementById('taskChart'),{type:'doughnut',data:{labels:['To Do','In Progress','Done'],datasets:[{data:[<?= $taskMap['todo'] ?>,<?= $taskMap['in_progress'] ?>,<?= $taskMap['done'] ?>],backgroundColor:['rgba(251,191,36,.6)','rgba(34,211,238,.6)','rgba(52,211,153,.6)'],borderWidth:0}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:'#64748b',font:{size:12}}}}}});

// Subject chart
<?php if (!empty($subjectData)): ?>
new Chart(document.getElementById('subjectChart'),{type:'bar',data:{labels:<?= json_encode(array_column($subjectData,'name')) ?>,datasets:[{label:'Hours',data:<?= json_encode(array_map(fn($s)=>(float)$s['total'],$subjectData)) ?>,backgroundColor:<?= json_encode(array_map(fn($s)=>$s['color'].'99',$subjectData)) ?>,borderColor:<?= json_encode(array_column($subjectData,'color')) ?>,borderWidth:2,borderRadius:6}]},options:{responsive:true,maintainAspectRatio:false,indexAxis:'y',plugins:{legend:{display:false}},scales:{x:{grid:{color:'rgba(255,255,255,.05)'},ticks:{color:'#64748b'},beginAtZero:true},y:{grid:{display:false},ticks:{color:'#e2e8f0',font:{size:12}}}}}});
<?php endif; ?>
</script>
</body>
</html>
