<?php $currentPage = 'partners'; ?>

<style>
.search-bar { display:flex; gap:12px; margin-bottom:24px; }
.search-bar input { flex:1; }
.partner-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:20px; }
.partner-card { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:22px; transition:all .2s; }
.partner-card:hover { border-color:var(--accent); }
.partner-top { display:flex; align-items:center; gap:14px; margin-bottom:14px; }
.partner-avatar { width:44px; height:44px; border-radius:50%; background:linear-gradient(135deg,var(--accent),var(--accent2)); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:16px; color:#0a0e1a; flex-shrink:0; }
.partner-name { font-family:'Syne',sans-serif; font-size:15px; font-weight:700; }
.partner-info { font-size:12px; color:var(--muted); }
.partner-bio { font-size:13px; color:var(--muted); line-height:1.5; margin-bottom:12px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.partner-stats { display:flex; gap:16px; font-size:12px; color:var(--muted); margin-bottom:14px; }
.partner-stat-val { font-weight:700; color:var(--text); }
</style>

<div class="topbar">
    <div>
        <div class="page-title">🔍 Find Study Partners</div>
        <div class="page-sub">Connect with fellow MU SE students</div>
    </div>
    <div style="display:flex;gap:20px;font-size:14px;">
        <span><strong style="color:var(--accent)"><?= $counts['following'] ?></strong> Following</span>
        <span><strong style="color:var(--accent2)"><?= $counts['followers'] ?></strong> Followers</span>
    </div>
</div>

<form class="search-bar" method="GET" action="/partners">
    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name, student ID, or batch...">
    <button class="btn btn-primary" type="submit">🔍 Search</button>
    <?php if ($search): ?><a href="/partners" class="btn btn-outline">Clear</a><?php endif; ?>
</form>

<?php if (empty($students)): ?>
<div class="card" style="text-align:center;padding:60px 20px;">
    <div style="font-size:48px;margin-bottom:16px;">🔍</div>
    <div style="font-family:'Syne',sans-serif;font-size:18px;font-weight:700;margin-bottom:8px;">No students found</div>
    <div style="color:var(--muted);font-size:14px;">Try a different search term or invite your classmates!</div>
</div>
<?php else: ?>
<div class="partner-grid">
    <?php foreach ($students as $s): ?>
    <div class="partner-card">
        <div class="partner-top">
            <div class="partner-avatar"><?= strtoupper(substr($s['name'],0,1)) ?></div>
            <div>
                <div class="partner-name"><?= htmlspecialchars($s['name']) ?></div>
                <div class="partner-info">
                    <?= htmlspecialchars($s['student_id'] ?: 'No ID') ?> · Semester <?= $s['semester'] ?>
                    <?php if ($s['batch']): ?> · Batch <?= htmlspecialchars($s['batch']) ?><?php endif; ?>
                </div>
            </div>
        </div>
        <?php if ($s['bio']): ?><div class="partner-bio"><?= htmlspecialchars($s['bio']) ?></div><?php endif; ?>
        <div class="partner-stats">
            <span>🔥 <span class="partner-stat-val"><?= $s['streak'] ?></span> streak</span>
            <span>👥 <span class="partner-stat-val"><?= $s['group_count'] ?></span> groups</span>
            <span>👤 <span class="partner-stat-val"><?= $s['follower_count'] ?></span> followers</span>
        </div>
        <form action="<?= $s['is_following'] ? '/partners/unfollow' : '/partners/follow' ?>" method="POST">
            <input type="hidden" name="user_id" value="<?= $s['id'] ?>">
            <?php if ($s['is_following']): ?>
                <button class="btn btn-sm btn-outline" style="width:100%;">✓ Following</button>
            <?php else: ?>
                <button class="btn btn-sm btn-primary" style="width:100%;">+ Follow</button>
            <?php endif; ?>
        </form>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
