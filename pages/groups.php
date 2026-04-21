<?php
// pages/groups.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$user = currentUser();
$db   = getDB();
$currentPage = 'groups';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $maxMembers = intval($_POST['max_members'] ?? 20);
        $stmt = $db->prepare("INSERT INTO study_groups (creator_id, subject_id, name, description, max_members) VALUES (?,?,?,?,?)");
        $stmt->execute([$user['id'], $_POST['subject_id'] ?: null, clean($_POST['name']), clean($_POST['description'] ?? ''), $maxMembers]);
        $gid = $db->lastInsertId();
        $db->prepare("INSERT INTO group_members (group_id, user_id, role) VALUES (?,?,'admin')")->execute([$gid, $user['id']]);

        $memberIds = array_slice(array_unique(array_map('intval', $_POST['member_ids'] ?? [])), 0, max(0, $maxMembers - 1));
        if (!empty($memberIds)) {
            $validMembers = $db->prepare("SELECT id FROM users WHERE id IN (" . implode(',', array_fill(0, count($memberIds), '?')) . ") AND id != ?");
            $validMembers->execute(array_merge($memberIds, [$user['id']]));
            foreach ($validMembers->fetchAll(PDO::FETCH_COLUMN) as $memberId) {
                $db->prepare("INSERT IGNORE INTO group_members (group_id, user_id) VALUES (?,?)")->execute([$gid, $memberId]);
            }
        }
        header('Location: groups.php'); exit;
    }
    if ($action === 'join') {
        $gid = intval($_POST['group_id']);
        $check = $db->prepare("SELECT COUNT(*) FROM group_members WHERE group_id=? AND user_id=?");
        $check->execute([$gid, $user['id']]);
        if (!$check->fetchColumn()) {
            $db->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?,?)")->execute([$gid, $user['id']]);
        }
        header('Location: groups.php'); exit;
    }
    if ($action === 'leave') {
        $db->prepare("DELETE FROM group_members WHERE group_id=? AND user_id=?")->execute([intval($_POST['group_id']), $user['id']]);
        header('Location: groups.php'); exit;
    }
}

$myGroups = $db->prepare("
    SELECT g.*, gm.role as my_role,
        (SELECT COUNT(*) FROM group_members WHERE group_id=g.id) AS member_count,
        (SELECT name FROM users WHERE id=g.creator_id) AS creator_name,
        (SELECT GROUP_CONCAT(u.name SEPARATOR ', ')
            FROM group_members gm2
            JOIN users u ON gm2.user_id=u.id
            WHERE gm2.group_id=g.id
            ORDER BY u.name ASC) AS member_names,
        s.name AS subject_name
    FROM study_groups g
    JOIN group_members gm ON gm.group_id=g.id AND gm.user_id=?
    LEFT JOIN subjects s ON g.subject_id=s.id
    ORDER BY g.created_at DESC
");
$myGroups->execute([$user['id']]);
$myGroupList = $myGroups->fetchAll();

$discover = $db->prepare("
    SELECT g.*,
        (SELECT COUNT(*) FROM group_members WHERE group_id=g.id) AS member_count,
        (SELECT name FROM users WHERE id=g.creator_id) AS creator_name,
        s.name AS subject_name
    FROM study_groups g
    LEFT JOIN subjects s ON g.subject_id=s.id
    WHERE g.is_public=1 AND g.id NOT IN (SELECT group_id FROM group_members WHERE user_id=?)
    ORDER BY member_count DESC LIMIT 20
");
$discover->execute([$user['id']]);
$discoverList = $discover->fetchAll();

$subjectList = $db->prepare("SELECT id, name, code, year, semester FROM subjects WHERE user_id=? ORDER BY year ASC, semester ASC, name ASC");
$subjectList->execute([$user['id']]);
$subs = $subjectList->fetchAll();
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

$classmates = $db->prepare("SELECT id, name, email, semester FROM users WHERE id != ? AND role='student' ORDER BY ABS(semester - ?), name ASC LIMIT 20");
$classmates->execute([$user['id'], $user['semester']]);
$classmateList = $classmates->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Study Groups - EduSync MU</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.group-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:20px; margin-top:16px; }
.group-card { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:22px; transition:all .2s; }
.group-card:hover { border-color:var(--accent); }
.group-name { font-family:'Syne',sans-serif; font-size:16px; font-weight:700; margin-bottom:6px; }
.group-desc { font-size:13px; color:var(--muted); line-height:1.5; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; margin-bottom:12px; }
.group-meta { display:flex; align-items:center; gap:12px; font-size:12px; color:var(--muted); margin-bottom:14px; flex-wrap:wrap; }
.section-heading { font-family:'Syne',sans-serif; font-size:16px; font-weight:700; margin:28px 0 4px; }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:200; align-items:center; justify-content:center; }
.modal-overlay.active { display:flex; }
.modal { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:28px; width:100%; max-width:460px; }
.modal h3 { font-family:'Syne',sans-serif; font-size:18px; font-weight:700; margin-bottom:20px; }
.form-group { margin-bottom:16px; }
.hint-text { color:var(--muted); font-size:11px; margin-top:8px; }
</style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">Study Groups</div>
            <div class="page-sub">Collaborate with classmates and study together</div>
        </div>
        <button class="btn btn-primary" onclick="document.getElementById('createModal').classList.add('active')">+ Create Group</button>
    </div>

    <div class="section-heading">My Groups (<?= count($myGroupList) ?>)</div>
    <?php if (empty($myGroupList)): ?>
    <div class="card" style="text-align:center;padding:40px;margin-top:12px;">
        <div style="font-size:36px;margin-bottom:12px;">Groups</div>
        <div style="color:var(--muted);font-size:14px;">You haven't joined any groups yet. Create one or browse below.</div>
    </div>
    <?php else: ?>
    <div class="group-grid">
        <?php foreach ($myGroupList as $g): ?>
        <div class="group-card">
            <div class="group-name"><?= htmlspecialchars($g['name']) ?></div>
            <?php if ($g['description']): ?><div class="group-desc"><?= htmlspecialchars($g['description']) ?></div><?php endif; ?>
            <div class="group-meta">
                <span><?= $g['member_count'] ?>/<?= $g['max_members'] ?> members</span>
                <?php if ($g['subject_name']): ?><span><?= htmlspecialchars($g['subject_name']) ?></span><?php endif; ?>
                <span>by <?= htmlspecialchars($g['creator_name']) ?></span>
            </div>
            <?php if ($g['member_names']): ?>
            <div style="font-size:12px;color:var(--muted);margin-bottom:12px;">Members: <?= htmlspecialchars($g['member_names']) ?></div>
            <?php endif; ?>
            <div style="display:flex;gap:8px;">
                <?php if ($g['my_role'] === 'admin'): ?>
                <span class="badge badge-cyan">Admin</span>
                <?php endif; ?>
                <form method="POST" style="margin-left:auto;" onsubmit="return confirm('Leave this group?')">
                    <input type="hidden" name="action" value="leave">
                    <input type="hidden" name="group_id" value="<?= $g['id'] ?>">
                    <button class="btn btn-sm btn-outline">Leave</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($discoverList)): ?>
    <div class="section-heading" style="margin-top:36px;">Discover Groups</div>
    <div class="group-grid">
        <?php foreach ($discoverList as $g): ?>
        <div class="group-card">
            <div class="group-name"><?= htmlspecialchars($g['name']) ?></div>
            <?php if ($g['description']): ?><div class="group-desc"><?= htmlspecialchars($g['description']) ?></div><?php endif; ?>
            <div class="group-meta">
                <span><?= $g['member_count'] ?>/<?= $g['max_members'] ?></span>
                <?php if ($g['subject_name']): ?><span><?= htmlspecialchars($g['subject_name']) ?></span><?php endif; ?>
                <span>by <?= htmlspecialchars($g['creator_name']) ?></span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="join">
                <input type="hidden" name="group_id" value="<?= $g['id'] ?>">
                <button class="btn btn-sm btn-primary">+ Join Group</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>

