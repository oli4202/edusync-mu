<?php
// LEGACY FILE - REDIRECT TO MVC
header('Location: /flashcards');
exit;

require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$user = currentUser();
$db   = getDB();
$currentPage = 'flashcards';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $stmt = $db->prepare("INSERT INTO flashcards (user_id, subject_id, deck_name, question, answer) VALUES (?,?,?,?,?)");
        $stmt->execute([$user['id'], $_POST['subject_id'] ?: null, clean($_POST['deck_name'] ?: 'General'), clean($_POST['question']), clean($_POST['answer'])]);
        header('Location: flashcards.php' . (isset($_GET['deck']) ? '?deck='.urlencode($_GET['deck']) : '')); exit;
    }
    if ($action === 'delete') {
        $db->prepare("DELETE FROM flashcards WHERE id=? AND user_id=?")->execute([$_POST['card_id'], $user['id']]);
        header('Location: flashcards.php' . (isset($_GET['deck']) ? '?deck='.urlencode($_GET['deck']) : '')); exit;
    }
    if ($action === 'ai_generate') {
        $topic = clean($_POST['topic']);
        $count = min(intval($_POST['count'] ?? 5), 10);
        $result = callAI(
            "Generate exactly $count flashcard question-answer pairs for the topic: \"$topic\" for a Software Engineering university student. Return ONLY a JSON array with objects having \"question\" and \"answer\" keys. No extra text.",
            "You are a flashcard generator. Return only valid JSON array."
        );

        if ($result['success']) {
            $json = $result['text'];
            // Extract JSON from response
            if (preg_match('/\[.*\]/s', $json, $matches)) {
                $cards = json_decode($matches[0], true);
                if ($cards) {
                    $stmt = $db->prepare("INSERT INTO flashcards (user_id, subject_id, deck_name, question, answer, ai_generated) VALUES (?,?,?,?,?,1)");
                    foreach ($cards as $card) {
                        if (isset($card['question']) && isset($card['answer'])) {
                            $stmt->execute([$user['id'], $_POST['subject_id'] ?: null, clean($_POST['deck_name'] ?: $topic), $card['question'], $card['answer']]);
                        }
                    }
                }
            }
        }
        header('Location: flashcards.php'); exit;
    }
}

// Get decks
$decks = $db->prepare("SELECT deck_name, COUNT(*) as cnt FROM flashcards WHERE user_id=? GROUP BY deck_name ORDER BY deck_name");
$decks->execute([$user['id']]); $deckList = $decks->fetchAll();

// Current deck
$currentDeck = $_GET['deck'] ?? null;
if ($currentDeck) {
    $cards = $db->prepare("SELECT f.*, s.name AS subject_name FROM flashcards f LEFT JOIN subjects s ON f.subject_id=s.id WHERE f.user_id=? AND f.deck_name=? ORDER BY f.created_at DESC");
    $cards->execute([$user['id'], $currentDeck]);
} else {
    $cards = $db->prepare("SELECT f.*, s.name AS subject_name FROM flashcards f LEFT JOIN subjects s ON f.subject_id=s.id WHERE f.user_id=? ORDER BY f.created_at DESC LIMIT 50");
    $cards->execute([$user['id']]);
}
$cardList = $cards->fetchAll();

$subjectList = $db->prepare("SELECT id, name, code, year, semester FROM subjects WHERE user_id=? ORDER BY year ASC, semester ASC, name ASC");
$subjectList->execute([$user['id']]); $subs = $subjectList->fetchAll();
$subjectMeta = [];
foreach ($subs as $subject) {
    $subjectMeta[] = [
        'id' => (int) $subject['id'],
        'name' => $subject['name'],
        'code' => $subject['code'] ?? '',
        'year' => isset($subject['year']) ? (int) $subject['year'] : 0,
        'semester' => isset($subject['semester']) ? (int) $subject['semester'] : 0,
    ];
}
$deckNames = array_map(fn($deck) => $deck['deck_name'], $deckList);

