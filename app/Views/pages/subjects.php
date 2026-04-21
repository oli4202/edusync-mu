<?php
$currentPage = 'subjects';
?>

<div class="page-header">
    <h2>My Subjects</h2>
    <button class="btn btn-primary" onclick="openModal('addSubject')">+ Add Subject</button>
</div>

<div class="subjects-grid">
    <?php if (!empty($subjects)): ?>
        <?php foreach ($subjects as $subject): ?>
            <div class="subject-card" style="border-left: 4px solid <?php echo htmlspecialchars($subject['color'] ?? '#818cf8'); ?>">
                <h3><?php echo htmlspecialchars($subject['name']); ?></h3>
                <p class="code"><?php echo htmlspecialchars($subject['code'] ?? ''); ?></p>
                <p class="semester">Semester <?php echo htmlspecialchars($subject['semester'] ?? 1); ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="empty-state">No subjects yet. Add one to get started!</p>
    <?php endif; ?>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.subjects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.subject-card {
    background: #111827;
    border: 1px solid #1e2d45;
    border-radius: 8px;
    padding: 20px;
}

.subject-card h3 {
    margin: 0 0 8px 0;
    color: #e2e8f0;
}

.subject-card .code {
    font-size: 12px;
    color: #64748b;
    margin: 0 0 8px 0;
}

.subject-card .semester {
    font-size: 12px;
    color: #64748b;
}
</style>
