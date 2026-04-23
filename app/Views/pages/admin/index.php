<?php $pageTitle = 'Admin Panel — EduSync MU'; ?>

<style>
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;}
.stat{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:18px;}
.stat-val{font-family:'Syne',sans-serif;font-size:28px;font-weight:800;color:var(--accent);}
.stat-lbl{font-size:12px;color:var(--muted);margin-top:4px;}
.section-title{font-family:'Syne',sans-serif;font-size:16px;font-weight:700;margin-bottom:16px;display:flex;align-items:center;gap:10px;}
.pending-badge{background:rgba(251,191,36,.15);color:var(--warn);font-size:12px;padding:2px 10px;border-radius:20px;}
.item-card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:18px;margin-bottom:12px;}
.item-meta{font-size:12px;color:var(--muted);margin-bottom:8px;}
.item-text{font-size:14px;line-height:1.6;margin-bottom:14px;}
.item-actions{display:flex;gap:10px;}
.btn-admin{padding:8px 18px;border-radius:8px;font-size:13px;font-weight:600;font-family:'Syne',sans-serif;cursor:pointer;border:none;transition:all .2s;}
.btn-approve{background:rgba(52,211,153,.15);border:1px solid rgba(52,211,153,.3);color:var(--accent3);}
.btn-approve:hover{background:var(--accent3);color:#0a0e1a;}
.btn-reject{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:var(--danger);}
.btn-reject:hover{background:var(--danger);color:#fff;}
.empty-admin{color:var(--muted);font-size:14px;text-align:center;padding:24px;background:var(--card);border:1px solid var(--border);border-radius:12px;}
.admin-nav{margin-bottom: 20px; display: flex; gap: 15px;}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px;}
@media(max-width:700px){.stats-grid{grid-template-columns:repeat(2,1fr);}.grid-2{grid-template-columns:1fr;}}
</style>

<div class="admin-nav">
    <a href="/admin/attendance" class="btn btn-outline btn-sm">📋 Manage Attendance</a>
    <a href="/admin/api-settings" class="btn btn-outline btn-sm">⚙️ API Settings</a>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat"><div class="stat-val"><?= $stats['users'] ?></div><div class="stat-lbl">Students</div></div>
    <div class="stat"><div class="stat-val"><?= $stats['questions'] ?></div><div class="stat-lbl">Approved Questions</div></div>
    <div class="stat"><div class="stat-val"><?= $stats['answers'] ?></div><div class="stat-lbl">Approved Answers</div></div>
    <div class="stat"><div class="stat-val"><?= $stats['groups'] ?></div><div class="stat-lbl">Study Groups</div></div>
</div>

<div class="grid-2">
    <!-- Pending Questions -->
    <div>
        <div class="section-title">
            📖 Pending Questions
            <span class="pending-badge"><?= count($pendingQuestions) ?> pending</span>
        </div>
        <?php if (empty($pendingQuestions)): ?>
        <div class="empty-admin">✅ No pending questions.</div>
        <?php else: ?>
        <?php foreach ($pendingQuestions as $q): ?>
        <div class="item-card">
            <div class="item-meta">
                <strong><?= htmlspecialchars($q['code']) ?></strong> ·
                <?= htmlspecialchars($q['course_name']) ?> ·
                <?= $q['exam_year'] ? 'Year '.$q['exam_year'] : '' ?> <?= $q['exam_semester'] ? $q['exam_semester'].' Sem' : '' ?> ·
                <?= $q['marks'] ?> marks ·
                Submitted by <?= htmlspecialchars($q['submitter'] ?? 'Unknown') ?>
            </div>
            <div class="item-text"><?= htmlspecialchars(mb_substr($q['question_text'], 0, 250)) ?><?= strlen($q['question_text']) > 250 ? '...' : '' ?></div>
            <div class="item-actions">
                <form action="/admin/questions/approve" method="POST" style="display:inline">
                    <input type="hidden" name="id" value="<?= $q['id'] ?>">
                    <button type="submit" class="btn-admin btn-approve">✅ Approve</button>
                </form>
                <form action="/admin/questions/reject" method="POST" style="display:inline" onsubmit="return confirm('Delete this question?')">
                    <input type="hidden" name="id" value="<?= $q['id'] ?>">
                    <button type="submit" class="btn-admin btn-reject">🗑 Reject</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pending Answers -->
    <div>
        <div class="section-title">
            💬 Pending Answers
            <span class="pending-badge"><?= count($pendingAnswers) ?> pending</span>
        </div>
        <?php if (empty($pendingAnswers)): ?>
        <div class="empty-admin">✅ No pending answers.</div>
        <?php else: ?>
        <?php foreach ($pendingAnswers as $a): ?>
        <div class="item-card">
            <div class="item-meta">
                Answering: <em><?= htmlspecialchars(mb_substr($a['question_text'],0,80)) ?>...</em> ·
                By <?= htmlspecialchars($a['author'] ?? 'Unknown') ?>
            </div>
            <div class="item-text"><?= htmlspecialchars(mb_substr($a['answer_text'], 0, 200)) ?><?= strlen($a['answer_text']) > 200 ? '...' : '' ?></div>
            <div class="item-actions">
                <form action="/admin/answers/approve" method="POST" style="display:inline">
                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                    <button type="submit" class="btn-admin btn-approve">✅ Approve</button>
                </form>
                <form action="/admin/answers/reject" method="POST" style="display:inline" onsubmit="return confirm('Delete this answer?')">
                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                    <button type="submit" class="btn-admin btn-reject">🗑 Reject</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
