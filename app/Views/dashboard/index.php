<?php
/**
 * Dashboard page - dashboard/index.php
 * $user - current user object
 * $tasksDueCount - number of tasks due soon
 * $doneCount - number of completed tasks
 * $weekHours - study hours this week
 * $upcomingTasks - list of upcoming tasks
 * $recentQuestions - recent questions from question bank
 */
$currentPage = 'dashboard';
?>

<div class="dashboard">
    <h2>Welcome back, <?php echo htmlspecialchars($user['name'] ?? 'User'); ?></h2>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $tasksDueCount; ?></div>
                <div class="stat-label">Tasks Due Soon</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $doneCount; ?></div>
                <div class="stat-label">Completed Tasks</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($weekHours, 1); ?></div>
                <div class="stat-label">Hours This Week</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🔥</div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $user['streak'] ?? 0; ?></div>
                <div class="stat-label">Day Streak</div>
            </div>
        </div>
    </div>

    <h3>Upcoming Tasks</h3>
    <div class="tasks-list">
        <?php if (!empty($upcomingTasks)): ?>
            <?php foreach ($upcomingTasks as $task): ?>
                <div class="task-item" style="border-left: 4px solid <?php echo htmlspecialchars($task['color'] ?? '#818cf8'); ?>">
                    <div class="task-header">
                        <strong><?php echo htmlspecialchars($task['title']); ?></strong>
                        <span class="badge"><?php echo htmlspecialchars($task['subject_name'] ?? 'General'); ?></span>
                    </div>
                    <div class="task-due">Due: <?php echo date('M d, Y', strtotime($task['due_date'])); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="empty-state">No upcoming tasks. Great job! 🎉</p>
        <?php endif; ?>
    </div>

    <h3>Recent Questions</h3>
    <div class="questions-list">
        <?php if (!empty($recentQuestions)): ?>
            <?php foreach (array_slice($recentQuestions, 0, 4) as $q): ?>
                <div class="question-item">
                    <div class="question-text"><?php echo htmlspecialchars(substr($q['question_text'], 0, 80) . '...'); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="empty-state">No questions yet.</p>
        <?php endif; ?>
    </div>
</div>

<style>
.dashboard h2 {
    margin-bottom: 30px;
    color: #e2e8f0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: #111827;
    border: 1px solid #1e2d45;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    gap: 15px;
}

.stat-icon {
    font-size: 32px;
}

.stat-value {
    font-size: 24px;
    font-weight: 700;
    color: #22d3ee;
}

.stat-label {
    font-size: 12px;
    color: #64748b;
    margin-top: 4px;
}

.tasks-list {
    margin-bottom: 40px;
}

.task-item {
    background: #111827;
    border: 1px solid #1e2d45;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 10px;
}

.task-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.task-due {
    font-size: 12px;
    color: #64748b;
}

.badge {
    background: #1e2d45;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
}

.empty-state {
    color: #64748b;
    text-align: center;
    padding: 30px;
}
</style>
