<?php
// includes/sidebar.php - shared sidebar
// Expects $currentPage variable to be set before including
$currentPage = $currentPage ?? '';
$user = $user ?? currentUser();
?>
<aside class="sidebar">
    <div class="sidebar-head">
        <div class="sidebar-logo">EduSync</div>
        <div class="sidebar-sub">MU SYLHET · SE DEPT</div>
    </div>

    <div class="sidebar-nav">
        <div class="nav-group">
            <div class="nav-section">Main</div>
            <a href="dashboard.php" class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                <span class="icon">🏠</span><span class="nav-copy">Dashboard</span>
            </a>
            <a href="tasks.php" class="nav-item <?= $currentPage === 'tasks' ? 'active' : '' ?>">
                <span class="icon">✅</span><span class="nav-copy">Tasks & Kanban</span>
            </a>
            <a href="subjects.php" class="nav-item <?= $currentPage === 'subjects' ? 'active' : '' ?>">
                <span class="icon">📚</span><span class="nav-copy">Subjects</span>
            </a>
            <a href="analytics.php" class="nav-item <?= $currentPage === 'analytics' ? 'active' : '' ?>">
                <span class="icon">📊</span><span class="nav-copy">Analytics</span>
            </a>
        </div>

        <div class="nav-group">
            <div class="nav-section">Student Portal</div>
            <a href="attendance.php" class="nav-item <?= $currentPage === 'attendance' ? 'active' : '' ?>">
                <span class="icon">🗓</span><span class="nav-copy">Attendance</span>
            </a>
            <a href="grades.php" class="nav-item <?= $currentPage === 'grades' ? 'active' : '' ?>">
                <span class="icon">🎓</span><span class="nav-copy">Grades & Results</span>
            </a>
            <a href="result-lookup.php" class="nav-item <?= $currentPage === 'result-lookup' ? 'active' : '' ?>">
                <span class="icon">🔍</span><span class="nav-copy">MU Result Lookup</span>
            </a>
            <a href="announcements.php" class="nav-item <?= $currentPage === 'announcements' ? 'active' : '' ?>">
                <span class="icon">📢</span><span class="nav-copy">Announcements</span>
            </a>
            <a href="prospectus.php" class="nav-item <?= $currentPage === 'prospectus' ? 'active' : '' ?>">
                <span class="icon">📘</span><span class="nav-copy">Prospectus</span>
            </a>
        </div>

        <div class="nav-group">
            <div class="nav-section">Finance</div>
            <a href="fees.php" class="nav-item <?= $currentPage === 'fees' ? 'active' : '' ?>">
                <span class="icon">💳</span><span class="nav-copy">Fee Payments</span>
            </a>
        </div>

        <div class="nav-group">
            <div class="nav-section">Social</div>
            <a href="groups.php" class="nav-item <?= $currentPage === 'groups' ? 'active' : '' ?>">
                <span class="icon">👥</span><span class="nav-copy">Study Groups</span>
            </a>
            <a href="partners.php" class="nav-item <?= $currentPage === 'partners' ? 'active' : '' ?>">
                <span class="icon">🤝</span><span class="nav-copy">Find Partners</span>
            </a>
        </div>

        <div class="nav-group">
            <div class="nav-section">AI & Study</div>
            <a href="ai.php" class="nav-item <?= $currentPage === 'ai' ? 'active' : '' ?>">
                <span class="icon">🤖</span><span class="nav-copy">AI Assistant</span>
            </a>
            <a href="flashcards.php" class="nav-item <?= $currentPage === 'flashcards' ? 'active' : '' ?>">
                <span class="icon">🃏</span><span class="nav-copy">Flashcards</span>
            </a>
            <a href="question-bank.php" class="nav-item <?= $currentPage === 'question-bank' ? 'active' : '' ?>">
                <span class="icon">📖</span><span class="nav-copy">Question Bank</span>
            </a>
            <a href="suggestions.php" class="nav-item <?= $currentPage === 'suggestions' ? 'active' : '' ?>">
                <span class="icon">💡</span><span class="nav-copy">Exam Suggestions</span>
            </a>
            <a href="learn.php" class="nav-item <?= $currentPage === 'learn' ? 'active' : '' ?>">
                <span class="icon">▶</span><span class="nav-copy">YouTube Learning</span>
            </a>
            <a href="playground.php" class="nav-item <?= $currentPage === 'playground' ? 'active' : '' ?>">
                <span class="icon">⚡</span><span class="nav-copy">Code Playground</span>
            </a>
        </div>

        <div class="nav-group">
            <div class="nav-section">Career</div>
            <a href="jobs.php" class="nav-item <?= $currentPage === 'jobs' ? 'active' : '' ?>">
                <span class="icon">💼</span><span class="nav-copy">Internship & Jobs</span>
            </a>
        </div>

        <?php if (($user['role'] ?? '') === 'admin'): ?>
        <div class="nav-group">
            <div class="nav-section">Admin</div>
            <a href="../admin/index.php" class="nav-item">
                <span class="icon">🛡</span><span class="nav-copy">Admin Panel</span>
            </a>
            <a href="../admin/manage-attendance.php" class="nav-item">
                <span class="icon">📋</span><span class="nav-copy">Manage Attendance</span>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-chip">
                <img class="avatar" src="<?= htmlspecialchars(avatarUrl($user['avatar'] ?? '', $user['name'] ?? 'User')) ?>" alt="<?= htmlspecialchars($user['name'] ?? 'User') ?>">
                <div class="user-copy">
                    <div class="user-name"><?= htmlspecialchars($user['name'] ?? 'User') ?></div>
                    <div class="user-role"><?= htmlspecialchars($user['department'] ?? 'Software Engineering') ?></div>
                </div>
            </div>
            <div class="user-streak">🔥 <?= (int) ($user['streak'] ?? 0) ?> day streak</div>
        </div>
        <a href="logout.php" class="logout-link">⬅ Logout</a>
    </div>
</aside>
