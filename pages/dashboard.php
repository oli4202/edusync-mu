<?php
// LEGACY FILE - REDIRECT TO MVC
header('Location: /dashboard');
exit;


// Stats
$tasksDue = $db->prepare("SELECT COUNT(*) FROM tasks WHERE user_id=? AND status!='done' AND due_date <= DATE_ADD(NOW(), INTERVAL 3 DAY)");
$tasksDue->execute([$user['id']]); $tasksDueCount = $tasksDue->fetchColumn();

$totalTasks = $db->prepare("SELECT COUNT(*) FROM tasks WHERE user_id=? AND status='done'");
$totalTasks->execute([$user['id']]); $doneCount = $totalTasks->fetchColumn();

$studyHours = $db->prepare("SELECT COALESCE(SUM(hours),0) FROM study_logs WHERE user_id=? AND WEEK(logged_date)=WEEK(NOW())");
$studyHours->execute([$user['id']]); $weekHours = $studyHours->fetchColumn();

$groupCount = $db->prepare("SELECT COUNT(*) FROM group_members WHERE user_id=?");
$groupCount->execute([$user['id']]); $myGroups = $groupCount->fetchColumn();

// Upcoming tasks
$upcoming = $db->prepare("SELECT t.*, s.name AS subject_name, s.color FROM tasks t LEFT JOIN subjects s ON t.subject_id=s.id WHERE t.user_id=? AND t.status!='done' ORDER BY t.due_date ASC LIMIT 5");
$upcoming->execute([$user['id']]); $upcomingTasks = $upcoming->fetchAll();

// Weekly study data for chart
$weekData = $db->prepare("SELECT DAYNAME(logged_date) as day, SUM(hours) as total FROM study_logs WHERE user_id=? AND logged_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(logged_date), DAYNAME(logged_date)");
$weekData->execute([$user['id']]); $chartData = $weekData->fetchAll();

$days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
$chartMap = [];
foreach ($chartData as $row) {
    $chartMap[substr($row['day'],0,3)] = (float)$row['total'];
}
$chartValues = array_map(fn($d) => $chartMap[$d] ?? 0, $days);

// Recent questions from question bank
$recentQ = $db->prepare("SELECT q.*, c.name AS course_name FROM questions q JOIN courses c ON q.course_id=c.id WHERE q.is_approved=1 ORDER BY q.created_at DESC LIMIT 4");
$recentQ->execute(); $recentQuestions = $recentQ->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — EduSync MU</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root{--bg:#0a0e1a;--card:#111827;--card2:#0f172a;--border:#1e2d45;--accent:#22d3ee;--accent2:#818cf8;--accent3:#34d399;--warn:#fbbf24;--text:#e2e8f0;--muted:#64748b;}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;}

/* ── Sidebar ── */
.sidebar{width:240px;min-height:100vh;background:var(--card2);border-right:1px solid var(--border);display:flex;flex-direction:column;padding:24px 0;position:fixed;top:0;left:0;z-index:100;}
.sidebar-logo{font-family:'Syne',sans-serif;font-size:22px;font-weight:800;background:linear-gradient(135deg,var(--accent),var(--accent2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;padding:0 24px;margin-bottom:8px;}
.sidebar-sub{font-size:10px;color:var(--muted);padding:0 24px;margin-bottom:28px;letter-spacing:.5px;}
.nav-section{font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;padding:0 24px;margin-bottom:8px;margin-top:16px;}
.nav-item{display:flex;align-items:center;gap:10px;padding:10px 24px;color:var(--muted);text-decoration:none;font-size:14px;transition:all .2s;border-left:2px solid transparent;}
.nav-item:hover,.nav-item.active{color:var(--text);background:rgba(34,211,238,.06);border-left-color:var(--accent);}
.nav-item .icon{font-size:16px;width:20px;text-align:center;}
.sidebar-footer{margin-top:auto;padding:20px 24px;border-top:1px solid var(--border);}
.user-chip{display:flex;align-items:center;gap:10px;}
.avatar{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:#0a0e1a;}
.user-name{font-size:13px;font-weight:500;}
.user-streak{font-size:11px;color:var(--warn);}

/* ── Main ── */
.main{margin-left:240px;flex:1;padding:32px;max-width:calc(100vw - 240px);}
.topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;}
.page-title{font-family:'Syne',sans-serif;font-size:22px;font-weight:700;}
.greeting{color:var(--muted);font-size:14px;margin-top:2px;}
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;font-family:'Syne',sans-serif;cursor:pointer;text-decoration:none;border:none;transition:all .2s;}
.btn-primary{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#0a0e1a;}
.btn-primary:hover{opacity:.9;}

/* ── Stats ── */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;}
.stat-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:20px;position:relative;overflow:hidden;}
.stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;}
.stat-card.cyan::before{background:var(--accent);}
.stat-card.purple::before{background:var(--accent2);}
.stat-card.green::before{background:var(--accent3);}
.stat-card.yellow::before{background:var(--warn);}
.stat-icon{font-size:22px;margin-bottom:10px;}
.stat-value{font-family:'Syne',sans-serif;font-size:28px;font-weight:800;}
.stat-label{font-size:12px;color:var(--muted);margin-top:4px;}

