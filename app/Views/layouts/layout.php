<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'EduSync MU'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <?php if (isset($extraStyles)) echo $extraStyles; ?>
</head>
<body>
    <div class="layout">
        <?php if (isset($user) && $user): ?>
            <!-- Sidebar -->
            <aside class="sidebar">
                <div class="sidebar-header">
                    <h1>EduSync</h1>
                </div>
                <nav class="sidebar-nav">
                    <a href="/dashboard" class="nav-link <?php echo ($currentPage ?? '') === 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
                    <a href="/subjects" class="nav-link <?php echo ($currentPage ?? '') === 'subjects' ? 'active' : ''; ?>">Subjects</a>
                    <a href="/tasks" class="nav-link <?php echo ($currentPage ?? '') === 'tasks' ? 'active' : ''; ?>">Tasks</a>
                    <a href="/grades" class="nav-link <?php echo ($currentPage ?? '') === 'grades' ? 'active' : ''; ?>">Grades</a>
                    <a href="/attendance" class="nav-link <?php echo ($currentPage ?? '') === 'attendance' ? 'active' : ''; ?>">Attendance</a>
                    <a href="/flashcards" class="nav-link <?php echo ($currentPage ?? '') === 'flashcards' ? 'active' : ''; ?>">Flashcards</a>
                    <a href="/groups" class="nav-link <?php echo ($currentPage ?? '') === 'groups' ? 'active' : ''; ?>">Groups</a>
                    <a href="/logout" class="nav-link">Logout</a>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <header class="top-bar">
                    <div class="user-info">
                        <span><?php echo htmlspecialchars($user['name'] ?? ''); ?></span>
                    </div>
                </header>

                <div class="content">
                    <?php if (isset($flash) && $flash): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>">
                            <?php echo htmlspecialchars($flash['message']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php echo $content; ?>
                </div>
            </main>
        <?php else: ?>
            <!-- Full width for auth pages -->
            <?php echo $content; ?>
        <?php endif; ?>
    </div>

    <script src="/assets/js/main.js"></script>
    <?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
