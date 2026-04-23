<?php 
$currentPage = 'announcements'; 
$typeColors = [
    'general'    => ['#22d3ee','rgba(34,211,238,.1)','📢'],
    'exam'       => ['#f87171','rgba(248,113,113,.1)','📝'],
    'assignment' => ['#818cf8','rgba(129,140,248,.1)','📋'],
    'event'      => ['#34d399','rgba(52,211,153,.1)','🎉'],
    'urgent'     => ['#fbbf24','rgba(251,191,36,.15)','🚨'],
];
?>

<style>
.ann-card { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:22px; margin-bottom:14px; position:relative; transition:border-color .2s; }
.ann-card.pinned { border-color:rgba(34,211,238,.3); }
.ann-card.urgent-type { border-color:rgba(251,191,36,.3); }
.pin-flag { position:absolute; top:14px; right:16px; font-size:16px; }
.ann-type-badge { display:inline-flex; align-items:center; gap:5px; font-size:11px; padding:3px 10px; border-radius:20px; font-weight:600; margin-bottom:10px; }
.ann-title { font-family:'Syne',sans-serif; font-size:17px; font-weight:700; margin-bottom:8px; }
.ann-content { font-size:14px; line-height:1.7; color:#cbd5e1; white-space:pre-wrap; }
.ann-meta { display:flex; gap:16px; font-size:12px; color:var(--muted); margin-top:14px; flex-wrap:wrap; }
.ann-actions { display:flex; gap:8px; margin-top:14px; }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.7); z-index:999; align-items:center; justify-content:center; }
.modal-overlay.open { display:flex; }
.modal { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:28px; width:100%; max-width:520px; animation:fadeUp .3s ease; }
@keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
.modal-title { font-family:'Syne',sans-serif; font-size:16px; font-weight:700; margin-bottom:20px; }
.field { margin-bottom:14px; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.filter-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:24px; }
.tab { padding:7px 16px; border-radius:20px; font-size:13px; font-weight:600; cursor:pointer; border:1px solid var(--border); color:var(--muted); transition:all .2s; background:transparent; }
.tab.active, .tab:hover { background:rgba(34,211,238,.1); border-color:var(--accent); color:var(--accent); }
.empty-state { text-align:center; padding:40px; color:var(--muted); }
</style>

<div class="topbar">
    <div>
        <div class="page-title">📢 Announcements</div>
        <div class="page-sub">Department notices, exam alerts, and event updates</div>
    </div>
    <?php if ($isAdmin): ?>
    <button class="btn btn-primary" onclick="document.getElementById('postModal').classList.add('open')">+ Post Announcement</button>
    <?php endif; ?>
</div>

<!-- Filter Tabs -->
<div class="filter-tabs">
    <button class="tab active" onclick="filterAnn('all',this)">All</button>
    <button class="tab" onclick="filterAnn('urgent',this)">🚨 Urgent</button>
    <button class="tab" onclick="filterAnn('exam',this)">📝 Exam</button>
    <button class="tab" onclick="filterAnn('assignment',this)">📋 Assignment</button>
    <button class="tab" onclick="filterAnn('event',this)">🎉 Event</button>
    <button class="tab" onclick="filterAnn('general',this)">📢 General</button>
</div>

<!-- Announcements List -->
<?php if (empty($announcements)): ?>
<div class="empty-state">
    <div style="font-size:40px;margin-bottom:12px;">📭</div>
    <div>No announcements yet.</div>
    <?php if ($isAdmin): ?>
    <div style="margin-top:10px;"><button onclick="document.getElementById('postModal').classList.add('open')" class="btn btn-primary btn-sm">Post First Announcement</button></div>
    <?php endif; ?>
</div>
<?php else: ?>
<div id="annList">
<?php foreach ($announcements as $a):
    [$tColor, $tBg, $tIcon] = $typeColors[$a['type']] ?? $typeColors['general'];
    $isUrgent = $a['type'] === 'urgent';