$totalCards = $db->prepare("SELECT COUNT(*) FROM flashcards WHERE user_id=?");
$totalCards->execute([$user['id']]); $totalCount = $totalCards->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Flashcards — EduSync MU</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.fc-layout { display:grid; grid-template-columns:220px 1fr; gap:24px; margin-top:20px; }
.deck-panel { }
.deck-item { display:flex; align-items:center; justify-content:space-between; padding:10px 14px; border-radius:8px; cursor:pointer; font-size:13px; color:var(--muted); transition:all .2s; text-decoration:none; margin-bottom:4px; }
.deck-item:hover, .deck-item.active { color:var(--text); background:rgba(34,211,238,.06); }
.deck-count { font-size:11px; background:rgba(34,211,238,.1); color:var(--accent); padding:2px 8px; border-radius:10px; }
.cards-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; }
.flashcard { background:var(--card); border:1px solid var(--border); border-radius:14px; min-height:180px; cursor:pointer; perspective:1000px; position:relative; }
.flashcard-inner { position:relative; width:100%; height:100%; min-height:180px; text-align:center; transition:transform .5s; transform-style:preserve-3d; }
.flashcard.flipped .flashcard-inner { transform:rotateY(180deg); }
.flashcard-front, .flashcard-back { position:absolute; inset:0; backface-visibility:hidden; border-radius:14px; padding:22px; display:flex; flex-direction:column; align-items:center; justify-content:center; }
.flashcard-front { background:var(--card); }
.flashcard-back { background:var(--card2); transform:rotateY(180deg); border:1px solid var(--accent); }
.flashcard-label { font-size:10px; color:var(--muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:10px; }
.flashcard-text { font-size:14px; line-height:1.6; }
.flashcard-meta { position:absolute; bottom:10px; left:14px; right:14px; display:flex; justify-content:space-between; align-items:center; font-size:11px; color:var(--muted); }
.study-mode { position:fixed; inset:0; background:var(--bg); z-index:300; display:none; flex-direction:column; align-items:center; justify-content:center; }
.study-mode.active { display:flex; }
.study-card { width:90%; max-width:600px; min-height:300px; perspective:1000px; cursor:pointer; }
.study-inner { position:relative; width:100%; min-height:300px; transition:transform .5s; transform-style:preserve-3d; }
.study-card.flipped .study-inner { transform:rotateY(180deg); }
.study-front, .study-back { position:absolute; inset:0; backface-visibility:hidden; border-radius:16px; padding:40px; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; }
.study-front { background:var(--card); border:1px solid var(--border); }
.study-back { background:var(--card2); border:1px solid var(--accent); transform:rotateY(180deg); }
.study-controls { margin-top:28px; display:flex; gap:12px; }
.study-progress { margin-top:16px; font-size:13px; color:var(--muted); }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:200; align-items:center; justify-content:center; }
.modal-overlay.active { display:flex; }
.modal { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:28px; width:100%; max-width:480px; }
.modal h3 { font-family:'Syne',sans-serif; font-size:18px; font-weight:700; margin-bottom:20px; }
.form-group { margin-bottom:16px; }
.suggestion-row { display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; }
.suggestion-chip { border:1px solid var(--border); background:rgba(255,255,255,.03); color:var(--text); border-radius:999px; padding:6px 10px; font-size:11px; cursor:pointer; transition:all .2s; }
.suggestion-chip:hover { border-color:var(--accent); color:var(--accent); }
.hint-text { color:var(--muted); font-size:11px; margin-top:8px; }
optgroup {
    background: rgba(34, 211, 238, 0.1) !important;
    color: #00d9ff !important;
    font-weight: 600;
}
@media(max-width:900px) { .fc-layout { grid-template-columns:1fr; } }
</style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">🃏 Flashcards</div>
            <div class="page-sub"><?= $totalCount ?> cards across <?= count($deckList) ?> decks</div>
        </div>
        <div style="display:flex;gap:10px;">
            <button class="btn btn-outline" onclick="document.getElementById('aiModal').classList.add('active')">🤖 AI Generate</button>
            <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('active')">+ New Card</button>
        </div>
    </div>

    <div class="fc-layout">
        <div class="deck-panel">
            <div class="card" style="padding:16px;">
                <div style="font-family:'Syne',sans-serif;font-size:13px;font-weight:700;margin-bottom:12px;">📦 Decks</div>
                <a href="flashcards.php" class="deck-item <?= !$currentDeck ? 'active' : '' ?>">All Cards <span class="deck-count"><?= $totalCount ?></span></a>
                <?php foreach ($deckList as $d): ?>
                <a href="flashcards.php?deck=<?= urlencode($d['deck_name']) ?>" class="deck-item <?= $currentDeck === $d['deck_name'] ? 'active' : '' ?>"><?= htmlspecialchars($d['deck_name']) ?> <span class="deck-count"><?= $d['cnt'] ?></span></a>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($cardList)): ?>
            <button class="btn btn-primary" style="width:100%;margin-top:12px;" onclick="startStudy()">📖 Study Mode</button>
            <?php endif; ?>
        </div>

        <div>
            <?php if (empty($cardList)): ?>
            <div class="card" style="text-align:center;padding:60px 20px;">
                <div style="font-size:48px;margin-bottom:16px;">🃏</div>
                <div style="font-family:'Syne',sans-serif;font-size:18px;font-weight:700;margin-bottom:8px;">No flashcards yet</div>
                <div style="color:var(--muted);font-size:14px;margin-bottom:20px;">Create cards manually or let AI generate them!</div>
                <div style="display:flex;gap:10px;justify-content:center;">
                    <button class="btn btn-outline" onclick="document.getElementById('aiModal').classList.add('active')">🤖 AI Generate</button>
                    <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('active')">+ Create Card</button>
                </div>
            </div>
            <?php else: ?>
            <div style="font-size:12px;color:var(--muted);margin-bottom:12px;">Click a card to flip it · <?= count($cardList) ?> cards shown</div>
            <div class="cards-grid">
                <?php foreach ($cardList as $c): ?>
                <div class="flashcard" onclick="this.classList.toggle('flipped')">
                    <div class="flashcard-inner">
                        <div class="flashcard-front">
                            <div class="flashcard-label">Question</div>
                            <div class="flashcard-text"><?= htmlspecialchars($c['question']) ?></div>
                            <div class="flashcard-meta">
                                <span><?= htmlspecialchars($c['deck_name']) ?></span>
                                <?php if ($c['ai_generated']): ?><span class="badge badge-cyan" style="font-size:9px;">AI</span><?php endif; ?>
                            </div>
                        </div>
                        <div class="flashcard-back">
                            <div class="flashcard-label">Answer</div>
                            <div class="flashcard-text"><?= htmlspecialchars($c['answer']) ?></div>
                        </div>
                    </div>
                    <form method="POST" onclick="event.stopPropagation();" style="position:absolute;top:8px;right:8px;z-index:10;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="card_id" value="<?= $c['id'] ?>">
                        <button type="submit" onclick="return confirm('Delete?')" style="background:none;border:none;font-size:14px;cursor:pointer;opacity:.4;color:var(--text);">🗑</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Study Mode -->
