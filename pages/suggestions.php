<?php
// pages/suggestions.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$user = currentUser();
$db   = getDB();

$courses = $db->query("SELECT * FROM courses ORDER BY year, semester, name")->fetchAll();
$selectedCourse = (int)($_GET['course'] ?? 0);
$courseData = null;
if ($selectedCourse) {
    $stmt = $db->prepare("SELECT * FROM courses WHERE id=?");
    $stmt->execute([$selectedCourse]);
    $courseData = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Exam Suggestions — EduSync MU</title>
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
.main{margin-left:240px;flex:1;padding:32px;max-width:900px;}
.page-title{font-family:'Syne',sans-serif;font-size:22px;font-weight:700;margin-bottom:6px;}
.page-sub{color:var(--muted);font-size:14px;margin-bottom:32px;}

.ai-hero{background:linear-gradient(135deg,rgba(34,211,238,.1),rgba(129,140,248,.1));border:1px solid rgba(34,211,238,.25);border-radius:20px;padding:32px;text-align:center;margin-bottom:32px;}
.ai-hero-icon{font-size:56px;margin-bottom:12px;}
.ai-hero h2{font-family:'Syne',sans-serif;font-size:22px;font-weight:800;margin-bottom:8px;}
.ai-hero p{color:var(--muted);font-size:15px;max-width:560px;margin:0 auto;}

.tool-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:32px;}
.tool-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:24px;}
.tool-icon{font-size:28px;margin-bottom:12px;}
.tool-title{font-family:'Syne',sans-serif;font-size:16px;font-weight:700;margin-bottom:6px;}
.tool-desc{font-size:13px;color:var(--muted);margin-bottom:16px;line-height:1.6;}

select,input,textarea{width:100%;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:12px 14px;color:var(--text);font-size:14px;font-family:inherit;outline:none;margin-bottom:12px;transition:border-color .2s;}
select:focus,input:focus,textarea:focus{border-color:var(--accent);}
select option{background:var(--card);}
label{font-size:12px;color:var(--muted);display:block;margin-bottom:6px;}
.btn{display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:600;font-family:'Syne',sans-serif;cursor:pointer;text-decoration:none;border:none;transition:all .2s;width:100%;justify-content:center;}
.btn-primary{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#0a0e1a;}
.btn-primary:hover{opacity:.9;}
.btn-primary:disabled{opacity:.5;cursor:not-allowed;}

.result-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:24px;margin-top:16px;display:none;}
.result-title{font-family:'Syne',sans-serif;font-size:15px;font-weight:700;margin-bottom:14px;color:var(--accent);}
.result-content{font-size:14px;line-height:1.9;white-space:pre-wrap;}
.loading{display:none;align-items:center;gap:10px;color:var(--accent);font-size:14px;margin-top:12px;}
.spinner{width:18px;height:18px;border:2px solid rgba(34,211,238,.3);border-top-color:var(--accent);border-radius:50%;animation:spin .6s linear infinite;}
@keyframes spin{to{transform:rotate(360deg)}}
@media(max-width:700px){.tool-grid{grid-template-columns:1fr;}.sidebar{display:none;}.main{margin-left:0;}}
</style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-logo">EduSync</div>
    <a href="dashboard.php" class="nav-item"><span class="icon">🏠</span> Dashboard</a>
    <a href="tasks.php" class="nav-item"><span class="icon">✅</span> Tasks</a>
    <a href="ai.php" class="nav-item"><span class="icon">🤖</span> AI Assistant</a>
    <a href="flashcards.php" class="nav-item"><span class="icon">🃏</span> Flashcards</a>
    <a href="question-bank.php" class="nav-item"><span class="icon">📖</span> Question Bank</a>
    <a href="suggestions.php" class="nav-item active"><span class="icon">💡</span> Exam Suggestions</a>
</aside>