<div class="modal-overlay" id="createModal">
    <div class="modal">
        <h3>Create Study Group</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="form-group">
                <label>Group Name *</label>
                <input type="text" name="name" id="groupName" list="groupNameList" required placeholder="e.g. DSA Study Group Batch 55">
                <datalist id="groupNameList"></datalist>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="groupDescription" rows="3" placeholder="What's this group about?"></textarea>
                <div class="hint-text" id="groupDescriptionHint">Pick a subject to get relevant group ideas.</div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label>Subject (optional)</label>
                    <select name="subject_id" id="groupSubject">
                        <option value="">-- None --</option>
                        <?php foreach ($subs as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars(($s['code'] ? $s['code'] . ': ' : '') . $s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Max Members</label>
                    <input type="number" name="max_members" value="20" min="2" max="100">
                </div>
            </div>
            <div class="form-group">
                <label>Add Members (optional)</label>
                <select name="member_ids[]" multiple size="5">
                    <?php foreach ($classmateList as $mate): ?>
                    <option value="<?= $mate['id'] ?>"><?= htmlspecialchars($mate['name'] . ' - Sem ' . $mate['semester'] . ' - ' . $mate['email']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="hint-text">Hold Ctrl or Cmd to choose multiple classmates to add right away.</div>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:10px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('createModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Group</button>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('createModal').addEventListener('click',function(e){if(e.target===this)this.classList.remove('active');});

const groupSubjectMeta = <?= json_encode($subjectMeta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const groupSubject = document.getElementById('groupSubject');
const groupNameList = document.getElementById('groupNameList');
const groupDescription = document.getElementById('groupDescription');
const groupDescriptionHint = document.getElementById('groupDescriptionHint');

function refreshGroupSuggestions() {
    const selected = groupSubjectMeta.find((item) => item.id === Number(groupSubject.value || 0)) || null;
    const label = selected ? (selected.code || selected.name) : 'Study';
    const names = [
        `${label} Study Circle`,
        `${label} Revision Squad`,
        `${label} Problem Solvers`,
        `${label} Exam Prep Team`,
        `${label} Lab Partners`
    ];
    const descriptions = [
        `Weekly ${label} discussion, revision, and problem solving together.`,
        `A small team for ${label} notes sharing, practice sessions, and exam prep.`,
        `Focused ${label} group for assignments, viva prep, and clearing weak topics.`,
        `Collaborative ${label} study group for class tasks, quizzes, and regular revision.`,
        `Friendly ${label} practice room for summaries, previous questions, and deadline support.`
    ];

    groupNameList.innerHTML = '';
    names.forEach((name) => {
        const option = document.createElement('option');
        option.value = name;
        groupNameList.appendChild(option);
    });

    const randomDescription = descriptions[Math.floor(Math.random() * descriptions.length)];
    groupDescriptionHint.textContent = randomDescription;
    if (!groupDescription.value.trim()) {
        groupDescription.value = randomDescription;
    }
}

groupSubject.addEventListener('change', refreshGroupSuggestions);
refreshGroupSuggestions();
</script>
</body>
</html>
