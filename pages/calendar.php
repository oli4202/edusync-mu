<?php
// pages/calendar.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$user = currentUser();
$db   = getDB();
$currentPage = 'calendar';

// Handle Month / Year logic
$m = isset($_GET['m']) ? intval($_GET['m']) : intval(date('m'));
$y = isset($_GET['y']) ? intval($_GET['y']) : intval(date('Y'));

if ($m < 1) { $m = 12; $y--; }
if ($m > 12) { $m = 1; $y++; }

$prevM = $m - 1; $prevY = $y;
$nextM = $m + 1; $nextY = $y;

$monthName = date('F', mktime(0, 0, 0, $m, 1, $y));
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $m, $y);
$firstDayOfWeek = date('N', mktime(0, 0, 0, $m, 1, $y)); // 1 (Mon) to 7 (Sun)

// Fetch user tasks for this month
$stmt = $db->prepare("
    SELECT t.id, t.title, t.priority, t.due_date, s.color, s.name as subject_name 
    FROM tasks t 
    LEFT JOIN subjects s ON t.subject_id = s.id 
    WHERE t.user_id = ? AND MONTH(t.due_date) = ? AND YEAR(t.due_date) = ?
");
$stmt->execute([$user['id'], $m, $y]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Map tasks by day
$calendarData = [];
foreach ($tasks as $task) {
    if ($task['due_date']) {
        $dayNum = intval(date('d', strtotime($task['due_date'])));
        $calendarData[$dayNum][] = [
            'type'  => 'task',
            'title' => $task['title'],
            'color' => $task['color'] ?: '#64748b',
            'subject' => $task['subject_name']
        ];
    }
}

// Metropolitan University Sylhet Academic Calendar Events (3 Semesters, 4 Months Each)
// Spring (Jan-Apr), Summer (May-Aug), Fall (Sep-Dec) - No Midterms, only Finals.
$muEvents = [];

// Selected Holidays
if ($m == 2) $muEvents[21][] = ['type'=>'holiday', 'title'=>'Intl. Mother Language Day', 'color'=>'#fbbf24'];
if ($m == 3) $muEvents[26][] = ['type'=>'holiday', 'title'=>'Independence Day', 'color'=>'#fbbf24'];
if ($m == 4) $muEvents[14][] = ['type'=>'holiday', 'title'=>'Pohela Boishakh', 'color'=>'#fbbf24'];
if ($m == 5) $muEvents[1][]  = ['type'=>'holiday', 'title'=>'May Day', 'color'=>'#fbbf24'];
if ($m == 12) $muEvents[16][] = ['type'=>'holiday', 'title'=>'Victory Day', 'color'=>'#fbbf24'];

// Start of Semester
if (in_array($m, [1, 5, 9])) {
    $semName = ($m == 1) ? 'Spring' : (($m == 5) ? 'Summer' : 'Fall');
    $muEvents[5][] = ['type'=>'holiday', 'title'=>"$semName Semester Classes Begin", 'color'=>'#34d399'];
}

// Final Exams (Always the 4th month of the semester)
if (in_array($m, [4, 8, 12])) {
    $semName = ($m == 4) ? 'Spring' : (($m == 8) ? 'Summer' : 'Fall');
    $muEvents[15][] = ['type'=>'exam', 'title'=>"$semName Semester Final Exams Begin", 'color'=>'#f87171'];
    $muEvents[28][] = ['type'=>'deadline', 'title'=>"$semName Semester Ends", 'color'=>'#818cf8'];
}

// Merge MU Events into calendar
foreach ($muEvents as $d => $evts) {
    if (!isset($calendarData[$d])) $calendarData[$d] = [];
    $calendarData[$d] = array_merge($calendarData[$d], $evts);
}

// Get upcoming global tasks (for sidebar widget)
$upcomingStmt = $db->prepare("SELECT title, due_date FROM tasks WHERE user_id=? AND due_date >= CURRENT_DATE AND status!='done' ORDER BY due_date ASC LIMIT 4");
$upcomingStmt->execute([$user['id']]);
$upcomingList = $upcomingStmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Academic Calendar — EduSync MU</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
/* Adjust colors specific to calendar */
.cal-layout { display:grid; grid-template-columns:1fr 300px; gap:24px; margin-top:20px; align-items:start; }
@media(max-width:1000px) { .cal-layout { grid-template-columns:1fr; } }
.cal-card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:24px; box-shadow:0 8px 30px rgba(0,0,0,0.2); }
.cal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
.cal-title { font-family:'Syne', sans-serif; font-size:24px; font-weight:700; display:flex; align-items:center; gap:12px; }
.cal-nav { display:flex; gap:10px; }
.cal-nav button { background:var(--card2); border:1px solid var(--border); color:var(--text); width:36px; height:36px; border-radius:8px; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; justify-content:center; }
.cal-nav button:hover { background:rgba(34,211,238,0.1); border-color:var(--accent); color:var(--accent); }

/* The Grid */
.cal-grid { display:grid; grid-template-columns:repeat(7, 1fr); gap:8px; }
.cal-day-header { text-align:center; font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:1px; padding-bottom:12px; border-bottom:1px solid var(--border); margin-bottom:8px; }
.cal-cell { 
    background:var(--card2); border:1px solid transparent; border-radius:12px; 
    min-height:100px; padding:8px; display:flex; flex-direction:column; gap:6px;
    transition:all 0.2s; position:relative;
}
.cal-cell:hover { border-color:rgba(34,211,238,0.3); background:#151b2b; }
.cal-cell.empty { background:transparent; border:none; }
.cal-cell.today { border:1px solid var(--accent); background:rgba(34,211,238,0.03); }
.cal-date-num { font-size:14px; font-weight:600; color:var(--muted); align-self:flex-end; margin-bottom:4px; }
.cal-cell.today .cal-date-num { color:var(--accent); background:rgba(34,211,238,0.15); width:24px; height:24px; display:flex; align-items:center; justify-content:center; border-radius:50%; }

/* Events */
.cal-event {
    font-size:11px; padding:4px 8px; border-radius:6px; background:rgba(255,255,255,0.05);
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis; cursor:pointer;
    border-left:3px solid var(--accent); font-weight:500; transition:transform 0.2s;
}
.cal-event:hover { transform:translateX(2px); filter:brightness(1.2); }
.evt-holiday { border-left-color:var(--warn); background:rgba(251,191,36,0.1); color:#fde68a; }
.evt-exam { border-left-color:#f87171; background:rgba(248,113,113,0.1); color:#fca5a5; }
.evt-task { border-left-color:var(--accent); }

/* Side Panel */
.side-widget { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:20px; margin-bottom:20px; }
.widget-title { font-family:'Syne',sans-serif; font-size:16px; font-weight:700; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
.widget-item { display:flex; gap:12px; margin-bottom:12px; padding-bottom:12px; border-bottom:1px solid var(--border); }
.widget-item:last-child { margin-bottom:0; padding-bottom:0; border-bottom:none; }
.widget-date { flex-shrink:0; background:var(--card2); border-radius:8px; width:44px; height:44px; display:flex; flex-direction:column; align-items:center; justify-content:center; }
.w-mon { font-size:10px; color:var(--accent); font-weight:700; text-transform:uppercase; }
.w-day { font-size:16px; font-family:'Syne',sans-serif; font-weight:800; }
.w-info { flex:1; display:flex; flex-direction:column; justify-content:center; }
.w-title { font-size:13px; font-weight:600; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
</style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>

<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">📅 Academic Calendar</div>
            <div class="page-sub">Metropolitan University Sylhet</div>
        </div>
        <a href="tasks.php?new=1" class="btn btn-primary">+ Add Deadline</a>
    </div>

    <div class="cal-layout">
        <!-- Main Calendar -->
        <div class="cal-card">
            <div class="cal-header">
                <div class="cal-title">
                    <?= $monthName ?> <?= $y ?>
                </div>
                <div class="cal-nav">
                    <button onclick="window.location.href='calendar.php?m=<?= $prevM ?>&y=<?= $prevY ?>'">←</button>
                    <button onclick="window.location.href='calendar.php?m=<?= date('m') ?>&y=<?= date('Y') ?>'">Today</button>
                    <button onclick="window.location.href='calendar.php?m=<?= $nextM ?>&y=<?= $nextY ?>'">→</button>
                </div>
            </div>

            <div class="cal-grid">
                <div class="cal-day-header">Mon</div>
                <div class="cal-day-header">Tue</div>
                <div class="cal-day-header">Wed</div>
                <div class="cal-day-header">Thu</div>
                <div class="cal-day-header">Fri</div>
                <div class="cal-day-header">Sat</div>
                <div class="cal-day-header">Sun</div>

                <?php
                // Empty cells before the first day of the month
                // Note: $firstDayOfWeek is 1-7 (Mon-Sun)
                for ($i = 1; $i < $firstDayOfWeek; $i++) {
                    echo '<div class="cal-cell empty"></div>';
                }

                // Days of the month
                $todayD = intval(date('d'));
                $todayM = intval(date('m'));
                $todayY = intval(date('Y'));

                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $isToday = ($d == $todayD && $m == $todayM && $y == $todayY) ? 'today' : '';
                    echo "<div class='cal-cell $isToday'>";
                    echo "<div class='cal-date-num'>$d</div>";

                    if (isset($calendarData[$d])) {
                        foreach ($calendarData[$d] as $evt) {
                            $evtClass = 'evt-task';
                            $style = '';
                            if ($evt['type'] === 'holiday') $evtClass = 'evt-holiday';
                            if ($evt['type'] === 'exam') $evtClass = 'evt-exam';
                            if ($evt['type'] === 'task' && !empty($evt['color'])) {
                                $style = "border-left-color: {$evt['color']};";
                            }
                            
                            $title = htmlspecialchars($evt['title']);
                            if(isset($evt['subject']) && $evt['subject']) {
                                $title = htmlspecialchars($evt['subject']) . ': ' . $title;
                            }
                            
                            echo "<div class='cal-event $evtClass' style='$style' title=\"$title\">$title</div>";
                        }
                    }

                    echo "</div>";
                }

                // Fill remaining cells to complete the grid (optional, but looks cleaner)
                $totalCells = ($firstDayOfWeek - 1) + $daysInMonth;
                $remCells = 42 - $totalCells; // Always show 6 rows for consistency
                if($remCells >= 7 && $totalCells <= 35) $remCells -= 7; // if 5 rows are enough
                
                for ($i = 0; $i < $remCells; $i++) {
                    echo '<div class="cal-cell empty"></div>';
                }
                ?>
            </div>
        </div>

        <!-- Side Panel -->
        <div>
            <div class="side-widget">
                <div class="widget-title">📌 MU Guidelines</div>
                <div style="font-size:13px; color:var(--muted); line-height:1.6;">
                    <p style="margin-bottom:10px;">Welcome to the MU SE Academic Calendar. Keep track of your assignments, quizzes, and university events.</p>
                    <ul style="padding-left:16px; margin-bottom:10px;">
                        <li>MU runs on a tri-semester system (Spring, Summer, Fall) of 4 months each.</li>
                        <li>Final exams are held at the end of each semester. There are no midterms.</li>
                        <li>Campus holidays are highlighted in <span style="color:#fbbf24">yellow</span>.</li>
                    </ul>
                </div>
            </div>

            <div class="side-widget">
                <div class="widget-title">⏳ Upcoming Deadlines</div>
                <?php if(empty($upcomingList)): ?>
                    <div style="font-size:13px; color:var(--muted); text-align:center; padding:20px 0;">No upcoming deadlines. You're all caught up! ✨</div>
                <?php else: ?>
                    <?php foreach($upcomingList as $up): 
                        $uDate = strtotime($up['due_date']);
                    ?>
                    <div class="widget-item">
                        <div class="widget-date">
                            <span class="w-mon"><?= date('M', $uDate) ?></span>
                            <span class="w-day"><?= date('d', $uDate) ?></span>
                        </div>
                        <div class="w-info">
                            <div class="w-title"><?= htmlspecialchars($up['title']) ?></div>
                            <div style="font-size:11px; color:var(--muted); margin-top:2px;">
                                <?= (date('Y-m-d', $uDate) == date('Y-m-d')) ? '<span style="color:var(--warn)">Due Today</span>' : 'Due in ' . ceil(($uDate - time())/86400) . ' days' ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <a href="tasks.php" class="btn btn-outline" style="width:100%; margin-top:10px; justify-content:center;">View all tasks</a>
            </div>
        </div>
    </div>
</main>

</body>
</html>
