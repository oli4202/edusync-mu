<?php
// pages/grades.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$user = currentUser();
$db   = getDB();
$currentPage = 'grades';

// Handle actions
$msg = $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $subjectId = (int)$_POST['subject_id'];
        $title     = clean($_POST['title']);
        $score     = (float)$_POST['score'];
        $maxScore  = (float)($_POST['max_score'] ?? 100);
        $examDate  = clean($_POST['exam_date'] ?? date('Y-m-d'));
        $notes     = clean($_POST['notes'] ?? '');
        if (!$title || !$score) { $err = 'Fill in required fields.'; }
        else {
            $db->prepare("INSERT INTO grades (user_id,subject_id,title,score,max_score,exam_date,notes) VALUES (?,?,?,?,?,?,?)")
               ->execute([$user['id'], $subjectId ?: null, $title, $score, $maxScore, $examDate, $notes]);
            $msg = 'Grade added!';
        }
    } elseif ($action === 'delete') {
        $db->prepare("DELETE FROM grades WHERE id=? AND user_id=?")->execute([(int)$_POST['grade_id'], $user['id']]);
        $msg = 'Grade deleted.';
    }
}

// All grades
$gradesStmt = $db->prepare("SELECT g.*, s.name AS subject_name, s.color
    FROM grades g LEFT JOIN subjects s ON g.subject_id=s.id
    WHERE g.user_id=? ORDER BY g.exam_date DESC");
$gradesStmt->execute([$user['id']]);
$grades = $gradesStmt->fetchAll();

// Per-subject averages
$avgStmt = $db->prepare("SELECT s.name, s.color, AVG(g.score/g.max_score*100) AS avg_pct, COUNT(*) AS cnt
    FROM grades g JOIN subjects s ON g.subject_id=s.id
    WHERE g.user_id=? GROUP BY g.subject_id ORDER BY avg_pct DESC");
$avgStmt->execute([$user['id']]);
$subjectAvgs = $avgStmt->fetchAll();

// MU grading scale
function getLetterGrade($pct) {
    if ($pct >= 80) return ['A+', '#34d399'];
    if ($pct >= 75) return ['A',  '#34d399'];
    if ($pct >= 70) return ['A-', '#34d399'];
    if ($pct >= 65) return ['B+', '#22d3ee'];
    if ($pct >= 60) return ['B',  '#22d3ee'];
    if ($pct >= 55) return ['B-', '#22d3ee'];
    if ($pct >= 50) return ['C+', '#fbbf24'];
    if ($pct >= 45) return ['C',  '#fbbf24'];
    if ($pct >= 40) return ['D',  '#f97316'];
    return ['F', '#f87171'];
}
function getGPA($pct) {
    if ($pct >= 80) return 4.00;
    if ($pct >= 75) return 3.75;
    if ($pct >= 70) return 3.50;
    if ($pct >= 65) return 3.25;
    if ($pct >= 60) return 3.00;
    if ($pct >= 55) return 2.75;
    if ($pct >= 50) return 2.50;
    if ($pct >= 45) return 2.25;
    if ($pct >= 40) return 2.00;
    return 0.00;
}

// Compute CGPA
$cgpa = 0;
if (!empty($grades)) {
    $gpas = array_map(fn($g) => getGPA($g['score']/$g['max_score']*100), $grades);
    $cgpa = round(array_sum($gpas) / count($gpas), 2);
}

$overallAvg = count($grades) ? round(array_sum(array_map(fn($g)=>$g['score']/$g['max_score']*100, $grades))/count($grades),1) : 0;
$best = count($grades) ? max(array_map(fn($g)=>$g['score']/$g['max_score']*100, $grades)) : 0;

// User subjects for form
$subjects = $db->prepare("SELECT * FROM subjects WHERE user_id=? ORDER BY year ASC, semester ASC, name ASC");
$subjects->execute([$user['id']]);
$mySubjects = $subjects->fetchAll();

// Chart data — last 10 grades timeline
$chartGrades = array_slice(array_reverse($grades), 0, 10);
$chartLabels = array_map(fn($g) => htmlspecialchars($g['title']), $chartGrades);
$chartData   = array_map(fn($g) => round($g['score']/$g['max_score']*100,1), $chartGrades);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Grades & Results — EduSync MU</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.grade-badge { display:inline-block; padding:4px 12px; border-radius:8px; font-family:'Syne',sans-serif; font-weight:800; font-size:14px; }
.gpa-ring { display:flex; flex-direction:column; align-items:center; justify-content:center; }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.7); z-index:999; align-items:center; justify-content:center; }
.modal-overlay.open { display:flex; }
.modal { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:28px; width:100%; max-width:480px; animation:fadeUp .3s ease; }
@keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
.modal-title { font-family:'Syne',sans-serif; font-size:16px; font-weight:700; margin-bottom:20px; }
.field { margin-bottom:14px; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.mu-scale { font-size:12px; }
.mu-scale td { padding:4px 10px; border-bottom:1px solid var(--border); }
</style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">🎓 Grades & Results</div>
            <div class="page-sub">Track your academic performance — MU Sylhet grading scale</div>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="result-lookup.php" class="btn btn-outline">🔍 MU Official Result</a>
            <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('open')">+ Add Grade</button>
        </div>
    </div>

    <?php if ($msg): ?><div class="alert-success">✅ <?= $msg ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert-error">⚠ <?= $err ?></div><?php endif; ?>

    <!-- Stats -->
    <div class="grid-4" style="margin-bottom:24px;">
        <div class="stat-card <?= $cgpa>=3.5?'green':($cgpa>=2.5?'cyan':'yellow') ?>">
            <div class="stat-icon">🏆</div>
            <div class="stat-value"><?= $cgpa ?></div>
            <div class="stat-label">Current CGPA</div>
        </div>
        <div class="stat-card cyan">
            <div class="stat-icon">📊</div>
            <div class="stat-value"><?= $overallAvg ?>%</div>
            <div class="stat-label">Average Score</div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon">⭐</div>
            <div class="stat-value"><?= round($best,1) ?>%</div>
            <div class="stat-label">Best Score</div>
        </div>
        <div class="stat-card purple">
            <div class="stat-icon">📝</div>
            <div class="stat-value"><?= count($grades) ?></div>
            <div class="stat-label">Total Exams Logged</div>
        </div>
    </div>

    <div class="grid-2" style="margin-bottom:24px;">
        <!-- Performance Chart -->
        <div class="card">
            <div class="card-title">📈 Score Trend (Last 10)</div>
            <div style="height:200px;position:relative;">
                <canvas id="gradeChart"></canvas>
            </div>
        </div>

        <!-- Subject Averages -->
        <div class="card">
            <div class="card-title">📚 Subject Averages</div>
            <?php if (empty($subjectAvgs)): ?>
            <div style="color:var(--muted);font-size:14px;text-align:center;padding:20px;">Add grades with subjects assigned to see breakdown.</div>
            <?php else: ?>
            <?php foreach ($subjectAvgs as $sa):
                [$letter,$color] = getLetterGrade($sa['avg_pct']);
            ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);">
                <div>
                    <div style="font-size:14px;font-weight:500;"><?= htmlspecialchars($sa['name']) ?></div>
                    <div style="font-size:12px;color:var(--muted)"><?= $sa['cnt'] ?> exam<?= $sa['cnt']!=1?'s':'' ?></div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <span style="font-size:13px;color:var(--muted)"><?= round($sa['avg_pct'],1) ?>%</span>
                    <span class="grade-badge" style="background:<?= $color ?>22;color:<?= $color ?>"><?= $letter ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Grades Table -->
    <div class="card" style="margin-bottom:24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <div class="card-title" style="margin-bottom:0;">📋 All Grades</div>
        </div>
        <?php if (empty($grades)): ?>
        <div style="text-align:center;padding:32px;color:var(--muted);">
            <div style="font-size:36px;margin-bottom:10px;">📝</div>
            No grades yet. <button onclick="document.getElementById('addModal').classList.add('open')" class="btn btn-primary btn-sm">+ Add First Grade</button>
        </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr><th>Exam / Assessment</th><th>Subject</th><th>Date</th><th>Score</th><th>%</th><th>Grade</th><th>GPA</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($grades as $g):
                    $pct = round($g['score']/$g['max_score']*100,1);
                    [$letter,$color] = getLetterGrade($pct);
                    $gpa = getGPA($pct);
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($g['title']) ?></strong>
                        <?php if ($g['notes']): ?><div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($g['notes']) ?></div><?php endif; ?>
                    </td>
                    <td>
                        <?php if ($g['subject_name']): ?>
                        <span style="display:inline-flex;align-items:center;gap:6px;">
                            <span style="width:8px;height:8px;border-radius:50%;background:<?= htmlspecialchars($g['color']??'#64748b') ?>"></span>
                            <?= htmlspecialchars($g['subject_name']) ?>
                        </span>
                        <?php else: ?><span style="color:var(--muted)">—</span><?php endif; ?>
                    </td>
                    <td style="color:var(--muted);font-size:13px;"><?= $g['exam_date'] ? date('M j, Y', strtotime($g['exam_date'])) : '—' ?></td>
                    <td><strong><?= $g['score'] ?></strong><span style="color:var(--muted);font-size:12px;"> / <?= $g['max_score'] ?></span></td>
                    <td><strong><?= $pct ?>%</strong></td>
                    <td><span class="grade-badge" style="background:<?= $color ?>22;color:<?= $color ?>"><?= $letter ?></span></td>
                    <td style="font-weight:600;color:var(--accent)"><?= number_format($gpa,2) ?></td>
                    <td>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="grade_id" value="<?= $g['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- MU Grading Scale Reference -->
    <div class="card">
        <div class="card-title">📐 MU Sylhet Grading Scale</div>
        <div class="grid-2">
            <table class="mu-scale">
                <tr style="color:var(--muted)"><th>Marks %</th><th>Letter</th><th>Grade Point</th></tr>
                <tr><td>80–100</td><td style="color:#34d399;font-weight:700">A+</td><td>4.00</td></tr>
                <tr><td>75–79</td><td style="color:#34d399;font-weight:700">A</td><td>3.75</td></tr>
                <tr><td>70–74</td><td style="color:#34d399;font-weight:700">A-</td><td>3.50</td></tr>
                <tr><td>65–69</td><td style="color:#22d3ee;font-weight:700">B+</td><td>3.25</td></tr>
                <tr><td>60–64</td><td style="color:#22d3ee;font-weight:700">B</td><td>3.00</td></tr>
            </table>
            <table class="mu-scale">
                <tr style="color:var(--muted)"><th>Marks %</th><th>Letter</th><th>Grade Point</th></tr>
                <tr><td>55–59</td><td style="color:#22d3ee;font-weight:700">B-</td><td>2.75</td></tr>
                <tr><td>50–54</td><td style="color:#fbbf24;font-weight:700">C+</td><td>2.50</td></tr>
                <tr><td>45–49</td><td style="color:#fbbf24;font-weight:700">C</td><td>2.25</td></tr>
                <tr><td>40–44</td><td style="color:#f97316;font-weight:700">D</td><td>2.00</td></tr>
                <tr><td>Below 40</td><td style="color:#f87171;font-weight:700">F</td><td>0.00</td></tr>
            </table>
        </div>
    </div>