/* ── Grid ── */
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;}
.grid-3{display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px;}
.section-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:22px;}
.section-title{font-family:'Syne',sans-serif;font-size:14px;font-weight:700;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;}
.section-title a{font-size:12px;color:var(--accent);text-decoration:none;font-family:'DM Sans',sans-serif;font-weight:400;}

/* ── Task items ── */
.task-item{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);}
.task-item:last-child{border-bottom:none;}
.task-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;}
.task-text{flex:1;font-size:14px;}
.task-subject{font-size:11px;color:var(--muted);}
.task-due{font-size:11px;padding:2px 8px;border-radius:20px;background:rgba(251,191,36,.1);color:var(--warn);}
.task-due.urgent{background:rgba(248,113,113,.1);color:#f87171;}

/* ── Question items ── */
.q-item{padding:12px 0;border-bottom:1px solid var(--border);}
.q-item:last-child{border-bottom:none;}
.q-course{font-size:11px;color:var(--accent);margin-bottom:4px;}
.q-text{font-size:13px;line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}

/* ── Chart ── */
.chart-wrap{height:180px;position:relative;}

/* ── AI Banner ── */
.ai-banner{background:linear-gradient(135deg,rgba(34,211,238,.1),rgba(129,140,248,.1));border:1px solid rgba(34,211,238,.2);border-radius:14px;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;}
.ai-banner-text h3{font-family:'Syne',sans-serif;font-size:16px;font-weight:700;margin-bottom:4px;}
.ai-banner-text p{font-size:13px;color:var(--muted);}
.ai-icon{font-size:36px;}

@media(max-width:900px){
.stats-grid{grid-template-columns:repeat(2,1fr);}
.grid-2,.grid-3{grid-template-columns:1fr;}
.sidebar{display:none;}
.main{margin-left:0;max-width:100vw;}
}
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-logo">EduSync</div>
    <div class="sidebar-sub">MU SYLHET · SE DEPT</div>
    <div class="nav-section">Main</div>
    <a href="dashboard.php" class="nav-item active"><span class="icon">🏠</span> Dashboard</a>
    <a href="tasks.php" class="nav-item"><span class="icon">✅</span> Tasks & Kanban</a>
    <a href="subjects.php" class="nav-item"><span class="icon">📚</span> Subjects</a>
    <a href="calendar.php" class="nav-item"><span class="icon">📅</span> Calendar</a>
    <a href="analytics.php" class="nav-item"><span class="icon">📊</span> Analytics</a>
    <div class="nav-section">Social</div>
    <a href="groups.php" class="nav-item"><span class="icon">👥</span> Study Groups</a>
    <a href="notes.php" class="nav-item"><span class="icon">📝</span> Shared Notes</a>
    <a href="partners.php" class="nav-item"><span class="icon">🔍</span> Find Partners</a>
    <div class="nav-section">AI & Study</div>
    <a href="ai.php" class="nav-item"><span class="icon">🤖</span> AI Assistant</a>
    <a href="flashcards.php" class="nav-item"><span class="icon">🃏</span> Flashcards</a>
    <a href="question-bank.php" class="nav-item"><span class="icon">📖</span> Question Bank</a>
    <a href="suggestions.php" class="nav-item"><span class="icon">💡</span> Exam Suggestions</a>
    <?php if ($user['role'] === 'admin'): ?>
    <div class="nav-section">Admin</div>
    <a href="../admin/index.php" class="nav-item"><span class="icon">🛡️</span> Admin Panel</a>
    <?php endif; ?>
    <div class="sidebar-footer">
        <div class="user-chip">
            <div class="avatar"><?= strtoupper(substr($user['name'],0,1)) ?></div>
            <div>
                <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
                <div class="user-streak">🔥 <?= $user['streak'] ?> day streak</div>
            </div>
        </div>
    </div>
</aside>

<!-- Main Content -->
<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">Good <?= date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening') ?>, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?> 👋</div>
            <div class="greeting"><?= date('l, F j, Y') ?> · Semester <?= $user['semester'] ?></div>
        </div>
        <a href="tasks.php?new=1" class="btn btn-primary">+ Add Task</a>
    </div>

    <!-- AI Banner -->
    <div class="ai-banner">
        <div class="ai-banner-text">
            <h3>🤖 AI Study Tools Available</h3>
            <p>Generate compact exam answers, predict exam questions, create flashcards, and get a personalized study plan.</p>
        </div>
        <div>
            <a href="ai.php" class="btn btn-primary">Open AI Assistant →</a>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card cyan">
            <div class="stat-icon">⚡</div>
            <div class="stat-value"><?= $tasksDueCount ?></div>
            <div class="stat-label">Tasks Due Soon</div>
        </div>
        <div class="stat-card purple">
            <div class="stat-icon">✅</div>
            <div class="stat-value"><?= $doneCount ?></div>
            <div class="stat-label">Tasks Completed</div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon">⏱</div>
            <div class="stat-value"><?= number_format($weekHours, 1) ?>h</div>
            <div class="stat-label">Study Hours This Week</div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-icon">👥</div>
            <div class="stat-value"><?= $myGroups ?></div>
            <div class="stat-label">Study Groups</div>
        </div>
    </div>

    <!-- Chart + Tasks -->
    <div class="grid-3">
        <div class="section-card">
            <div class="section-title">📈 Weekly Study Hours <a href="analytics.php">View all →</a></div>
            <div class="chart-wrap">
                <canvas id="weekChart"></canvas>
            </div>
        </div>
        <div class="section-card">
            <div class="section-title">🔥 Streak & Activity</div>
            <div style="text-align:center;padding:20px 0;">
                <div style="font-size:52px;">🔥</div>
                <div style="font-family:'Syne',sans-serif;font-size:36px;font-weight:800;color:var(--warn)"><?= $user['streak'] ?></div>
                <div style="font-size:13px;color:var(--muted);margin-top:4px;">Day Streak</div>
                <div style="margin-top:20px;font-size:12px;color:var(--muted);">Keep it up! Log in daily and<br>complete tasks to maintain your streak.</div>
            </div>
        </div>
    </div>

    <!-- Upcoming Tasks + Recent Questions -->
    <div class="grid-2">
        <div class="section-card">
            <div class="section-title">📋 Upcoming Tasks <a href="tasks.php">View all →</a></div>
            <?php if (empty($upcomingTasks)): ?>
                <div style="color:var(--muted);font-size:14px;text-align:center;padding:20px 0;">No upcoming tasks. <a href="tasks.php?new=1" style="color:var(--accent)">Add one!</a></div>
            <?php else: ?>
            <?php foreach ($upcomingTasks as $task): ?>
            <div class="task-item">
                <div class="task-dot" style="background:<?= htmlspecialchars($task['color'] ?? '#64748b') ?>"></div>
                <div style="flex:1">
                    <div class="task-text"><?= htmlspecialchars($task['title']) ?></div>
                    <div class="task-subject"><?= htmlspecialchars($task['subject_name'] ?? 'No subject') ?></div>
                </div>
                <?php if ($task['due_date']): ?>
                <?php $daysLeft = (strtotime($task['due_date']) - time()) / 86400; ?>
                <span class="task-due <?= $daysLeft < 1 ? 'urgent' : '' ?>">
                    <?= $daysLeft < 0 ? 'Overdue' : ($daysLeft < 1 ? 'Today' : ceil($daysLeft).' days') ?>
                </span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="section-card">
            <div class="section-title">📖 Recent Questions <a href="question-bank.php">Browse all →</a></div>
            <?php if (empty($recentQuestions)): ?>
                <div style="color:var(--muted);font-size:14px;text-align:center;padding:20px 0;">No questions yet. <a href="question-bank.php" style="color:var(--accent)">Contribute!</a></div>
            <?php else: ?>
            <?php foreach ($recentQuestions as $q): ?>
            <div class="q-item">
                <div class="q-course"><?= htmlspecialchars($q['course_name']) ?> · <?= htmlspecialchars($q['exam_semester'] ?? '') ?> Sem <?= htmlspecialchars($q['exam_year'] ?? '') ?></div>
                <div class="q-text"><a href="question-detail.php?id=<?= $q['id'] ?>" style="color:var(--text);text-decoration:none"><?= htmlspecialchars($q['question_text']) ?></a></div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
const ctx = document.getElementById('weekChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($days) ?>,
        datasets: [{
            label: 'Hours',
            data: <?= json_encode($chartValues) ?>,
            backgroundColor: 'rgba(34,211,238,.25)',
            borderColor: 'rgba(34,211,238,1)',
            borderWidth: 2,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { color: '#64748b', font: { size: 11 } } },
            y: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { color: '#64748b', font: { size: 11 } }, beginAtZero: true }
        }
    }
});
</script>
</body>
</html>
