<?php
$currentPage = 'tasks';
?>

<div class="page-header">
    <h2>My Tasks</h2>
    <button class="btn btn-primary" onclick="openModal('addTask')">+ Add Task</button>
</div>

<div class="tasks-container">
    <div class="task-filter">
        <button class="filter-btn active">All</button>
        <button class="filter-btn">Pending</button>
        <button class="filter-btn">Completed</button>
    </div>

    <div class="tasks-list">
        <?php if (!empty($tasks)): ?>
            <?php foreach ($tasks as $task): ?>
                <div class="task-row">
                    <div class="task-checkbox">
                        <input type="checkbox" <?php echo $task['status'] === 'done' ? 'checked' : ''; ?>>
                    </div>
                    <div class="task-content">
                        <h4><?php echo htmlspecialchars($task['title']); ?></h4>
                        <p><?php echo htmlspecialchars($task['description'] ?? ''); ?></p>
                    </div>
                    <div class="task-date">
                        <?php echo $task['due_date'] ? date('M d', strtotime($task['due_date'])) : 'No date'; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="empty-state">No tasks yet. Create one to get started!</p>
        <?php endif; ?>
    </div>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.task-filter {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.filter-btn {
    padding: 8px 16px;
    background: transparent;
    border: 1px solid #1e2d45;
    border-radius: 4px;
    color: #64748b;
    cursor: pointer;
}

.filter-btn.active {
    background: #22d3ee;
    border-color: #22d3ee;
    color: #0a0e1a;
}

.task-row {
    display: flex;
    gap: 15px;
    align-items: center;
    background: #111827;
    border: 1px solid #1e2d45;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 10px;
}

.task-checkbox {
    flex-shrink: 0;
}

.task-content {
    flex: 1;
}

.task-content h4 {
    margin: 0 0 5px 0;
}

.task-content p {
    margin: 0;
    font-size: 13px;
    color: #64748b;
}

.task-date {
    flex-shrink: 0;
    color: #64748b;
    font-size: 13px;
}
</style>