</main>

<!-- Add Grade Modal -->
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-title">🎓 Add Grade / Result</div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="field">
                <label>Exam / Assessment Title *</label>
                <input type="text" name="title" placeholder="e.g. Mid-term Exam, Assignment 1..." required>
            </div>
            <div class="field">
                <label>Subject (optional)</label>
                <select name="subject_id">
                    <option value="">No subject</option>
                    <?php
                    $currentYear = null;
                    $currentSemester = null;
                    foreach ($mySubjects as $s):
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
                    <?php if (!empty($mySubjects)) echo '</optgroup>'; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>Score Obtained *</label>
                    <input type="number" name="score" step="0.01" placeholder="e.g. 78" required>
                </div>
                <div class="field">
                    <label>Total Marks</label>
                    <input type="number" name="max_score" step="0.01" value="100">
                </div>
            </div>
            <div class="field">
                <label>Exam Date</label>
                <input type="date" name="exam_date" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="field">
                <label>Notes (optional)</label>
                <input type="text" name="notes" placeholder="e.g. Final exam, Theory part...">
            </div>
            <div id="gradePreview" style="display:none;background:rgba(34,211,238,.06);border:1px solid rgba(34,211,238,.2);border-radius:10px;padding:12px;margin-bottom:14px;font-size:14px;text-align:center;"></div>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn-primary">Save Grade</button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