?>
<div class="ann-card <?= $a['is_pinned']?'pinned':'' ?> <?= $isUrgent?'urgent-type':'' ?>" data-type="<?= $a['type'] ?>">
    <?php if ($a['is_pinned']): ?><div class="pin-flag">📌</div><?php endif; ?>
    <div class="ann-type-badge" style="background:<?= $tBg ?>;color:<?= $tColor ?>"><?= $tIcon ?> <?= ucfirst($a['type']) ?></div>
    <div class="ann-title"><?= htmlspecialchars($a['title']) ?></div>
    <div class="ann-content"><?= htmlspecialchars($a['content']) ?></div>
    <div class="ann-meta">
        <span>👤 <?= htmlspecialchars($a['posted_by']) ?></span>
        <span>🕒 <?= App\timeAgo($a['created_at']) ?></span>
        <?php if ($a['target_semester'] > 0): ?>
        <span>🎓 Semester <?= $a['target_semester'] ?> only</span>
        <?php endif; ?>
        <?php if ($a['expires_at']): ?>
        <span>⏳ Expires <?= date('M j, Y', strtotime($a['expires_at'])) ?></span>
        <?php endif; ?>
    </div>
    <?php if ($isAdmin): ?>
    <div class="ann-actions">
        <form action="/announcements/pin" method="POST" style="display:inline">
            <input type="hidden" name="ann_id" value="<?= $a['id'] ?>">
            <button type="submit" class="btn btn-outline btn-sm"><?= $a['is_pinned']?'Unpin':'📌 Pin' ?></button>
        </form>
        <form action="/announcements/delete" method="POST" style="display:inline" onsubmit="return confirm('Delete this announcement?')">
            <input type="hidden" name="ann_id" value="<?= $a['id'] ?>">
            <button type="submit" class="btn btn-danger btn-sm">🗑 Delete</button>
        </form>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Post Announcement Modal (Admin only) -->
<?php if ($isAdmin): ?>
<div class="modal-overlay" id="postModal">
    <div class="modal">
        <div class="modal-title">📢 Post Announcement</div>
        <form action="/announcements/post" method="POST">
            <div class="field">
                <label>Title *</label>
                <input type="text" name="title" placeholder="Announcement title..." required>
            </div>
            <div class="field">
                <label>Content *</label>
                <textarea name="content" rows="5" placeholder="Write your announcement..." required></textarea>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>Type</label>
                    <select name="type">
                        <option value="general">📢 General</option>
                        <option value="exam">📝 Exam</option>
                        <option value="assignment">📋 Assignment</option>
                        <option value="event">🎉 Event</option>
                        <option value="urgent">🚨 Urgent</option>
                    </select>
                </div>
                <div class="field">
                    <label>Target Semester (0 = All)</label>
                    <select name="target_semester">
                        <option value="0">All Students</option>
                        <?php for($i=1;$i<=8;$i++): ?>
                        <option value="<?= $i ?>">Semester <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>Expires On (optional)</label>
                    <input type="date" name="expires_at">
                </div>
                <div class="field" style="display:flex;align-items:center;gap:8px;padding-top:22px;">
                    <input type="checkbox" name="is_pinned" id="pinCheck" style="width:16px;height:16px;">
                    <label for="pinCheck" style="text-transform:none;letter-spacing:0;margin:0;font-size:14px;">📌 Pin to top</label>
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:8px;">
                <button type="submit" class="btn btn-primary">Post Announcement</button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('postModal').classList.remove('open')">Cancel</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
document.getElementById('postModal')?.addEventListener('click', function(e) {
    if(e.target===this) this.classList.remove('open');
});
function filterAnn(type, btn) {
    document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.ann-card').forEach(card => {
        card.style.display = (type==='all' || card.dataset.type===type) ? 'block' : 'none';
    });
}
</script>