<main class="main">
    <div class="page-title">💡 AI Exam Suggestions</div>
    <div class="page-sub">AI-powered exam predictions & compact answers for MU Sylhet SE Department</div>

    <div class="ai-hero">
        <div class="ai-hero-icon">🤖</div>
        <h2>Your AI Exam Coach</h2>
        <p>Powered by Claude AI. Analyze past questions, predict exam topics, and get compact exam-ready answers instantly.</p>
    </div>

    <div class="tool-grid">

        <!-- Tool 1: Exam Topic Predictions -->
        <div class="tool-card">
            <div class="tool-icon">🎯</div>
            <div class="tool-title">Exam Topic Predictions</div>
            <div class="tool-desc">AI analyzes past questions to predict the most likely topics for your upcoming exam.</div>
            <label>Select Course</label>
            <select id="predCourse">
                <option value="">Choose a course...</option>
                <?php foreach ($courses as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $selectedCourse == $c['id'] ? 'selected' : '' ?>
                    data-name="<?= htmlspecialchars($c['name']) ?>" data-code="<?= htmlspecialchars($c['code']) ?>">
                    <?= htmlspecialchars($c['code']) ?> — <?= htmlspecialchars($c['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <label>Current Semester</label>
            <select id="predSem">
                <?php for ($i=1;$i<=8;$i++): ?>
                <option value="<?= $i ?>" <?= $user['semester'] == $i ? 'selected' : '' ?>>Semester <?= $i ?></option>
                <?php endfor; ?>
            </select>
            <button class="btn btn-primary" id="predBtn" onclick="runPrediction()">🎯 Predict Exam Topics</button>
            <div class="loading" id="predLoading"><div class="spinner"></div> Analyzing past questions...</div>
            <div class="result-card" id="predResult">
                <div class="result-title">🎯 Predicted Exam Topics</div>
                <div class="result-content" id="predContent"></div>
            </div>
        </div>

        <!-- Tool 2: Compact Answer Generator -->
        <div class="tool-card">
            <div class="tool-icon">📋</div>
            <div class="tool-title">Compact Answer Generator</div>
            <div class="tool-desc">Paste a question and full answer → AI rewrites it as a concise, exam-ready compact answer.</div>
            <label>Question</label>
            <textarea id="compactQ" rows="3" placeholder="Paste or type the exam question..."></textarea>
            <label>Full Answer (optional)</label>
            <textarea id="compactA" rows="3" placeholder="Paste your full answer for AI to compact..."></textarea>
            <button class="btn btn-primary" id="compactBtn" onclick="runCompact()">✨ Generate Compact Answer</button>
            <div class="loading" id="compactLoading"><div class="spinner"></div> Generating compact answer...</div>
            <div class="result-card" id="compactResult">
                <div class="result-title">📋 Compact Exam Answer</div>
                <div class="result-content" id="compactContent"></div>
            </div>
        </div>

        <!-- Tool 3: Study Plan Generator -->
        <div class="tool-card">
            <div class="tool-icon">📅</div>
            <div class="tool-title">AI Study Plan Generator</div>
            <div class="tool-desc">Enter your exam date and subjects → AI builds a personalized day-by-day study plan.</div>
            <label>Subjects to Study (comma-separated)</label>
            <input type="text" id="planSubjects" placeholder="e.g. DSA, OOP, DBMS, OS">
            <label>Days Until Exam</label>
            <input type="number" id="planDays" placeholder="e.g. 14" min="1" max="90">
            <label>Daily Study Hours Available</label>
            <input type="number" id="planHours" placeholder="e.g. 4" min="1" max="12">
            <button class="btn btn-primary" id="planBtn" onclick="runPlan()">📅 Generate Study Plan</button>
            <div class="loading" id="planLoading"><div class="spinner"></div> Building your study plan...</div>
            <div class="result-card" id="planResult">
                <div class="result-title">📅 Your Personalized Study Plan</div>
                <div class="result-content" id="planContent"></div>
            </div>
        </div>

        <!-- Tool 4: Quick Revision Sheet -->
        <div class="tool-card">
            <div class="tool-icon">⚡</div>
            <div class="tool-title">Quick Revision Sheet</div>
            <div class="tool-desc">Select a course → AI generates a one-page revision sheet of the most important concepts.</div>
            <label>Select Course</label>
            <select id="revCourse">
                <option value="">Choose a course...</option>
                <?php foreach ($courses as $c): ?>
                <option value="<?= htmlspecialchars($c['name']) ?>" data-code="<?= htmlspecialchars($c['code']) ?>">
                    <?= htmlspecialchars($c['code']) ?> — <?= htmlspecialchars($c['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <label>Focus Area (optional)</label>
            <input type="text" id="revFocus" placeholder="e.g. Trees, Sorting, SQL joins...">
            <button class="btn btn-primary" id="revBtn" onclick="runRevision()">⚡ Generate Revision Sheet</button>
            <div class="loading" id="revLoading"><div class="spinner"></div> Creating revision sheet...</div>
            <div class="result-card" id="revResult">
                <div class="result-title">⚡ Quick Revision Sheet</div>
                <div class="result-content" id="revContent"></div>
            </div>
        </div>
    </div>
</main>

<script>
async function callAI(prompt, loadingId, btnId, resultId, contentId) {
    const loading = document.getElementById(loadingId);
    const btn = document.getElementById(btnId);
    const result = document.getElementById(resultId);
    const content = document.getElementById(contentId);

    loading.style.display = 'flex';
    btn.disabled = true;
    result.style.display = 'none';

    try {
        const resp = await fetch('../ajax/ai-suggest.php', {

            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prompt })
        });
        const data = await resp.json();
        content.textContent = data.text || 'Could not generate. Please try again.';
        result.style.display = 'block';
    } catch(e) {
        content.textContent = 'Request failed. Please check your API key in config/database.php.';
        result.style.display = 'block';
    }

    loading.style.display = 'none';
    btn.disabled = false;
}

function runPrediction() {
    const sel = document.getElementById('predCourse');
    const sem = document.getElementById('predSem').value;
    if (!sel.value) { alert('Please select a course.'); return; }
    const name = sel.options[sel.selectedIndex].dataset.name;
    const code = sel.options[sel.selectedIndex].dataset.code;
    const prompt = `You are an expert on Metropolitan University Sylhet, Bangladesh, Software Engineering department exam patterns.

Course: ${code} — ${name}
Student's current semester: ${sem}
University: Metropolitan University Sylhet

Based on typical university exam patterns for this course in a software engineering program:
1. List the TOP 8 most likely exam topics (with brief explanation of why each is important)
2. Suggest 5 probable exam questions with suggested compact answer outlines
3. List 3 topics to prioritize for last-minute revision
4. Give one study tip specific to this course.

Format clearly with numbered sections and bullet points.`;
    callAI(prompt, 'predLoading', 'predBtn', 'predResult', 'predContent');
}

function runCompact() {
    const q = document.getElementById('compactQ').value.trim();
    const a = document.getElementById('compactA').value.trim();
    if (!q) { alert('Please enter the question.'); return; }
    const prompt = `You are an exam preparation assistant for Metropolitan University Sylhet, Software Engineering department.

Question: ${q}
${a ? 'Full Answer: ' + a : 'No answer provided — generate your own based on the question.'}

Rewrite this as a COMPACT, exam-ready answer in maximum 10 lines.
- Use bullet points or numbered steps where helpful
- Focus on key points an examiner wants to see
- Include definitions, examples, or formulas if relevant
- Be precise and concise
- Do NOT include unnecessary padding or repetition

Start with "📋 Compact Answer:" on the first line.`;
    callAI(prompt, 'compactLoading', 'compactBtn', 'compactResult', 'compactContent');
}

function runPlan() {
    const subjects = document.getElementById('planSubjects').value.trim();
    const days = document.getElementById('planDays').value;
    const hours = document.getElementById('planHours').value;
    if (!subjects || !days || !hours) { alert('Please fill in all fields.'); return; }
    const prompt = `Create a detailed day-by-day study plan for a Software Engineering student at Metropolitan University Sylhet, Bangladesh.

Subjects: ${subjects}
Days available: ${days}
Daily study hours: ${hours}

Requirements:
- Distribute subjects evenly but give more time to harder ones
- Include revision days near the end
- Specify what exactly to study each day (topics, not just subject names)
- Include short breaks and self-test reminders
- On the last 2 days, focus only on revision and practice questions

Format as:
Day 1 (Date pattern): [Subject] - [Specific topics]
Day 2: ...
etc.

End with "📌 Key Tips for Success:"`;
    callAI(prompt, 'planLoading', 'planBtn', 'planResult', 'planContent');
}

function runRevision() {
    const course = document.getElementById('revCourse').value;
    const focus = document.getElementById('revFocus').value.trim();
    if (!course) { alert('Please select a course.'); return; }
    const prompt = `Create a one-page quick revision sheet for a Software Engineering student at Metropolitan University Sylhet preparing for their ${course} exam.

${focus ? 'Focus area: ' + focus : 'Cover all major topics.'}

Format the revision sheet with:
1. 📌 Key Definitions (5-8 most important terms)
2. 🧠 Core Concepts to Remember (bullet points)
3. 📐 Important Formulas / Algorithms (if applicable)
4. ⚡ Quick Facts (common exam points)
5. 🚨 Common Mistakes to Avoid

Keep it concise — this should fit on one page. Use simple language.`;
    callAI(prompt, 'revLoading', 'revBtn', 'revResult', 'revContent');
}
</script>
</body>
</html>
