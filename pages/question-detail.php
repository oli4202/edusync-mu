<?php
// pages/question-detail.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$user = currentUser();
$db   = getDB();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: question-bank.php'); exit(); }

// Get question
$stmt = $db->prepare("SELECT q.*, c.name AS course_name, c.code AS course_code, u.name AS submitted_by_name
    FROM questions q JOIN courses c ON q.course_id=c.id LEFT JOIN users u ON q.submitted_by=u.id
    WHERE q.id=? AND q.is_approved=1");
$stmt->execute([$id]);
$question = $stmt->fetch();
if (!$question) { header('Location: question-bank.php'); exit(); }

// Update view count
$db->prepare("UPDATE questions SET view_count=view_count+1 WHERE id=?")->execute([$id]);

// Get answers
$aStmt = $db->prepare("SELECT a.*, u.name AS author_name FROM answers a LEFT JOIN users u ON a.user_id=u.id WHERE a.question_id=? AND a.is_approved=1 ORDER BY a.upvotes DESC, a.created_at ASC");
$aStmt->execute([$id]);
$answers = $aStmt->fetchAll();

// Is bookmarked?
$bStmt = $db->prepare("SELECT id FROM question_bookmarks WHERE user_id=? AND question_id=?");
$bStmt->execute([$user['id'], $id]);
$isBookmarked = (bool)$bStmt->fetch();

// Related questions
$related = $db->prepare("SELECT q.id, q.question_text FROM questions q WHERE q.course_id=? AND q.id!=? AND q.is_approved=1 LIMIT 4");
$related->execute([$question['course_id'], $id]);
$relatedQs = $related->fetchAll();

// Handle answer submission
$submitError = $submitSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_answer'])) {
    $answerText = trim($_POST['answer_text'] ?? '');
    $steps = trim($_POST['solution_steps'] ?? '');
    if (strlen($answerText) < 20) {
        $submitError = 'Answer must be at least 20 characters.';
    } else {
        $db->prepare("INSERT INTO answers (question_id, user_id, answer_text, solution_steps) VALUES (?,?,?,?)")
           ->execute([$id, $user['id'], $answerText, $steps]);
        $submitSuccess = 'Answer submitted! It will appear after admin approval.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars(mb_substr($question['question_text'],0,60)) ?>... — EduSync MU</title>
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
.main{margin-left:240px;flex:1;padding:32px;max-width:1100px;}
.breadcrumb{font-size:13px;color:var(--muted);margin-bottom:20px;}
.breadcrumb a{color:var(--accent);text-decoration:none;}
.layout{display:grid;grid-template-columns:1fr 300px;gap:24px;}
.q-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:28px;margin-bottom:20px;}
.q-badges{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;}
.badge{font-size:11px;padding:3px 10px;border-radius:20px;}
.badge-cyan{background:rgba(34,211,238,.1);border:1px solid rgba(34,211,238,.2);color:var(--accent);}
.badge-purple{background:rgba(129,140,248,.1);border:1px solid rgba(129,140,248,.2);color:var(--accent2);}
.badge-green{background:rgba(52,211,153,.1);color:var(--accent3);}
.badge-yellow{background:rgba(251,191,36,.1);color:var(--warn);}
.q-text{font-size:18px;font-weight:500;line-height:1.7;margin-bottom:16px;}
.q-meta{font-size:12px;color:var(--muted);display:flex;gap:20px;}
.action-bar{display:flex;gap:10px;margin-top:20px;flex-wrap:wrap;}
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;font-family:'Syne',sans-serif;cursor:pointer;text-decoration:none;border:none;transition:all .2s;}
.btn-primary{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#0a0e1a;}
.btn-outline{background:transparent;border:1px solid var(--border);color:var(--text);}
.btn-outline:hover{border-color:var(--accent);color:var(--accent);}
.btn-bookmark{background:transparent;border:1px solid var(--border);color:var(--warn);}
.btn-sm{padding:6px 14px;font-size:12px;}

/* AI Compact Answer */
.ai-panel{background:linear-gradient(135deg,rgba(34,211,238,.06),rgba(129,140,248,.06));border:1px solid rgba(34,211,238,.2);border-radius:14px;padding:22px;margin-bottom:20px;}
.ai-panel-title{font-family:'Syne',sans-serif;font-size:15px;font-weight:700;margin-bottom:6px;display:flex;align-items:center;gap:8px;}
.ai-panel-sub{font-size:13px;color:var(--muted);margin-bottom:16px;}
.ai-result{background:rgba(0,0,0,.2);border:1px solid var(--border);border-radius:10px;padding:16px;font-size:14px;line-height:1.8;white-space:pre-wrap;margin-top:14px;display:none;}
.ai-loading{display:none;color:var(--accent);font-size:13px;margin-top:12px;align-items:center;gap:8px;}
.spinner{width:16px;height:16px;border:2px solid rgba(34,211,238,.3);border-top-color:var(--accent);border-radius:50%;animation:spin .6s linear infinite;}
@keyframes spin{to{transform:rotate(360deg)}}

/* Answers */
.answer-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:22px;margin-bottom:14px;}
.answer-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
.author{display:flex;align-items:center;gap:10px;}
.author-avatar{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--accent2),var(--accent));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:#0a0e1a;}
.author-name{font-size:14px;font-weight:500;}
.answer-date{font-size:12px;color:var(--muted);}
.answer-text{font-size:14px;line-height:1.8;white-space:pre-wrap;}
.compact-section{background:rgba(52,211,153,.06);border:1px solid rgba(52,211,153,.2);border-radius:10px;padding:14px;margin-top:14px;}
.compact-label{font-size:11px;color:var(--accent3);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;font-weight:600;}
.steps-section{background:rgba(251,191,36,.06);border:1px solid rgba(251,191,36,.2);border-radius:10px;padding:14px;margin-top:14px;}
.steps-label{font-size:11px;color:var(--warn);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;font-weight:600;}
.upvote-btn{background:none;border:1px solid var(--border);border-radius:8px;color:var(--muted);cursor:pointer;padding:6px 12px;font-size:12px;transition:all .2s;}
.upvote-btn:hover{border-color:var(--accent3);color:var(--accent3);}

