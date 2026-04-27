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

/* Modal Styles */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:200; align-items:center; justify-content:center; }
.modal-overlay.active { display:flex; }
.modal { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:28px; width:100%; max-width:440px; }
.modal h3 { font-family:'Syne',sans-serif; font-size:18px; font-weight:700; margin-bottom:20px; color:#fff; }
.form-group { margin-bottom:16px; }
.form-group label { display:block; margin-bottom:8px; font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:0.05em; }
.form-group input, .form-group select { width:100%; padding:10px 14px; border-radius:8px; border:1px solid var(--border); background:rgba(255,255,255,0.05); color:#fff; font-family:inherit; transition:border-color 0.2s; }
.form-group input:focus, .form-group select:focus { outline:none; border-color:var(--accent); }
</style>

<div class="modal-overlay" id="addTask">
    <div class="modal">
        <h3>📝 Add Task</h3>
        <form method="POST" action="/tasks">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Task Title *</label>
                <input type="text" name="title" required placeholder="e.g. Finish Chapter 3 Reading">
            </div>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label>Subject *</label>
                    <select name="subject_id" required>
                        <?php if (!empty($subjects)): ?>
                            <?php foreach ($subjects as $sub): ?>
                            <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?></option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">No subjects added yet</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="type">
                        <option value="assignment">Assignment</option>
                        <option value="project">Project</option>
                        <option value="exam">Exam</option>
                        <option value="reading">Reading</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Due Date</label>
                <input type="date" name="due_date" value="<?= date('Y-m-d', strtotime('+3 days')) ?>">
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:10px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('addTask')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Task</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('active');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

document.getElementById('addTask').addEventListener('click', function(e){
    if(e.target===this) this.classList.remove('active');
});
</script>
