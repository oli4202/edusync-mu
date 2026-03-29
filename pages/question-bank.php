<?php
// pages/question-bank.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$user = currentUser();
$db   = getDB();

// Filters
$courseId   = (int)($_GET['course'] ?? 0);
$yearFilter = (int)($_GET['year'] ?? 0);
$semFilter  = clean($_GET['sem'] ?? '');
$search     = clean($_GET['q'] ?? '');

// Build query
$where = ["q.is_approved = 1"];
$params = [];
if ($courseId)   { $where[] = "q.course_id = ?"; $params[] = $courseId; }
if ($yearFilter) { $where[] = "q.exam_year = ?";  $params[] = $yearFilter; }
if ($semFilter)  { $where[] = "q.exam_semester = ?"; $params[] = $semFilter; }
if ($search)     { $where[] = "q.question_text LIKE ?"; $params[] = "%$search%"; }

$sql = "SELECT q.*, c.name AS course_name, c.code AS course_code,
        (SELECT COUNT(*) FROM answers a WHERE a.question_id=q.id AND a.is_approved=1) AS answer_count,
        (SELECT COUNT(*) FROM question_bookmarks b WHERE b.question_id=q.id AND b.user_id=?) AS bookmarked
        FROM questions q JOIN courses c ON q.course_id=c.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY q.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute(array_merge([$user['id']], $params));
$questions = $stmt->fetchAll();

// Courses for filter
$courses = $db->query("SELECT * FROM courses ORDER BY year, semester, name")->fetchAll();

// Topic frequency for heatmap (top topics)
$topTopics = $db->prepare("SELECT qt.topic_name, COUNT(*) as cnt FROM question_topics qt JOIN questions q ON qt.question_id=q.id WHERE q.is_approved=1 GROUP BY qt.topic_name ORDER BY cnt DESC LIMIT 15");
$topTopics->execute(); $hotTopics = $topTopics->fetchAll();