/* Submit form */
.submit-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:22px;}
.submit-title{font-family:'Syne',sans-serif;font-size:15px;font-weight:700;margin-bottom:16px;}
textarea{width:100%;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:12px;color:var(--text);font-size:14px;font-family:inherit;outline:none;resize:vertical;transition:border-color .2s;}
textarea:focus{border-color:var(--accent);}
label{font-size:12px;color:var(--muted);display:block;margin-bottom:6px;margin-top:14px;}
.success-box{background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.2);color:var(--accent3);border-radius:10px;padding:12px;font-size:14px;margin-bottom:16px;}
.error-box{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:#f87171;border-radius:10px;padding:12px;font-size:14px;margin-bottom:16px;}

/* Right sidebar */
.widget{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:18px;margin-bottom:16px;}
.widget-title{font-family:'Syne',sans-serif;font-size:13px;font-weight:700;margin-bottom:12px;}
.related-item{padding:10px 0;border-bottom:1px solid var(--border);font-size:13px;line-height:1.5;}
.related-item:last-child{border-bottom:none;}
.related-item a{color:var(--text);text-decoration:none;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.related-item a:hover{color:var(--accent);}
@media(max-width:900px){.layout{grid-template-columns:1fr;}.sidebar{display:none;}.main{margin-left:0;}}
</style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-logo">EduSync</div>
    <a href="dashboard.php" class="nav-item"><span class="icon">🏠</span> Dashboard</a>
    <a href="tasks.php" class="nav-item"><span class="icon">✅</span> Tasks</a>
    <a href="analytics.php" class="nav-item"><span class="icon">📊</span> Analytics</a>
    <a href="ai.php" class="nav-item"><span class="icon">🤖</span> AI Assistant</a>
    <a href="flashcards.php" class="nav-item"><span class="icon">🃏</span> Flashcards</a>
    <a href="question-bank.php" class="nav-item active"><span class="icon">📖</span> Question Bank</a>
    <a href="suggestions.php" class="nav-item"><span class="icon">💡</span> Exam Suggestions</a>
</aside>

<main class="main">
    <div class="breadcrumb">
        <a href="question-bank.php">← Question Bank</a> /
        <a href="question-bank.php?course=<?= $question['course_id'] ?>"><?= htmlspecialchars($question['course_code']) ?></a>
    </div>

    <div class="layout">
        <div>
            <!-- Question Card -->
            <div class="q-card">
                <div class="q-badges">
                    <span class="badge badge-cyan"><?= htmlspecialchars($question['course_code']) ?> — <?= htmlspecialchars($question['course_name']) ?></span>
                    <?php if ($question['exam_year']): ?>
                    <span class="badge badge-purple">Year <?= $question['exam_year'] ?></span>
                    <?php endif; ?>
                    <?php if ($question['exam_semester']): ?>
                    <span class="badge badge-purple"><?= htmlspecialchars($question['exam_semester']) ?> Semester</span>
                    <?php endif; ?>
                    <span class="badge badge-green"><?= ucfirst($question['question_type']) ?></span>
                    <?php if ($question['marks']): ?>
                    <span class="badge badge-yellow"><?= $question['marks'] ?> marks</span>
                    <?php endif; ?>
                </div>
                <div class="q-text"><?= nl2br(htmlspecialchars($question['question_text'])) ?></div>
                
                <?php if (!empty($question['image_path'])): ?>
                    <?php if (str_ends_with(strtolower($question['image_path']), '.pdf')): ?>
                        <div style="margin:16px 0;">
                            <a href="../<?= htmlspecialchars($question['image_path']) ?>" target="_blank" class="btn btn-outline" style="color:var(--accent);"><span style="font-size:18px">📄</span> View Attached PDF/Document</a>
                        </div>
                    <?php else: ?>
                        <div style="margin:16px 0; border:1px solid var(--border); border-radius:10px; overflow:hidden;">
                            <img src="../<?= htmlspecialchars($question['image_path']) ?>" alt="Question Attachments" style="max-width:100%; display:block;">
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($question['topic']): ?>
                <div style="font-size:12px;color:var(--muted);margin-bottom:12px;">🏷 Topic: <?= htmlspecialchars($question['topic']) ?></div>
                <?php endif; ?>
                <div class="q-meta">
                    <span>👁 <?= $question['view_count'] + 1 ?> views</span>
                    <span>💬 <?= count($answers) ?> answers</span>
                    <?php if ($question['submitted_by_name']): ?>
                    <span>✍️ <?= htmlspecialchars($question['submitted_by_name']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="action-bar">
                    <button class="btn <?= $isBookmarked ? 'btn-bookmark' : 'btn-outline' ?>" id="bookmarkBtn" onclick="toggleBookmark(<?= $id ?>)">
                        <?= $isBookmarked ? '🔖 Bookmarked' : '🏷 Bookmark' ?>
                    </button>
                    <a href="suggestions.php?course=<?= $question['course_id'] ?>" class="btn btn-outline">💡 Exam Suggestions for this Course</a>
                </div>
            </div>

            <!-- AI Compact Answer Panel -->
            <div class="ai-panel">
                <div class="ai-panel-title">🤖 AI Compact Answer Generator</div>
                <div class="ai-panel-sub">Get an exam-ready compact answer — max 10 lines, perfect for last-minute revision.</div>
                <button class="btn btn-primary" onclick="generateCompact()">✨ Generate Compact Answer</button>
                <div class="ai-loading" id="aiLoading"><div class="spinner"></div> AI is writing your compact answer...</div>
                <div class="ai-result" id="aiResult"></div>
            </div>

            <!-- Answers -->
            <div style="font-family:'Syne',sans-serif;font-size:16px;font-weight:700;margin-bottom:16px;">
                💬 <?= count($answers) ?> Answer<?= count($answers) != 1 ? 's' : '' ?>
            </div>

            <?php if (empty($answers)): ?>
            <div style="color:var(--muted);font-size:14px;background:var(--card);border:1px solid var(--border);border-radius:14px;padding:28px;text-align:center;margin-bottom:20px;">
                <div style="font-size:32px;margin-bottom:10px;">📝</div>
                No answers yet. Be the first to contribute!
            </div>
            <?php else: ?>
            <?php foreach ($answers as $a): ?>
            <div class="answer-card">
                <div class="answer-header">
                    <div class="author">
                        <div class="author-avatar"><?= strtoupper(substr($a['author_name'] ?? 'A',0,1)) ?></div>
                        <div>
                            <div class="author-name"><?= htmlspecialchars($a['author_name'] ?? 'Anonymous') ?></div>
                            <div class="answer-date"><?= timeAgo($a['created_at']) ?></div>
                        </div>
                    </div>
                    <button class="upvote-btn" onclick="upvote(<?= $a['id'] ?>, this)">
                        👍 <span class="upvote-count"><?= $a['upvotes'] ?></span>
                    </button>
                </div>
                <div class="answer-text"><?= htmlspecialchars($a['answer_text']) ?></div>

                <?php if ($a['compact_answer']): ?>
                <div class="compact-section">
                    <div class="compact-label">📋 Compact Exam Answer</div>
                    <div style="font-size:14px;line-height:1.8;white-space:pre-wrap;"><?= htmlspecialchars($a['compact_answer']) ?></div>
                    <?php if ($a['ai_compact']): ?>
                    <div style="font-size:11px;color:var(--accent);margin-top:8px;">✨ AI-generated</div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ($a['solution_steps']): ?>
                <div class="steps-section">
                    <div class="steps-label">🔢 Step-by-Step Solution</div>
                    <div style="font-size:14px;line-height:1.8;white-space:pre-wrap;"><?= htmlspecialchars($a['solution_steps']) ?></div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>

            <!-- Submit Answer Form -->
            <div class="submit-card">
                <div class="submit-title">✍️ Submit Your Answer</div>
                <?php if ($submitSuccess): ?>
                <div class="success-box">✅ <?= $submitSuccess ?></div>
                <?php elseif ($submitError): ?>
                <div class="error-box">⚠ <?= $submitError ?></div>
                <?php endif; ?>
                <form method="POST">
                    <label>Your Answer *</label>
                    <textarea name="answer_text" rows="6" placeholder="Write your full answer here..." required><?= htmlspecialchars($_POST['answer_text'] ?? '') ?></textarea>
                    <label>Step-by-Step Solution (optional — helpful for math/algorithm questions)</label>
                    <textarea name="solution_steps" rows="4" placeholder="Step 1: ...&#10;Step 2: ...&#10;Step 3: ..."><?= htmlspecialchars($_POST['solution_steps'] ?? '') ?></textarea>
                    <button type="submit" name="submit_answer" class="btn btn-primary" style="margin-top:14px;">Submit Answer →</button>
                </form>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div>
            <div class="widget" style="background:linear-gradient(135deg,rgba(34,211,238,.06),rgba(129,140,248,.06));border-color:rgba(34,211,238,.2);">
                <div class="widget-title">💡 AI Exam Suggestions</div>
                <p style="font-size:13px;color:var(--muted);margin-bottom:14px;line-height:1.6;">Want to know what topics might come up in your next exam for this course?</p>
                <a href="suggestions.php?course=<?= $question['course_id'] ?>" class="btn btn-primary btn-sm" style="width:100%;justify-content:center;">Get Predictions →</a>
            </div>

            <?php if (!empty($relatedQs)): ?>
            <div class="widget">
                <div class="widget-title">📖 Related Questions</div>
                <?php foreach ($relatedQs as $rq): ?>
                <div class="related-item">
                    <a href="question-detail.php?id=<?= $rq['id'] ?>"><?= htmlspecialchars($rq['question_text']) ?></a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="widget">
                <div class="widget-title">⚡ Quick Actions</div>
                <a href="flashcards.php?new=1&q=<?= urlencode(mb_substr($question['question_text'],0,100)) ?>" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;margin-bottom:8px;">🃏 Save as Flashcard</a>
                <a href="question-bank.php?course=<?= $question['course_id'] ?>" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;">📚 More from this Course</a>
            </div>
        </div>
    </div>
</main>

<script>
const questionText = <?= json_encode($question['question_text']) ?>;
const existingAnswers = <?= json_encode(implode('\n\n', array_column($answers, 'answer_text'))) ?>;

function generateCompact() {
    document.getElementById('aiLoading').style.display = 'flex';
    document.getElementById('aiResult').style.display = 'none';
    document.querySelector('.ai-panel .btn-primary').disabled = true;

    fetch('../ajax/ai-compact.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            question: questionText,
            answers: existingAnswers
        })
    })
    .then(async r => {
        if (!r.ok) {
            throw new Error('Request failed with status ' + r.status);
        }
        return r.json();
    })
    .then(data => {
        document.getElementById('aiLoading').style.display = 'none';
        document.querySelector('.ai-panel .btn-primary').disabled = false;
        const result = document.getElementById('aiResult');
        result.style.display = 'block';
        result.textContent = data.text || 'Could not generate answer. Please try again.';
    })
    .catch(() => {
        document.getElementById('aiLoading').style.display = 'none';
        document.querySelector('.ai-panel .btn-primary').disabled = false;
        alert('Compact answer request failed. Please try again.');
    });
}

function toggleBookmark(qId) {
    fetch('ajax/bookmark.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ question_id: qId })
    })
    .then(r => r.json())
    .then(data => {
        const btn = document.getElementById('bookmarkBtn');
        btn.textContent = data.bookmarked ? '🔖 Bookmarked' : '🏷 Bookmark';
    });
}

function upvote(answerId, btn) {
    fetch('ajax/upvote.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ answer_id: answerId })
    })
    .then(r => r.json())
    .then(data => {
        btn.querySelector('.upvote-count').textContent = data.upvotes;
    });
}
</script>
</body>
</html>
