<?php
// pages/partners.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$user = currentUser();
$db   = getDB();
$currentPage = 'partners';

// Handle follow/unfollow
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetId = intval($_POST['user_id'] ?? 0);
    if (($_POST['action'] ?? '') === 'follow' && $targetId !== $user['id']) {
        $check = $db->prepare("SELECT id FROM follows WHERE follower_id=? AND following_id=?");
        $check->execute([$user['id'], $targetId]);
        if (!$check->fetch()) {
            $db->prepare("INSERT INTO follows (follower_id, following_id) VALUES (?,?)")->execute([$user['id'], $targetId]);
        }
        header('Location: partners.php'); exit;
    }
    if (($_POST['action'] ?? '') === 'unfollow') {
        $db->prepare("DELETE FROM follows WHERE follower_id=? AND following_id=?")->execute([$user['id'], $targetId]);
        header('Location: partners.php'); exit;
    }
}

// Search
$search = trim($_GET['q'] ?? '');

// All students (excluding self), with follow status
$query = "
    SELECT u.id, u.name, u.email, u.student_id, u.batch, u.semester, u.department, u.streak, u.bio,
        (SELECT COUNT(*) FROM follows WHERE follower_id=? AND following_id=u.id) AS is_following,
        (SELECT COUNT(*) FROM follows WHERE follower_id=u.id) AS following_count,
        (SELECT COUNT(*) FROM follows WHERE following_id=u.id) AS follower_count,
        (SELECT COUNT(*) FROM group_members WHERE user_id=u.id) AS group_count
    FROM users u
    WHERE u.id != ? AND u.role = 'student'
";
$params = [$user['id'], $user['id']];
if ($search) {
    $query .= " AND (u.name LIKE ? OR u.student_id LIKE ? OR u.batch LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}
$query .= " ORDER BY is_following DESC, u.name ASC LIMIT 50";
$stmt = $db->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();

// My connections count
$myFollowing = $db->prepare("SELECT COUNT(*) FROM follows WHERE follower_id=?");
$myFollowing->execute([$user['id']]); $followingCount = $myFollowing->fetchColumn();
$myFollowers = $db->prepare("SELECT COUNT(*) FROM follows WHERE following_id=?");
$myFollowers->execute([$user['id']]); $followerCount = $myFollowers->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Find Partners — EduSync MU</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
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
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">🔍 Find Study Partners</div>
            <div class="page-sub">Connect with fellow MU SE students</div>
        </div>
        <div style="display:flex;gap:20px;font-size:14px;">
            <span><strong style="color:var(--accent)"><?= $followingCount ?></strong> Following</span>
            <span><strong style="color:var(--accent2)"><?= $followerCount ?></strong> Followers</span>
        </div>
    </div>

    <form class="search-bar" method="GET">
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name, student ID, or batch...">
        <button class="btn btn-primary" type="submit">🔍 Search</button>
        <?php if ($search): ?><a href="partners.php" class="btn btn-outline">Clear</a><?php endif; ?>
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
            <form method="POST">
                <input type="hidden" name="user_id" value="<?= $s['id'] ?>">
                <?php if ($s['is_following']): ?>
                    <input type="hidden" name="action" value="unfollow">
                    <button class="btn btn-sm btn-outline" style="width:100%;">✓ Following</button>
                <?php else: ?>
                    <input type="hidden" name="action" value="follow">
                    <button class="btn btn-sm btn-primary" style="width:100%;">+ Follow</button>
                <?php endif; ?>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>
</body>
</html>