<div class="study-mode" id="studyMode">
    <button class="btn btn-outline" onclick="exitStudy()" style="position:absolute;top:20px;right:20px;">✕ Exit</button>
    <div class="study-card" id="studyCard" onclick="this.classList.toggle('flipped')">
        <div class="study-inner">
            <div class="study-front">
                <div class="flashcard-label">Question</div>
                <div class="flashcard-text" id="studyQ" style="font-size:18px;"></div>
            </div>
            <div class="study-back">
                <div class="flashcard-label">Answer</div>
                <div class="flashcard-text" id="studyA" style="font-size:18px;"></div>
            </div>
        </div>
    </div>
    <div class="study-controls">
        <button class="btn btn-outline" onclick="prevCard()">← Previous</button>
        <button class="btn btn-primary" onclick="nextCard()">Next →</button>
    </div>
    <div class="study-progress" id="studyProgress"></div>
</div>

<!-- Add Card Modal -->
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <h3>🃏 New Flashcard</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label>Deck Name</label>
                    <input type="text" name="deck_name" id="flashcardDeck" list="deckNameList" value="<?= htmlspecialchars($currentDeck ?? 'General') ?>" placeholder="e.g. DSA">
                    <datalist id="deckNameList">
                        <?php foreach ($deckNames as $deckName): ?>
                        <option value="<?= htmlspecialchars($deckName) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="form-group">
                    <label>Subject</label>
                    <select name="subject_id">
                        <option value="">— None —</option>
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
            </div>
            <div class="form-group">
                <label>Question *</label>
                <textarea name="question" id="flashcardQuestion" rows="3" required placeholder="What is the time complexity of binary search?"></textarea>
                <div class="suggestion-row" id="flashcardQuestionSuggestions"></div>
            </div>
            <div class="form-group">
                <label>Answer *</label>
                <textarea name="answer" id="flashcardAnswer" rows="3" required placeholder="O(log n)"></textarea>
                <div class="suggestion-row" id="flashcardAnswerSuggestions"></div>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:10px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('addModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Card</button>
            </div>
        </form>
    </div>
</div>

<!-- AI Generate Modal -->
<div class="modal-overlay" id="aiModal">
    <div class="modal">
        <h3>🤖 AI Generate Flashcards</h3>
        <form method="POST">
            <input type="hidden" name="action" value="ai_generate">
            <div class="form-group">
                <label>Topic *</label>
                <input type="text" name="topic" id="aiTopic" list="aiTopicList" required placeholder="e.g. Binary Trees, OOP concepts, SQL joins">
                <datalist id="aiTopicList"></datalist>
                <div class="hint-text">Relevant topic ideas update when you choose a subject.</div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label>Count</label>
                    <select name="count">
                        <option value="5">5 cards</option>
                        <option value="10">10 cards</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Deck</label>
                    <input type="text" name="deck_name" placeholder="Auto">
                </div>
                <div class="form-group">
                    <label>Subject</label>
                    <select name="subject_id">
                        <option value="">—</option>
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
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:10px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('aiModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary">🤖 Generate</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.modal-overlay').forEach(m=>m.addEventListener('click',function(e){if(e.target===this)this.classList.remove('active');}));

const studyCards = <?= json_encode($cardList) ?>;
const flashcardSubjects = <?= json_encode($subjectMeta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const manualSubjectSelect = document.querySelector('#addModal select[name="subject_id"]');
const aiSubjectSelect = document.querySelector('#aiModal select[name="subject_id"]');
const flashcardDeck = document.getElementById('flashcardDeck');
const flashcardQuestion = document.getElementById('flashcardQuestion');
const flashcardAnswer = document.getElementById('flashcardAnswer');
const questionSuggestions = document.getElementById('flashcardQuestionSuggestions');
const answerSuggestions = document.getElementById('flashcardAnswerSuggestions');
const aiTopicList = document.getElementById('aiTopicList');
let studyIdx = 0;

function buildFlashcardPrompts(subject) {
    const label = subject ? (subject.code || subject.name) : 'General Study';
    return {
        deck: subject ? `${label} Quick Review` : 'General',
        questions: [
            `What are the key concepts of ${label}?`,
            `What is the most important formula or rule in ${label}?`,
            `What are common exam questions from ${label}?`
        ],
        answers: [
            `A short summary of the core ideas, definitions, and examples from ${label}.`,
            `List the main formula, explain when to use it, and give one quick example.`,
            `Mention the usual question patterns, key steps, and common mistakes to avoid.`
        ],
        topics: [
            `${label} definitions`,
            `${label} important formulas`,
            `${label} short questions`,
            `${label} viva topics`,
            `${label} exam revision`
        ]
    };
}

function renderSuggestionButtons(container, values, target) {
    container.innerHTML = '';
    values.forEach((value) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'suggestion-chip';
        button.textContent = value;
        button.addEventListener('click', () => {
            target.value = value;
            target.focus();
        });
        container.appendChild(button);
    });
}

function syncFlashcardSuggestions() {
    const selectedSubject = flashcardSubjects.find((item) => item.id === Number(manualSubjectSelect.value || 0)) || null;
    const prompts = buildFlashcardPrompts(selectedSubject);
    if (!flashcardDeck.value.trim() || flashcardDeck.value === 'General') {
        flashcardDeck.value = prompts.deck;
    }
    renderSuggestionButtons(questionSuggestions, prompts.questions, flashcardQuestion);
    renderSuggestionButtons(answerSuggestions, prompts.answers, flashcardAnswer);
}

function syncAITopicSuggestions() {
    const selectedSubject = flashcardSubjects.find((item) => item.id === Number(aiSubjectSelect.value || 0)) || null;
    const prompts = buildFlashcardPrompts(selectedSubject);
    aiTopicList.innerHTML = '';
    prompts.topics.forEach((topic) => {
        const option = document.createElement('option');
        option.value = topic;
        aiTopicList.appendChild(option);
    });
}

manualSubjectSelect.addEventListener('change', syncFlashcardSuggestions);
aiSubjectSelect.addEventListener('change', syncAITopicSuggestions);
syncFlashcardSuggestions();
syncAITopicSuggestions();

function startStudy() {
    if (!studyCards.length) return;
    studyIdx = 0;
    showStudyCard();
    document.getElementById('studyMode').classList.add('active');
}
function exitStudy() { document.getElementById('studyMode').classList.remove('active'); }
function showStudyCard() {
    const c = studyCards[studyIdx];
    document.getElementById('studyQ').textContent = c.question;
    document.getElementById('studyA').textContent = c.answer;
    document.getElementById('studyCard').classList.remove('flipped');
    document.getElementById('studyProgress').textContent = `Card ${studyIdx+1} of ${studyCards.length}`;
}
function nextCard() { studyIdx = (studyIdx + 1) % studyCards.length; showStudyCard(); }
function prevCard() { studyIdx = (studyIdx - 1 + studyCards.length) % studyCards.length; showStudyCard(); }
document.addEventListener('keydown', function(e) {
    if (!document.getElementById('studyMode').classList.contains('active')) return;
    if (e.key === 'ArrowRight') nextCard();
    if (e.key === 'ArrowLeft') prevCard();
    if (e.key === ' ') { e.preventDefault(); document.getElementById('studyCard').classList.toggle('flipped'); }
    if (e.key === 'Escape') exitStudy();
});
</script>
</body>
</html>