$years = $db->query("SELECT DISTINCT exam_year FROM questions WHERE exam_year IS NOT NULL ORDER BY exam_year DESC")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Question Bank — EduSync MU</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root{--bg:#0a0e1a;--card:#111827;--card2:#0f172a;--border:#1e2d45;--accent:#22d3ee;--accent2:#818cf8;--accent3:#34d399;--warn:#fbbf24;--text:#e2e8f0;--muted:#64748b;}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;}
.sidebar{width:240px;min-height:100vh;background:var(--card2);border-right:1px solid var(--border);display:flex;flex-direction:column;padding:24px 0;position:fixed;top:0;left:0;z-index:100;}
.sidebar-logo{font-family:'Syne',sans-serif;font-size:22px;font-weight:800;background:linear-gradient(135deg,var(--accent),var(--accent2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;padding:0 24px;margin-bottom:32px;}
.nav-item{display:flex;align-items:center;gap:10px;padding:10px 24px;color:var(--muted);text-decoration:none;font-size:14px;transition:all .2s;border-left:2px solid transparent;}
.nav-item:hover,.nav-item.active{color:var(--text);background:rgba(34,211,238,.06);border-left-color:var(--accent);}
.nav-item .icon{font-size:16px;width:20px;text-align:center;}
.main{margin-left:240px;flex:1;padding:32px;}
.topbar{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:16px;}
.page-title{font-family:'Syne',sans-serif;font-size:22px;font-weight:700;}
.page-sub{color:var(--muted);font-size:14px;margin-top:4px;}
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;font-family:'Syne',sans-serif;cursor:pointer;text-decoration:none;border:none;transition:all .2s;}
.btn-primary{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#0a0e1a;}
.btn-outline{background:transparent;border:1px solid var(--border);color:var(--text);}
.btn-outline:hover{border-color:var(--accent);color:var(--accent);}
.layout{display:grid;grid-template-columns:1fr 280px;gap:20px;}
.filter-bar{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:20px;margin-bottom:20px;display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;}
.filter-group label{font-size:11px;color:var(--muted);display:block;margin-bottom:5px;text-transform:uppercase;letter-spacing:.5px;}
.filter-group select,.filter-group input{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:8px 12px;color:var(--text);font-size:13px;font-family:inherit;outline:none;}
.filter-group select:focus,.filter-group input:focus{border-color:var(--accent);}
.filter-group select option{background:var(--card);}
.search-input{min-width:220px;}
.q-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:20px;margin-bottom:14px;transition:border-color .2s;cursor:pointer;}
.q-card:hover{border-color:rgba(34,211,238,.3);}
.q-header{display:flex;align-items:center;gap:10px;margin-bottom:10px;flex-wrap:wrap;}
.q-course-badge{background:rgba(34,211,238,.1);border:1px solid rgba(34,211,238,.2);color:var(--accent);font-size:11px;padding:3px 10px;border-radius:20px;}
.q-year-badge{background:rgba(129,140,248,.1);border:1px solid rgba(129,140,248,.2);color:var(--accent2);font-size:11px;padding:3px 10px;border-radius:20px;}
.q-type-badge{background:rgba(52,211,153,.1);color:var(--accent3);font-size:11px;padding:3px 10px;border-radius:20px;}
.q-marks{margin-left:auto;font-size:12px;color:var(--warn);font-weight:600;}
.q-text{font-size:15px;line-height:1.6;margin-bottom:12px;}
.q-footer{display:flex;align-items:center;justify-content:space-between;}
.q-stats{display:flex;gap:16px;font-size:12px;color:var(--muted);}
.bookmark-btn{background:none;border:none;color:var(--muted);cursor:pointer;font-size:18px;transition:color .2s;}
.bookmark-btn.active{color:var(--warn);}
.bookmark-btn:hover{color:var(--warn);}

/* Sidebar widgets */
.widget{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:18px;margin-bottom:16px;}
.widget-title{font-family:'Syne',sans-serif;font-size:13px;font-weight:700;margin-bottom:14px;}
.topic-tag{display:inline-block;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:6px;padding:4px 10px;font-size:12px;margin:3px;cursor:pointer;transition:all .2s;}
.topic-tag:hover{background:rgba(34,211,238,.1);border-color:var(--accent);color:var(--accent);}
.topic-count{font-size:10px;color:var(--muted);margin-left:4px;}
.empty-state{text-align:center;padding:40px;color:var(--muted);}
.empty-state .icon{font-size:40px;margin-bottom:12px;}

@media(max-width:900px){.layout{grid-template-columns:1fr;}.sidebar{display:none;}.main{margin-left:0;}}
optgroup {
    background: rgba(34, 211, 238, 0.1) !important;
    color: #102c31 !important;
    font-weight: 600;
}
</style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-logo">EduSync</div>
    <a href="dashboard.php" class="nav-item"><span class="icon">🏠</span> Dashboard</a>
    <a href="tasks.php" class="nav-item"><span class="icon">✅</span> Tasks</a>
    <a href="analytics.php" class="nav-item"><span class="icon">📊</span> Analytics</a>
    <a href="groups.php" class="nav-item"><span class="icon">👥</span> Study Groups</a>
    <a href="ai.php" class="nav-item"><span class="icon">🤖</span> AI Assistant</a>
    <a href="flashcards.php" class="nav-item"><span class="icon">🃏</span> Flashcards</a>
    <a href="question-bank.php" class="nav-item active"><span class="icon">📖</span> Question Bank</a>
    <a href="suggestions.php" class="nav-item"><span class="icon">💡</span> Exam Suggestions</a>
    <?php if ($user['role'] === 'admin'): ?>
    <a href="../admin/index.php" class="nav-item"><span class="icon">🛡️</span> Admin Panel</a>
    <?php endif; ?>
</aside>

<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">📖 MU Sylhet Question Bank</div>
            <div class="page-sub">Previous semester exam questions with answers & AI compact solutions</div>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="submit-question.php" class="btn btn-outline">+ Submit Question</a>
            <a href="suggestions.php" class="btn btn-primary">💡 AI Exam Suggestions</a>
        </div>
    </div>

    <!-- Filter Bar -->
    <form method="GET" class="filter-bar">
        <div class="filter-group">
            <label>Course</label>
            <select name="course">
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
                <option value="<?= $c['id'] ?>" <?= $courseId == $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['code']) ?> — <?= htmlspecialchars($c['name']) ?>
                </option>
                <?php endforeach; ?>
                <?php if (!empty($courses)) echo '</optgroup>'; ?>
            </select>
        </div>
        <div class="filter-group">
            <label>Exam Year</label>
            <select name="year">
                <option value="">All Years</option>
                <?php foreach ($years as $y): ?>
                <option value="<?= $y ?>" <?= $yearFilter == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label>Semester</label>
            <select name="sem">
                <option value="">All</option>
                <?php foreach (['1st','2nd','3rd','4th','5th','6th','7th','8th'] as $s): ?>
                <option value="<?= $s ?>" <?= $semFilter === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label>Search</label>
            <input class="search-input" type="text" name="q" placeholder="Search questions..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Search</button>
        <?php if ($courseId || $yearFilter || $semFilter || $search): ?>
        <a href="question-bank.php" class="btn btn-outline">Clear</a>
        <?php endif; ?>
    </form>

    <div class="layout">
        <!-- Questions List -->
        <div>
            <?php if (empty($questions)): ?>
            <div class="empty-state">
                <div class="icon">📭</div>
                <div>No questions found.</div>
                <div style="margin-top:8px;font-size:13px;">Be the first to <a href="submit-question.php" style="color:var(--accent)">submit a question!</a></div>
            </div>
            <?php else: ?>
            <div style="color:var(--muted);font-size:13px;margin-bottom:16px;"><?= count($questions) ?> questions found</div>
            <?php foreach ($questions as $q): ?>
            <div class="q-card" onclick="window.location='question-detail.php?id=<?= $q['id'] ?>'">
                <div class="q-header">
                    <span class="q-course-badge"><?= htmlspecialchars($q['course_code']) ?></span>
                    <?php if ($q['exam_year']): ?>
                    <span class="q-year-badge"><?= $q['exam_year'] ?></span>
                    <?php endif; ?>
                    <?php if ($q['exam_semester']): ?>
                    <span class="q-year-badge"><?= htmlspecialchars($q['exam_semester']) ?> Sem</span>
                    <?php endif; ?>
                    <span class="q-type-badge"><?= ucfirst($q['question_type']) ?></span>
                    <?php if ($q['marks']): ?>
                    <span class="q-marks"><?= $q['marks'] ?> marks</span>
                    <?php endif; ?>
                </div>
                <div class="q-text"><?= htmlspecialchars(mb_substr($q['question_text'], 0, 200)) ?><?= strlen($q['question_text']) > 200 ? '...' : '' ?></div>
                <div class="q-footer">
                    <div class="q-stats">
                        <span>💬 <?= $q['answer_count'] ?> answer<?= $q['answer_count'] != 1 ? 's' : '' ?></span>
                        <span>👁 <?= $q['view_count'] ?> views</span>
                        <?php if ($q['topic']): ?>
                        <span>🏷 <?= htmlspecialchars($q['topic']) ?></span>
                        <?php endif; ?>
                    </div>
                    <button class="bookmark-btn <?= $q['bookmarked'] ? 'active' : '' ?>"
                        onclick="event.stopPropagation(); toggleBookmark(<?= $q['id'] ?>, this)"
                        title="Bookmark">
                        <?= $q['bookmarked'] ? '🔖' : '🏷' ?>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar Widgets -->
        <div>
            <div class="widget">
                <div class="widget-title">🔥 Hot Topics</div>
                <?php foreach ($hotTopics as $t): ?>
                <a href="?q=<?= urlencode($t['topic_name']) ?>" class="topic-tag">
                    <?= htmlspecialchars($t['topic_name']) ?>
                    <span class="topic-count"><?= $t['cnt'] ?></span>
                </a>
                <?php endforeach; ?>
                <?php if (empty($hotTopics)): ?>
                <div style="color:var(--muted);font-size:13px;">Topics will appear as questions are added.</div>
                <?php endif; ?>
            </div>

            <div class="widget">
                <div class="widget-title">📊 Browse by Year</div>
                <?php foreach ($years as $y): ?>
                <a href="?year=<?= $y ?>" class="topic-tag"><?= $y ?></a>
                <?php endforeach; ?>
                <?php if (empty($years)): ?>
                <div style="color:var(--muted);font-size:13px;">No questions yet.</div>
                <?php endif; ?>
            </div>

            <div class="widget" style="background:linear-gradient(135deg,rgba(34,211,238,.08),rgba(129,140,248,.08));border-color:rgba(34,211,238,.2);">
                <div class="widget-title">🤖 AI Compact Answers</div>
                <p style="font-size:13px;color:var(--muted);line-height:1.6;margin-bottom:14px;">Open any question to get an AI-generated compact answer — perfect for last-minute exam revision.</p>
                <a href="suggestions.php" class="btn btn-primary" style="width:100%;justify-content:center;">Get Exam Suggestions →</a>
            </div>

            <div class="widget">
                <div class="widget-title">📚 Courses</div>
                <?php
                $years_list = [1,2,3,4];
                foreach ($years_list as $yr):
                    $coursesByYear = array_filter($courses, fn($c) => $c['year'] == $yr);
                    if (!empty($coursesByYear)):
                ?>
                <div style="font-size:11px;color:var(--muted);margin-bottom:6px;margin-top:10px;text-transform:uppercase;letter-spacing:.5px;">Year <?= $yr ?></div>
                <?php foreach ($coursesByYear as $c): ?>
                <a href="?course=<?= $c['id'] ?>" class="topic-tag" style="display:block;margin:3px 0;">
                    <?= htmlspecialchars($c['code']) ?> — <?= htmlspecialchars(mb_substr($c['name'],0,30)) ?>
                </a>
                <?php endforeach; ?>
                <?php endif; endforeach; ?>
            </div>
        </div>
    </div>
</main>

<script>
function toggleBookmark(questionId, btn) {
    fetch('ajax/bookmark.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ question_id: questionId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.bookmarked) {
            btn.textContent = '🔖';
            btn.classList.add('active');
        } else {
            btn.textContent = '🏷';
            btn.classList.remove('active');
        }
    });
}
</script>
</body>
</html>