// Live grade preview
const scoreInput = document.querySelector('[name=score]');
const maxInput   = document.querySelector('[name=max_score]');
const preview    = document.getElementById('gradePreview');
function updatePreview() {
    const s = parseFloat(scoreInput.value), m = parseFloat(maxInput.value)||100;
    if (!isNaN(s) && s>0) {
        const pct = (s/m*100).toFixed(1);
        let letter='F', gpa=0;
        if(pct>=80){letter='A+';gpa=4.00}else if(pct>=75){letter='A';gpa=3.75}else if(pct>=70){letter='A-';gpa=3.50}
        else if(pct>=65){letter='B+';gpa=3.25}else if(pct>=60){letter='B';gpa=3.00}else if(pct>=55){letter='B-';gpa=2.75}
        else if(pct>=50){letter='C+';gpa=2.50}else if(pct>=45){letter='C';gpa=2.25}else if(pct>=40){letter='D';gpa=2.00}
        preview.style.display='block';
        preview.innerHTML=`Score: <strong>${pct}%</strong> → Grade: <strong style="color:var(--accent)">${letter}</strong> → GPA: <strong>${gpa.toFixed(2)}</strong>`;
    } else { preview.style.display='none'; }
}
scoreInput.addEventListener('input', updatePreview);
maxInput.addEventListener('input', updatePreview);
document.getElementById('addModal').addEventListener('click', function(e){if(e.target===this)this.classList.remove('open')});

// Chart
const ctx = document.getElementById('gradeChart').getContext('2d');
new Chart(ctx, {
    type:'line',
    data:{
        labels:<?= json_encode($chartLabels) ?>,
        datasets:[{
            label:'Score %', data:<?= json_encode($chartData) ?>,
            borderColor:'#22d3ee', backgroundColor:'rgba(34,211,238,.1)',
            borderWidth:2, pointRadius:5, tension:.3, fill:true
        }]
    },
    options:{
        responsive:true, maintainAspectRatio:false,
        plugins:{legend:{display:false}},
        scales:{
            x:{grid:{color:'rgba(255,255,255,.05)'},ticks:{color:'#64748b',font:{size:10},maxRotation:30}},
            y:{grid:{color:'rgba(255,255,255,.05)'},ticks:{color:'#64748b',font:{size:11}},min:0,max:100,
               ticks:{callback:v=>v+'%',color:'#64748b',font:{size:11}}}
        }
    }
});
</script>
</body>
</html>
