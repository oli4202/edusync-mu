<?php
// pages/submit-question.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$user = currentUser();
$db   = getDB();

$courses = $db->query("SELECT * FROM courses ORDER BY year, semester, name")->fetchAll();
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
.container{width:100%;max-width:680px;}
.back{color:var(--accent);text-decoration:none;font-size:14px;display:inline-block;margin-bottom:20px;}
.page-title{font-family:'Syne',sans-serif;font-size:22px;font-weight:700;margin-bottom:6px;}
.page-sub{color:var(--muted);font-size:14px;margin-bottom:28px;}
.card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:28px;margin-bottom:20px;}
.card-title{font-family:'Syne',sans-serif;font-size:15px;font-weight:700;margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid var(--border);}
.row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
label{font-size:12px;color:var(--muted);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:.4px;}
input,select,textarea{width:100%;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:12px 14px;color:var(--text);font-size:14px;font-family:inherit;outline:none;margin-bottom:16px;transition:border-color .2s;}
input:focus,select:focus,textarea:focus{border-color:var(--accent);}
select option{background:var(--card);}
.btn{display:inline-flex;align-items:center;gap:6px;padding:12px 24px;border-radius:10px;font-size:14px;font-weight:700;font-family:'Syne',sans-serif;cursor:pointer;text-decoration:none;border:none;transition:all .2s;}
.btn-primary{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#0a0e1a;}
.success-box{background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.2);color:#34d399;border-radius:10px;padding:16px;font-size:14px;margin-bottom:20px;line-height:1.6;}
.error-box{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:#f87171;border-radius:10px;padding:12px;font-size:14px;margin-bottom:20px;}
.tip{background:rgba(34,211,238,.06);border:1px solid rgba(34,211,238,.15);border-radius:10px;padding:14px;font-size:13px;color:var(--muted);margin-bottom:20px;line-height:1.6;}
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

    <form method="POST" enctype="multipart/form-data">
        <div class="card">
            <div class="card-title">📖 Question Details</div>
            <label>Course *</label>
            <select name="course_id" required>
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
                        <?php foreach (['1st','2nd','3rd','4th','5th','6th','7th','8th'] as $s): ?>
                        <option value="<?= $s ?>" <?= (($_POST['exam_semester'] ?? '') === $s) ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div>
                    <label>Question Type</label>
                    <select name="question_type">
                        <option value="broad">Broad (long answer)</option>
                        <option value="short">Short answer</option>
                        <option value="problem">Problem / Calculation</option>
                        <option value="mcq">MCQ</option>
                    </select>
                </div>
                <div>
                    <label>Marks</label>
                    <input type="number" name="marks" value="<?= htmlspecialchars($_POST['marks'] ?? '10') ?>" min="1" max="100">
                </div>
            </div>

            <label>Topic / Chapter</label>
            <input type="text" name="topic" placeholder="e.g. Binary Trees, SQL Joins, Deadlock..." value="<?= htmlspecialchars($_POST['topic'] ?? '') ?>">

            <label>Question Text *</label>
            <textarea name="question_text" rows="4" placeholder="Type or paste the full exam question here..." required><?= htmlspecialchars($_POST['question_text'] ?? '') ?></textarea>
            
            <label>Attach Question Image / PDF (Optional)</label>
            <input type="file" name="question_file" accept=".jpg,.jpeg,.png,.pdf,.heic" style="padding:10px; background:rgba(255,255,255,.05); cursor:pointer;">
        </div>

        <div class="card">
            <div class="card-title">✍️ Answer (optional but encouraged)</div>
            <label>Full Answer</label>
            <textarea name="answer_text" rows="5" placeholder="Write a complete answer for this question..."><?= htmlspecialchars($_POST['answer_text'] ?? '') ?></textarea>
            <label>Step-by-Step Solution (for math/algorithm questions)</label>
            <textarea name="solution_steps" rows="4" placeholder="Step 1: ...&#10;Step 2: ...&#10;Step 3: ..."><?= htmlspecialchars($_POST['solution_steps'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit for Review →</button>
    </form>
</div>
</body>
</html>
