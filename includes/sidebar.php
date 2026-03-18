<?php
// includes/sidebar.php — shared sidebar
// Expects $currentPage variable to be set before including
$currentPage = $currentPage ?? '';
$user = $user ?? currentUser();
?>
<aside class="sidebar">
    <div class="sidebar-logo">EduSync</div>
    <div class="sidebar-sub">MU SYLHET · SE DEPT</div>

    <div class="nav-section">Main</div>
    <a href="dashboard.php"    class="nav-item <?= $currentPage==='dashboard'?'active':'' ?>"><span class="icon">🏠</span> Dashboard</a>
    <a href="tasks.php"        class="nav-item <?= $currentPage==='tasks'?'active':'' ?>"><span class="icon">✅</span> Tasks & Kanban</a>
    <a href="subjects.php"     class="nav-item <?= $currentPage==='subjects'?'active':'' ?>"><span class="icon">📚</span> Subjects</a>
    <a href="analytics.php"    class="nav-item <?= $currentPage==='analytics'?'active':'' ?>"><span class="icon">📊</span> Analytics</a>

    <div class="nav-section">Student Portal</div>
    <a href="attendance.php"   class="nav-item <?= $currentPage==='attendance'?'active':'' ?>"><span class="icon">🗓</span> Attendance</a>
    <a href="grades.php"       class="nav-item <?= $currentPage==='grades'?'active':'' ?>"><span class="icon">🎓</span> Grades & Results</a>
    <a href="result-lookup.php" class="nav-item <?= $currentPage==='result-lookup'?'active':'' ?>"><span class="icon">🔍</span> MU Result Lookup</a>
    <a href="announcements.php" class="nav-item <?= $currentPage==='announcements'?'active':'' ?>"><span class="icon">📢</span> Announcements</a>

    <div class="nav-section">Social</div>
    <a href="groups.php"       class="nav-item <?= $currentPage==='groups'?'active':'' ?>"><span class="icon">👥</span> Study Groups</a>
    <a href="partners.php"     class="nav-item <?= $currentPage==='partners'?'active':'' ?>"><span class="icon">🔍</span> Find Partners</a>

    <div class="nav-section">AI & Study</div>
    <a href="ai.php"           class="nav-item <?= $currentPage==='ai'?'active':'' ?>"><span class="icon">🤖</span> AI Assistant</a>
    <a href="flashcards.php"   class="nav-item <?= $currentPage==='flashcards'?'active':'' ?>"><span class="icon">🃏</span> Flashcards</a>
    <a href="question-bank.php" class="nav-item <?= $currentPage==='question-bank'?'active':'' ?>"><span class="icon">📖</span> Question Bank</a>
    <a href="suggestions.php"  class="nav-item <?= $currentPage==='suggestions'?'active':'' ?>"><span class="icon">💡</span> Exam Suggestions</a>
    <a href="learn.php"        class="nav-item <?= $currentPage==='learn'?'active':'' ?>"><span class="icon">▶️</span> YouTube Learning</a>
    <a href="playground.php"   class="nav-item <?= $currentPage==='playground'?'active':'' ?>"><span class="icon">⚡</span> Code Playground</a>

    <div class="nav-section">Career</div>
    <a href="jobs.php"         class="nav-item <?= $currentPage==='jobs'?'active':'' ?>"><span class="icon">💼</span> Internship & Jobs</a>

    <div class="nav-section">Finance</div>
    <a href="fees.php"         class="nav-item <?= $currentPage==='fees'?'active':'' ?>"><span class="icon">💳</span> Fee Payments</a>

    <?php if (($user['role'] ?? '') === 'admin'): ?>
    <div class="nav-section">Admin</div>
    <a href="../admin/index.php" class="nav-item"><span class="icon">🛡️</span> Admin Panel</a>
    <?php endif; ?>

    <div class="sidebar-footer">
        <div class="user-chip">
            <div class="avatar"><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></div>
            <div>
                <div class="user-name"><?= htmlspecialchars($user['name'] ?? '') ?></div>
                <div class="user-streak">🔥 <?= $user['streak'] ?? 0 ?> day streak</div>
            </div>
        </div>
        <a href="logout.php" style="display:block;margin-top:12px;font-size:12px;color:var(--muted);text-decoration:none;text-align:center;">⬅ Logout</a>
    </div>
</aside>
