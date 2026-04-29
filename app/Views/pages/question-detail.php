<?php
$currentPage = 'question-bank';
$pageTitle = 'Question Detail';
$questionTitle = htmlspecialchars(substr($question['question_text'], 0, 60));
$attachmentPath = isset($question['image_path']) ? '/' . ltrim($question['image_path'], '/') : '';
$existingAnswersText = implode("\n\n", array_column($answers, 'answer_text'));
?>

<style>
.question-detail-page { display: grid; gap: 24px; }
.question-breadcrumb { font-size: 13px; color: #94a3b8; }
.question-breadcrumb a { color: #22d3ee; text-decoration: none; }
.question-detail-layout { display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 24px; align-items: start; }
.q-card, .answer-card, .submit-card, .widget, .ai-panel {
    background: #111827;
    border: 1px solid #1e2d45;
    border-radius: 16px;
    padding: 24px;
}
.q-badges, .action-bar { display: flex; gap: 8px; flex-wrap: wrap; }
.q-badges { margin-bottom: 16px; }
.badge { font-size: 11px; padding: 4px 10px; border-radius: 999px; }
.badge-cyan { background: rgba(34, 211, 238, 0.10); border: 1px solid rgba(34, 211, 238, 0.20); color: #22d3ee; }
.badge-purple { background: rgba(129, 140, 248, 0.10); border: 1px solid rgba(129, 140, 248, 0.20); color: #818cf8; }
.badge-green { background: rgba(52, 211, 153, 0.10); border: 1px solid rgba(52, 211, 153, 0.20); color: #34d399; }
.badge-yellow { background: rgba(251, 191, 36, 0.10); border: 1px solid rgba(251, 191, 36, 0.20); color: #fbbf24; }
.q-text { font-size: 18px; line-height: 1.75; margin-bottom: 16px; color: #e2e8f0; }
.q-meta { display: flex; gap: 20px; flex-wrap: wrap; font-size: 12px; color: #94a3b8; margin-top: 16px; }
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; text-decoration: none; border: none; font-family: 'Syne', sans-serif; }
.btn-primary { background: linear-gradient(135deg, #22d3ee, #818cf8); color: #0a0e1a; }
.btn-outline { background: transparent; border: 1px solid #1e2d45; color: #e2e8f0; }
.btn-bookmark { background: transparent; border: 1px solid #1e2d45; color: #fbbf24; }
.btn-sm { padding: 8px 14px; font-size: 12px; justify-content: center; }
.btn-reading { background: rgba(34, 211, 238, 0.15); border: 1px solid #22d3ee; color: #22d3ee; }
.ai-panel { background: linear-gradient(135deg, rgba(34, 211, 238, 0.06), rgba(129, 140, 248, 0.06)); border-color: rgba(34, 211, 238, 0.20); }
.ai-panel-title, .submit-title, .widget-title, .answers-title { font-family: 'Syne', sans-serif; font-size: 16px; font-weight: 700; color: #e2e8f0; }
.ai-panel-sub { font-size: 13px; color: #94a3b8; margin: 8px 0 16px; }
.ai-loading { display: none; color: #22d3ee; font-size: 13px; margin-top: 12px; align-items: center; gap: 8px; }
.spinner { width: 16px; height: 16px; border: 2px solid rgba(34, 211, 238, 0.30); border-top-color: #22d3ee; border-radius: 50%; animation: spin .6s linear infinite; }
.ai-result {
    background: rgba(0, 0, 0, 0.18);
    border: 1px solid #1e2d45;
    border-radius: 12px;
    padding: 16px;
    font-size: 14px;
    line-height: 1.8;
    white-space: pre-wrap;
    margin-top: 14px;
    display: none;
}
@keyframes spin { to { transform: rotate(360deg); } }
.answer-card { margin-top: 14px; }
.answer-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 14px; }
.author { display: flex; align-items: center; gap: 10px; }
.author-avatar {
    width: 34px; height: 34px; border-radius: 50%;
    object-fit: cover; border: 1px solid #1e2d45;
}
.author-name { font-size: 14px; font-weight: 600; }
.answer-date { font-size: 12px; color: #94a3b8; }
.answer-text { font-size: 14px; line-height: 1.8; white-space: pre-wrap; }
.compact-section, .steps-section {
    border-radius: 12px; padding: 14px; margin-top: 14px; font-size: 14px; line-height: 1.8; white-space: pre-wrap;
}
.compact-section { background: rgba(52, 211, 153, 0.06); border: 1px solid rgba(52, 211, 153, 0.20); }
.steps-section { background: rgba(251, 191, 36, 0.06); border: 1px solid rgba(251, 191, 36, 0.20); }
.section-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 8px; }
.compact-section .section-label { color: #34d399; }
.steps-section .section-label { color: #fbbf24; }
.upvote-btn {
    background: none; border: 1px solid #1e2d45; border-radius: 10px; color: #94a3b8; cursor: pointer; padding: 8px 12px; font-size: 12px;
}
.submit-card textarea {
    width: 100%; background: rgba(255, 255, 255, 0.03); border: 1px solid #1e2d45; border-radius: 12px; padding: 12px; color: #e2e8f0;
    font-size: 14px; font-family: inherit; resize: vertical; outline: none; margin-top: 6px;
}
.submit-card label { display: block; font-size: 12px; color: #94a3b8; margin-top: 14px; }
.success-box, .error-box {
    border-radius: 12px; padding: 12px; font-size: 14px; margin-bottom: 16px;
}
.success-box { background: rgba(52, 211, 153, 0.10); border: 1px solid rgba(52, 211, 153, 0.20); color: #34d399; }
.error-box { background: rgba(248, 113, 113, 0.10); border: 1px solid rgba(248, 113, 113, 0.25); color: #f87171; }
.widget-title { margin-bottom: 12px; }
.related-item { padding: 10px 0; border-bottom: 1px solid #1e2d45; font-size: 13px; line-height: 1.5; }
.related-item:last-child { border-bottom: none; }
.related-item a { color: #e2e8f0; text-decoration: none; }
.question-empty { color: #94a3b8; font-size: 14px; text-align: center; padding: 28px; }
@media (max-width: 960px) { .question-detail-layout { grid-template-columns: 1fr; } }
</style>

<div class="question-detail-page">
    <div class="question-breadcrumb">
        <a href="/question-bank">Question Bank</a> /
        <a href="/question-bank?course=<?= urlencode($question['course_code']) ?>"><?= htmlspecialchars($question['course_code']) ?></a> /
        <span><?= $questionTitle ?></span>
    </div>

    <div class="question-detail-layout">
        <div>
            <div class="q-card">
                <div class="q-badges">
                    <span class="badge badge-cyan"><?= htmlspecialchars($question['course_code']) ?> - <?= htmlspecialchars($question['course_name']) ?></span>
                    <?php if (!empty($question['exam_year'])): ?>
                        <span class="badge badge-purple">Year <?= htmlspecialchars($question['exam_year']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($question['exam_semester'])): ?>
                        <span class="badge badge-purple"><?= htmlspecialchars($question['exam_semester']) ?> Semester</span>
                    <?php endif; ?>
                    <span class="badge badge-green"><?= htmlspecialchars(ucfirst($question['question_type'] ?? 'Theory')) ?></span>
                    <?php if (!empty($question['marks'])): ?>
                        <span class="badge badge-yellow"><?= htmlspecialchars($question['marks']) ?> marks</span>
                    <?php endif; ?>
                    <?php if (($question['is_approved'] ?? 0) == 0): ?>
                        <span class="badge" style="background:rgba(251,191,36,0.1); border:1px solid #fbbf24; color:#fbbf24;">Pending Approval</span>
                    <?php endif; ?>
                </div>

                <div class="q-text"><?= nl2br(htmlspecialchars($question['question_text'])) ?></div>

                <?php if (!empty($attachmentPath)): ?>
                    <?php if (strtolower(substr($question['image_path'], -4)) === '.pdf'): ?>
                        <div style="margin:16px 0;">
                            <a href="<?= htmlspecialchars($attachmentPath) ?>" target="_blank" class="btn btn-outline">View Attached PDF/Document</a>
                        </div>
                    <?php else: ?>
                        <div style="margin:16px 0; border:1px solid #1e2d45; border-radius:12px; overflow:hidden;">
                            <img src="<?= htmlspecialchars($attachmentPath) ?>" alt="Question attachment" style="max-width:100%; display:block;">
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (!empty($question['topic'])): ?>
                    <div style="font-size:12px; color:#94a3b8; margin-bottom:12px;">Topic: <?= htmlspecialchars($question['topic']) ?></div>
                <?php endif; ?>

                <div class="q-meta">
                    <span><?= (int) $question['view_count'] ?> views</span>
                    <span><?= count($answers) ?> answers</span>
                    <?php if (!empty($question['submitted_by_name'])): ?>
                        <span>By <?= htmlspecialchars($question['submitted_by_name']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="action-bar" style="margin-top:20px;">
                    <button class="btn <?= $isBookmarked ? 'btn-bookmark' : 'btn-outline' ?>" id="bookmarkBtn" onclick="toggleBookmark(<?= (int) $question['id'] ?>)">
                        <?= $isBookmarked ? 'Bookmarked' : 'Bookmark' ?>
                    </button>
                    <button class="btn btn-outline" id="readAloudBtn" onclick="toggleReadAloud()">
                        <i data-lucide="volume-2" style="width:14px;height:14px;margin-right:4px;"></i>
                        <span id="readAloudText">Read Aloud</span>
                    </button>
                    <a href="/suggestions?course=<?= (int) $question['course_id'] ?>" class="btn btn-outline">Exam Suggestions</a>
                </div>
            </div>

            <div class="ai-panel">
                <div class="ai-panel-title">AI Compact Answer Generator</div>
                <div class="ai-panel-sub">Generate a short exam-ready answer from the question and the approved community answers.</div>
                <button class="btn btn-primary" id="compactAnswerBtn" onclick="generateCompact()">Generate Compact Answer</button>
                <div class="ai-loading" id="aiLoading"><div class="spinner"></div>AI is writing your compact answer...</div>
                <div class="ai-result" id="aiResult"></div>
            </div>

            <div class="answers-title"><?= count($answers) ?> Answer<?= count($answers) === 1 ? '' : 's' ?></div>

            <?php if (empty($answers)): ?>
                <div class="answer-card question-empty">No answers yet. Be the first to contribute.</div>
            <?php else: ?>
                <?php foreach ($answers as $answer): ?>
                    <div class="answer-card">
                        <div class="answer-header">
                            <div class="author">
                                <img class="author-avatar" src="<?= htmlspecialchars(avatarUrl($answer['author_avatar'] ?? '', $answer['author_name'] ?? 'Author')) ?>" alt="<?= htmlspecialchars($answer['author_name'] ?? 'Author') ?>">
                                <div>
                                    <div class="author-name"><?= htmlspecialchars($answer['author_name'] ?? 'Anonymous') ?></div>
                                    <div class="answer-date"><?= htmlspecialchars(\App\timeAgo($answer['created_at'])) ?></div>
                                </div>
                            </div>
                            <button class="upvote-btn" onclick="upvote(<?= (int) $answer['id'] ?>, this)">
                                Upvote <span class="upvote-count"><?= (int) $answer['upvotes'] ?></span>
                            </button>
                        </div>

                        <?php if ($answer['is_approved'] == 0): ?>
                            <div style="font-size:11px; color:#fbbf24; margin-bottom:8px; font-weight:600;">(Pending Approval — only visible to you)</div>
                        <?php endif; ?>

                        <div class="answer-text"><?= htmlspecialchars($answer['answer_text']) ?></div>

                        <?php if (!empty($answer['compact_answer'])): ?>
                            <div class="compact-section">
                                <div class="section-label">Compact Exam Answer</div>
                                <div><?= htmlspecialchars($answer['compact_answer']) ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($answer['solution_steps'])): ?>
                            <div class="steps-section">
                                <div class="section-label">Step-by-Step Solution</div>
                                <div><?= htmlspecialchars($answer['solution_steps']) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="submit-card">
                <div class="submit-title">Submit Your Answer</div>
                <?php if (!empty($submitSuccess)): ?>
                    <div class="success-box"><?= htmlspecialchars($submitSuccess) ?></div>
                <?php elseif (!empty($submitError)): ?>
                    <div class="error-box"><?= htmlspecialchars($submitError) ?></div>
                <?php endif; ?>

                <form method="POST" action="/question-bank/<?= (int) $question['id'] ?>">
                    <label for="answer_text">Your Answer</label>
                    <textarea id="answer_text" name="answer_text" rows="6" required><?= htmlspecialchars($formData['answer_text'] ?? '') ?></textarea>

                    <label for="solution_steps">Step-by-Step Solution</label>
                    <textarea id="solution_steps" name="solution_steps" rows="4"><?= htmlspecialchars($formData['solution_steps'] ?? '') ?></textarea>

                    <button type="submit" name="submit_answer" class="btn btn-primary" style="margin-top:14px;">Submit Answer</button>
                </form>
            </div>
        </div>

        <div>
            <div class="widget" style="background:linear-gradient(135deg, rgba(34, 211, 238, 0.06), rgba(129, 140, 248, 0.06)); border-color: rgba(34, 211, 238, 0.20);">
                <div class="widget-title">AI Exam Suggestions</div>
                <p style="font-size:13px; color:#94a3b8; line-height:1.6; margin-bottom:14px;">Want to know which topics may appear next in this course?</p>
                <a href="/suggestions?course=<?= (int) $question['course_id'] ?>" class="btn btn-primary btn-sm" style="width:100%;">Get Predictions</a>
            </div>

            <?php if (!empty($relatedQs)): ?>
                <div class="widget">
                    <div class="widget-title">Related Questions</div>
                    <?php foreach ($relatedQs as $related): ?>
                        <div class="related-item">
                            <a href="/question-bank/<?= (int) $related['id'] ?>"><?= htmlspecialchars($related['question_text']) ?></a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="widget">
                <div class="widget-title">Quick Actions</div>
                <a href="/question-bank?course=<?= urlencode($question['course_code']) ?>" class="btn btn-outline btn-sm" style="width:100%; margin-bottom:8px;">More From This Course</a>
                <a href="/question-bank/submit" class="btn btn-outline btn-sm" style="width:100%;">Submit Another Question</a>
            </div>
        </div>
    </div>
</div>

<script>
const questionText = <?php echo json_encode($question['question_text']); ?>;
const existingAnswers = <?php echo json_encode($existingAnswersText); ?>;

/* ── Read Aloud (Web Speech API — 100% free, no API key) ── */
let isSpeaking = false;

function toggleReadAloud() {
    const btn = document.getElementById('readAloudBtn');
    const label = document.getElementById('readAloudText');

    if (isSpeaking) {
        speechSynthesis.cancel();
        isSpeaking = false;
        label.textContent = 'Read Aloud';
        btn.classList.remove('btn-reading');
        btn.classList.add('btn-outline');
        return;
    }

    if (!('speechSynthesis' in window)) {
        alert('Your browser does not support text-to-speech.');
        return;
    }

    // Build the text to read: question + all answers
    let textToRead = questionText;
    const answerCards = document.querySelectorAll('.answer-text');
    if (answerCards.length > 0) {
        textToRead += '. Here are the answers: ';
        answerCards.forEach((card, i) => {
            textToRead += ' Answer ' + (i + 1) + ': ' + card.textContent + '. ';
        });
    }

    const utterance = new SpeechSynthesisUtterance(textToRead);
    utterance.rate = 0.95;
    utterance.pitch = 1;
    utterance.lang = 'en-US';

    // Try to pick a good English voice
    const voices = speechSynthesis.getVoices();
    const englishVoice = voices.find(v => v.lang.startsWith('en') && v.name.includes('Google'))
        || voices.find(v => v.lang.startsWith('en'));
    if (englishVoice) utterance.voice = englishVoice;

    utterance.onstart = () => {
        isSpeaking = true;
        label.textContent = 'Stop Reading';
        btn.classList.remove('btn-outline');
        btn.classList.add('btn-reading');
    };

    utterance.onend = () => {
        isSpeaking = false;
        label.textContent = 'Read Aloud';
        btn.classList.remove('btn-reading');
        btn.classList.add('btn-outline');
    };

    utterance.onerror = () => {
        isSpeaking = false;
        label.textContent = 'Read Aloud';
        btn.classList.remove('btn-reading');
        btn.classList.add('btn-outline');
    };

    speechSynthesis.speak(utterance);
}

// Pre-load voices (some browsers need this)
if ('speechSynthesis' in window) {
    speechSynthesis.getVoices();
    speechSynthesis.onvoiceschanged = () => speechSynthesis.getVoices();
}

/* ── AI Compact Answer ── */
function generateCompact() {
    const loading = document.getElementById('aiLoading');
    const result = document.getElementById('aiResult');
    const button = document.getElementById('compactAnswerBtn');

    loading.style.display = 'flex';
    result.style.display = 'none';
    button.disabled = true;

    fetch('/api/question-bank/compact-answer', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ question: questionText, answers: existingAnswers })
    })
    .then(async (response) => {
        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.text || 'Could not generate compact answer.');
        }
        result.textContent = data.text || 'Could not generate compact answer.';
        result.style.display = 'block';
    })
    .catch((error) => {
        alert(error.message || 'Compact answer request failed.');
    })
    .finally(() => {
        loading.style.display = 'none';
        button.disabled = false;
    });
}

/* ── Bookmark ── */
function toggleBookmark(questionId) {
    fetch('/api/question-bank/bookmark', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ question_id: questionId })
    })
    .then((response) => response.json())
    .then((data) => {
        const button = document.getElementById('bookmarkBtn');
        button.textContent = data.bookmarked ? 'Bookmarked' : 'Bookmark';
        button.className = 'btn ' + (data.bookmarked ? 'btn-bookmark' : 'btn-outline');
    });
}

/* ── Upvote ── */
function upvote(answerId, button) {
    fetch('/api/question-bank/upvote', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ answer_id: answerId })
    })
    .then((response) => response.json())
    .then((data) => {
        if (typeof data.upvotes === 'number') {
            button.querySelector('.upvote-count').textContent = data.upvotes;
        }
    });
}

// Re-render lucide icons for dynamic content
if (typeof lucide !== 'undefined') lucide.createIcons();
</script>
