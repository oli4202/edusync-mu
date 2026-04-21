<?php
// pages/submit-question.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$user = currentUser();
$db   = getDB();

$courses = $db->query("SELECT * FROM courses ORDER BY year, semester, name")->fetchAll();
$courseMeta = [];
foreach ($courses as $course) {
    $courseMeta[] = [
        'id' => (int) $course['id'],
        'name' => $course['name'],
        'code' => $course['code'] ?? '',
        'year' => isset($course['year']) ? (int) $course['year'] : 0,
        'semester' => isset($course['semester']) ? (int) $course['semester'] : 0,
    ];
}
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId    = (int)($_POST['course_id'] ?? 0);
    $questionText = trim($_POST['question_text'] ?? '');
    $qType       = clean($_POST['question_type'] ?? 'broad');
    $examYear    = (int)($_POST['exam_year'] ?? 0);
    $examSem     = clean($_POST['exam_semester'] ?? '');
    $marks       = (int)($_POST['marks'] ?? 10);
    $topic       = clean($_POST['topic'] ?? '');
    $answerText  = trim($_POST['answer_text'] ?? '');
    $steps       = trim($_POST['solution_steps'] ?? '');

    if (!$courseId || !$questionText) {
        $error = 'Please fill in the required fields.';
    } elseif (strlen($questionText) < 10) {
        $error = 'Question must be at least 10 characters.';
    } else {
        $db->prepare("INSERT INTO questions (course_id, submitted_by, question_text, question_type, exam_year, exam_semester, marks, topic) VALUES (?,?,?,?,?,?,?,?)")
           ->execute([$courseId, $user['id'], $questionText, $qType, $examYear ?: null, $examSem ?: null, $marks, $topic]);
        $qId = $db->lastInsertId();

        // Handle File Upload
        if (isset($_FILES['question_file']) && $_FILES['question_file']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['question_file']['tmp_name'];
            $fileName = basename($_FILES['question_file']['name']);
            $uploadDir = __DIR__ . '/../assets/uploads/questions/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $newName = "q_{$qId}_" . time() . ".$ext";
            $destPath = $uploadDir . $newName;
            
            if (move_uploaded_file($tmpName, $destPath)) {
                $imagePath = 'assets/uploads/questions/' . $newName;
                $db->prepare("UPDATE questions SET image_path=? WHERE id=?")->execute([$imagePath, $qId]);
            }
        }

        // Save topic tag
        if ($topic) {
            $db->prepare("INSERT INTO question_topics (question_id, topic_name) VALUES (?,?)")->execute([$qId, $topic]);
        }

        // If answer also submitted
        if (strlen($answerText) >= 20) {
            $db->prepare("INSERT INTO answers (question_id, user_id, answer_text, solution_steps) VALUES (?,?,?,?)")
               ->execute([$qId, $user['id'], $answerText, $steps]);
        }

        $success = 'Question submitted successfully! It will appear after admin approval. Thank you for contributing! 🎉';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Submit Question — EduSync MU</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root{--bg:#0a0e1a;--card:#111827;--border:#1e2d45;--accent:#22d3ee;--accent2:#818cf8;--text:#e2e8f0;--muted:#64748b;}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;padding:32px;display:flex;justify-content:center;}
.container{width:100%;max-width:760px;}
.back{color:var(--accent);text-decoration:none;font-size:14px;display:inline-block;margin-bottom:20px;}
.page-title{font-family:'Syne',sans-serif;font-size:22px;font-weight:700;margin-bottom:6px;}
.page-sub{color:var(--muted);font-size:14px;margin-bottom:28px;}
.card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:28px;margin-bottom:20px;}
.card-title{font-family:'Syne',sans-serif;font-size:15px;font-weight:700;margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid var(--border);}
.row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;}
label{font-size:12px;color:var(--muted);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:.4px;}
input,select,textarea{width:100%;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:12px 14px;color:var(--text);font-size:14px;font-family:inherit;outline:none;margin-bottom:16px;transition:border-color .2s;}
input:focus,select:focus,textarea:focus{border-color:var(--accent);}
select option{background:var(--card);}
.btn{display:inline-flex;align-items:center;gap:6px;padding:12px 24px;border-radius:10px;font-size:14px;font-weight:700;font-family:'Syne',sans-serif;cursor:pointer;text-decoration:none;border:none;transition:all .2s;}
.btn-primary{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#0a0e1a;}
.success-box{background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.2);color:#34d399;border-radius:10px;padding:16px;font-size:14px;margin-bottom:20px;line-height:1.6;}
.error-box{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:#f87171;border-radius:10px;padding:12px;font-size:14px;margin-bottom:20px;}
.tip{background:rgba(34,211,238,.06);border:1px solid rgba(34,211,238,.15);border-radius:10px;padding:14px;font-size:13px;color:var(--muted);margin-bottom:20px;line-height:1.6;}
.hint-text{margin:-10px 0 16px;font-size:12px;line-height:1.6;color:var(--muted);}
.note-box{padding:12px 14px;border:1px dashed rgba(34,211,238,.25);border-radius:10px;background:rgba(15,23,42,.55);font-size:12px;color:var(--muted);margin-bottom:16px;line-height:1.6;}
@media(max-width:760px){.row-3{grid-template-columns:1fr;}}
@media(max-width:600px){.row{grid-template-columns:1fr;}}
</style>
</head>
<body>
<div class="container">
    <a href="question-bank.php" class="back">← Back to Question Bank</a>
    <div class="page-title">➕ Submit a Question</div>
    <div class="page-sub">Contribute to the MU Sylhet SE Department question bank. Your submission will be reviewed by an admin.</div>

    <?php if ($success): ?>
    <div class="success-box">✅ <?= $success ?> <a href="question-bank.php" style="color:var(--accent)">View Question Bank →</a></div>
    <?php elseif ($error): ?>
    <div class="error-box">⚠ <?= $error ?></div>
    <?php endif; ?>

    <div class="tip">💡 <strong>Tip:</strong> Add a compact answer along with your question to help your fellow students! The more complete your submission, the faster it gets approved.</div>

    <div class="note-box">MU theory note: there is no midterm here. Internal usually totals 60 marks before final through attendance, class tests, assignment or quiz, and project work. Final theory commonly has 6 questions with students answering any 4, and each main question is 10 marks with splits like 4+4+2, 5+5, 6+4, 2+3+5, or 3+3+4.</div>

    <form method="POST" enctype="multipart/form-data">
        <div class="card">
            <div class="card-title">📖 Question Details</div>
            <label>Course *</label>
            <select name="course_id" id="courseSelect" required>
                <option value="">Select the course this question is from...</option>
                <?php foreach ($courses as $c): ?>
                <optgroup label="Year <?= $c['year'] ?> — Semester <?= $c['semester'] ?>">
                <option value="<?= $c['id'] ?>" <?= (($_POST['course_id'] ?? 0) == $c['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['code']) ?> — <?= htmlspecialchars($c['name']) ?>
                </option>
                </optgroup>
                <?php endforeach; ?>
            </select>

            <div class="row">
                <div>
                    <label>Exam Year</label>
                    <input type="number" name="exam_year" placeholder="e.g. 2023" min="2000" max="2030" value="<?= htmlspecialchars($_POST['exam_year'] ?? '') ?>">
                </div>
                <div>
                    <label>Exam Semester</label>
                    <select name="exam_semester">
                        <option value="">Select semester</option>
                        <?php foreach (['1.1','1.2','1.3','2.1','2.2','2.3','3.1','3.2','3.3','4.1','4.2','4.3'] as $s): ?>
                        <option value="<?= $s ?>" <?= (($_POST['exam_semester'] ?? '') === $s) ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row-3">
                <div>
                    <label>Assessment Type</label>
                    <select name="assessment_type" id="assessmentType">
                        <?php foreach (['Class Test', 'Final Question', 'Assignment / Quiz', 'Project', 'Viva', 'Attendance'] as $assessment): ?>
                        <option value="<?= htmlspecialchars($assessment) ?>" <?= (($_POST['assessment_type'] ?? 'Class Test') === $assessment) ? 'selected' : '' ?>><?= htmlspecialchars($assessment) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Question Type</label>
                    <select name="question_type" id="questionType">
                        <option value="broad" <?= (($_POST['question_type'] ?? 'broad') === 'broad') ? 'selected' : '' ?>>Broad</option>
                        <option value="short" <?= (($_POST['question_type'] ?? '') === 'short') ? 'selected' : '' ?>>Short</option>
                        <option value="problem" <?= (($_POST['question_type'] ?? '') === 'problem') ? 'selected' : '' ?>>Problem</option>
                        <option value="mcq" <?= (($_POST['question_type'] ?? '') === 'mcq') ? 'selected' : '' ?>>MCQ</option>
                    </select>
                </div>
                <div>
                    <label>Marks</label>
                    <select name="marks" id="marksSelect"></select>
                </div>
            </div>

            <div class="note-box" id="assessmentNote">Choose an assessment type to get the right format and marks pattern.</div>
            <div class="hint-text" id="questionTypeHint">Class tests, finals, assignment or quiz, project, viva, and attendance each show different question-type suggestions.</div>

            <label>Topic / Chapter</label>
            <input type="text" name="topic" id="topicInput" list="topicSuggestions" placeholder="Select a course to see relevant topics..." value="<?= htmlspecialchars($_POST['topic'] ?? '') ?>">
            <datalist id="topicSuggestions"></datalist>
            <div class="hint-text" id="topicHint">Choose a subject to get 5 to 7 relevant topics.</div>

            <label>Question Text *</label>
            <textarea name="question_text" id="questionText" rows="4" placeholder="Type or paste the full exam question here..." required><?= htmlspecialchars($_POST['question_text'] ?? '') ?></textarea>
            <div class="hint-text" id="questionHint">The prompt example will adapt to the selected subject, topic, and assessment style.</div>
            
            <label>Attach Question Image / PDF (Optional)</label>
            <input type="file" name="question_file" accept=".jpg,.jpeg,.png,.pdf,.heic" style="padding:10px; background:rgba(255,255,255,.05); cursor:pointer;">
        </div>

        <div class="card">
            <div class="card-title">✍️ Answer (optional but encouraged)</div>
            <label>Full Answer</label>
            <textarea name="answer_text" id="answerText" rows="5" placeholder="Write a complete answer for this question..."><?= htmlspecialchars($_POST['answer_text'] ?? '') ?></textarea>
            <div class="hint-text" id="answerHint">Answer suggestions become subject-aware after course selection.</div>
            <label>Step-by-Step Solution (for math/algorithm questions)</label>
            <textarea name="solution_steps" id="solutionSteps" rows="4" placeholder="Step 1: ...&#10;Step 2: ...&#10;Step 3: ..."><?= htmlspecialchars($_POST['solution_steps'] ?? '') ?></textarea>
            <div class="hint-text" id="stepsHint">Step suggestions will adapt for broad, short, problem, or MCQ style questions.</div>
        </div>

        <button type="submit" class="btn btn-primary">Submit for Review →</button>
    </form>
</div>
<script>
const courseMeta = <?= json_encode($courseMeta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const savedMarks = <?= json_encode((string) ($_POST['marks'] ?? '10')) ?>;
const savedQuestionType = <?= json_encode($_POST['question_type'] ?? 'broad') ?>;
const courseSelect = document.getElementById('courseSelect');
const assessmentType = document.getElementById('assessmentType');
const questionType = document.getElementById('questionType');
const marksSelect = document.getElementById('marksSelect');
const topicInput = document.getElementById('topicInput');
const topicSuggestions = document.getElementById('topicSuggestions');
const questionText = document.getElementById('questionText');
const answerText = document.getElementById('answerText');
const solutionSteps = document.getElementById('solutionSteps');
const assessmentNote = document.getElementById('assessmentNote');
const questionTypeHint = document.getElementById('questionTypeHint');
const topicHint = document.getElementById('topicHint');
const questionHint = document.getElementById('questionHint');
const answerHint = document.getElementById('answerHint');
const stepsHint = document.getElementById('stepsHint');

const baseTopics = ['Introduction and core concepts', 'Important definitions', 'Worked examples', 'Applications', 'Common short questions', 'Comparison and limitations'];

const topicKeywords = [
    { match: ['theory of computation', 'compiler'], topics: ['Finite automata', 'Regular expressions', 'Context-free grammar', 'Pushdown automata', 'Turing machine', 'Pumping lemma', 'Parsing techniques'] },
    { match: ['digital logic', 'electrical', 'electronic'], topics: ['Logic gates', 'Boolean algebra', 'K-map simplification', 'Combinational circuits', 'Sequential circuits', 'Flip-flops', 'Counters and registers'] },
    { match: ['data structure'], topics: ['Arrays and linked lists', 'Stack and queue', 'Tree traversal', 'Binary search tree', 'Heap', 'Hashing', 'Graph representation'] },
    { match: ['algorithm'], topics: ['Asymptotic analysis', 'Divide and conquer', 'Greedy method', 'Dynamic programming', 'Backtracking', 'Graph algorithms', 'NP-completeness'] },
    { match: ['database'], topics: ['ER model', 'Relational algebra', 'Normalization', 'SQL joins', 'Transactions', 'Indexing', 'Concurrency control'] },
    { match: ['operating system'], topics: ['Processes and threads', 'CPU scheduling', 'Deadlock', 'Memory management', 'Paging and segmentation', 'File systems', 'Synchronization'] },
    { match: ['network'], topics: ['OSI model', 'TCP/IP', 'Subnetting', 'Routing', 'Transport layer', 'Congestion control', 'Application layer'] },
    { match: ['machine learning', 'deep learning'], topics: ['Regression', 'Classification', 'Decision tree', 'Clustering', 'Neural networks', 'Overfitting and regularization', 'Model evaluation'] },
    { match: ['artificial intelligence'], topics: ['Intelligent agents', 'State-space search', 'A* algorithm', 'Knowledge representation', 'Expert systems', 'Game playing', 'Fuzzy logic'] },
    { match: ['software engineering', 'requirement', 'architecture', 'testing', 'project management'], topics: ['SDLC phases', 'Requirements analysis', 'Design principles', 'Architecture patterns', 'Testing strategy', 'Risk management', 'Documentation'] },
    { match: ['object oriented', 'java'], topics: ['Classes and objects', 'Encapsulation', 'Inheritance', 'Polymorphism', 'Abstraction', 'Exception handling', 'File handling'] },
    { match: ['web'], topics: ['HTML forms', 'CSS layout', 'JavaScript DOM', 'PHP basics', 'AJAX', 'Session management', 'CRUD workflow'] },
    { match: ['calculus', 'algebra', 'mathematics', 'statistics', 'probability', 'numerical'], topics: ['Definitions and formulas', 'Theorem statements', 'Worked derivations', 'Problem-solving methods', 'Applications', 'Short notes', 'Common mistakes'] },
    { match: ['cryptography', 'cyber', 'blockchain'], topics: ['Core terminology', 'Algorithms and protocols', 'Security properties', 'Use cases', 'Attack models', 'Comparisons', 'Limitations'] },
    { match: ['graphics'], topics: ['Transformations', 'Projection', 'Clipping', 'Rendering pipeline', 'Algorithms', 'Applications', 'Worked examples'] },
    { match: ['cloud', 'distributed'], topics: ['Architecture overview', 'Core models', 'Scalability', 'Consistency', 'Fault tolerance', 'Security', 'Real-world use cases'] },
    { match: ['viva', 'internship', 'career', 'ethics', 'bangladesh', 'english', 'marketing', 'entrepreneurship', 'economics', 'accounting'], topics: ['Core definitions', 'Short explanations', 'Real-life examples', 'Comparison points', 'Applications', 'Case-based questions', 'Viva points'] }
];

const assessmentConfig = {
    'Class Test': {
        note: 'Class tests usually carry 15 or 20 marks. Use these formats for CT-1 or CT-2.',
        types: [
            { value: 'mcq', label: 'Class Test - MCQ' },
            { value: 'short', label: 'Class Test - Short Question' },
            { value: 'broad', label: 'Class Test - Broad Question' },
            { value: 'problem', label: 'Class Test - Problem / Calculation' }
        ],
        marks: [
            { value: '15', label: '15 marks - class test' },
            { value: '20', label: '20 marks - class test' },
            { value: '10', label: '10 marks - short class test' }
        ]
    },
    'Final Question': {
        note: 'Final theory commonly has 6 questions, answer any 4, and each main question is 10 marks with subpart splits.',
        types: [
            { value: 'broad', label: 'Final - Broad Theory Question' },
            { value: 'problem', label: 'Final - Problem / Derivation' },
            { value: 'short', label: 'Final - Short Structured Question' },
            { value: 'mcq', label: 'Final - MCQ / objective subpart' }
        ],
        marks: [
            { value: '10', label: '10 marks - 4+4+2' },
            { value: '10', label: '10 marks - 5+5' },
            { value: '10', label: '10 marks - 6+4' },
            { value: '10', label: '10 marks - 2+3+5' },
            { value: '10', label: '10 marks - 3+3+4' }
        ]
    },
    'Assignment / Quiz': {
        note: 'Assignment or quiz marks depend on the teacher, but 10 marks is the common internal format.',
        types: [
            { value: 'short', label: 'Assignment / Quiz - Short Question' },
            { value: 'problem', label: 'Assignment / Quiz - Problem Solving' },
            { value: 'mcq', label: 'Assignment / Quiz - MCQ' },
            { value: 'broad', label: 'Assignment / Quiz - Broad Answer' }
        ],
        marks: [
            { value: '10', label: '10 marks - assignment / quiz' },
            { value: '5', label: '5 marks - quick quiz' }
        ]
    },
    'Project': {
        note: 'Project evaluation is usually between 10 and 20 marks depending on the course and teacher.',
        types: [
            { value: 'problem', label: 'Project - Design / Implementation Task' },
            { value: 'broad', label: 'Project - Report / Discussion' },
            { value: 'short', label: 'Project - Feature Explanation' },
            { value: 'mcq', label: 'Project - Checklist / MCQ' }
        ],
        marks: [
            { value: '10', label: '10 marks - mini project' },
            { value: '15', label: '15 marks - regular project' },
            { value: '20', label: '20 marks - major project' }
        ]
    },
    'Viva': {
        note: 'Viva questions should stay concise and oral-defense friendly, usually in the 10 to 20 marks range.',
        types: [
            { value: 'short', label: 'Viva - Short Oral Question' },
            { value: 'broad', label: 'Viva - Concept Explanation' },
            { value: 'problem', label: 'Viva - Dry Run / Problem' },
            { value: 'mcq', label: 'Viva - Rapid Fire MCQ' }
        ],
        marks: [
            { value: '10', label: '10 marks - viva' },
            { value: '15', label: '15 marks - extended viva' },
            { value: '20', label: '20 marks - board viva' }
        ]
    },
    'Attendance': {
        note: 'Attendance contributes 10 marks in the internal 60 before final.',
        types: [
            { value: 'short', label: 'Attendance - Short Check' },
            { value: 'mcq', label: 'Attendance - Quick MCQ' }
        ],
        marks: [
            { value: '10', label: '10 marks - attendance component' }
        ]
    }
};

function getSelectedCourse() {
    const selectedId = Number(courseSelect.value || 0);
    return courseMeta.find((course) => course.id === selectedId) || null;
}

function getCourseTopics(course) {
    if (!course) return baseTopics;
    const name = `${course.name} ${course.code}`.toLowerCase();
    for (const entry of topicKeywords) {
        if (entry.match.some((keyword) => name.includes(keyword))) {
            return entry.topics;
        }
    }
    if (name.includes('lab')) return ['Experiment setup', 'Implementation steps', 'Dry run', 'Output analysis', 'Viva questions', 'Common errors'];
    if (name.includes('project')) return ['Problem statement', 'Requirements', 'Design decisions', 'Implementation steps', 'Testing', 'Presentation points'];
    return baseTopics;
}

function syncQuestionTypes() {
    const config = assessmentConfig[assessmentType.value] || assessmentConfig['Class Test'];
    const currentValue = questionType.dataset.selected || questionType.value || savedQuestionType;
    questionType.innerHTML = '';
    config.types.forEach((item) => {
        const option = document.createElement('option');
        option.value = item.value;
        option.textContent = item.label;
        if (item.value === currentValue) option.selected = true;
        questionType.appendChild(option);
    });
    if (!questionType.value && questionType.options.length) questionType.options[0].selected = true;
    questionTypeHint.textContent = config.note;
}

function syncMarks() {
    const config = assessmentConfig[assessmentType.value] || assessmentConfig['Class Test'];
    const currentValue = marksSelect.dataset.selected || savedMarks;
    marksSelect.innerHTML = '';
    config.marks.forEach((item) => {
        const option = document.createElement('option');
        option.value = item.value;
        option.textContent = item.label;
        if (item.value === currentValue) option.selected = true;
        marksSelect.appendChild(option);
    });
    if (!marksSelect.value && marksSelect.options.length) marksSelect.options[0].selected = true;
    assessmentNote.textContent = config.note;
}

function syncTopics() {
    const course = getSelectedCourse();
    const topics = getCourseTopics(course);
    topicSuggestions.innerHTML = '';
    topics.forEach((topic) => {
        const option = document.createElement('option');
        option.value = topic;
        topicSuggestions.appendChild(option);
    });
    topicInput.placeholder = `Example: ${topics[0]}`;
    topicHint.textContent = course
        ? `${course.code || course.name} topic ideas: ${topics.slice(0, 6).join(', ')}.`
        : 'Choose a subject to get 5 to 7 relevant topics.';
}

function syncPrompts() {
    const course = getSelectedCourse();
    const courseLabel = course ? `${course.code} - ${course.name}` : 'the selected subject';
    const topic = topicInput.value.trim() || 'the selected topic';
    const typeLabel = questionType.options[questionType.selectedIndex] ? questionType.options[questionType.selectedIndex].text : 'question';
    questionText.placeholder = `Write the ${assessmentType.value.toLowerCase()} ${typeLabel.toLowerCase()} for ${courseLabel} on ${topic}. Keep the wording exam-ready.`;
    answerText.placeholder = `Write an exam-ready answer for ${courseLabel} on ${topic}. Match the expected depth for ${marksSelect.value || 'the selected'} marks.`;
    solutionSteps.placeholder = questionType.value === 'problem'
        ? `Step 1: State the rule, theorem, formula, or given data for ${topic}\nStep 2: Show the derivation, calculation, algorithm, or circuit\nStep 3: Present the final answer clearly`
        : `Step 1: Define ${topic}\nStep 2: Explain the key points clearly\nStep 3: Add an example, comparison, or diagram if needed`;
    questionHint.textContent = `Question prompt is tuned for ${assessmentType.value.toLowerCase()} in ${courseLabel}.`;
    answerHint.textContent = `Answer should stay relevant to ${topic} and match the expected marks pattern.`;
    stepsHint.textContent = questionType.value === 'problem'
        ? 'Use clear steps for formulas, circuits, proofs, algorithms, or calculations.'
        : 'Break the response into marking-friendly parts when needed.';
}

function syncAll() {
    syncQuestionTypes();
    syncMarks();
    syncTopics();
    syncPrompts();
}

assessmentType.addEventListener('change', () => {
    questionType.dataset.selected = '';
    marksSelect.dataset.selected = '';
    syncAll();
});
courseSelect.addEventListener('change', syncAll);
questionType.addEventListener('change', () => {
    questionType.dataset.selected = questionType.value;
    syncPrompts();
});
marksSelect.addEventListener('change', () => {
    marksSelect.dataset.selected = marksSelect.value;
    syncPrompts();
});
topicInput.addEventListener('input', syncPrompts);

syncAll();
</script>
</body>
</html>
