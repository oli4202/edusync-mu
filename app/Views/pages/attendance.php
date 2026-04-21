<?php
$currentPage = 'attendance';
?>

<div class="page-header">
    <h2>Attendance</h2>
</div>

<div class="attendance-table">
    <?php if (!empty($attendance)): ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Course</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance as $record): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($record['class_date'])); ?></td>
                        <td>Course <?php echo htmlspecialchars($record['course_id']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo htmlspecialchars($record['status']); ?>">
                                <?php echo ucfirst($record['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($record['notes'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="empty-state">No attendance records yet.</p>
    <?php endif; ?>
</div>

<style>
.attendance-table {
    background: #111827;
    border: 1px solid #1e2d45;
    border-radius: 8px;
    overflow: hidden;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: #0f172a;
}

th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #64748b;
}

td {
    padding: 15px;
    border-top: 1px solid #1e2d45;
    color: #e2e8f0;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.badge-present {
    background: rgba(52, 211, 153, 0.2);
    color: #34d399;
}

.badge-absent {
    background: rgba(244, 63, 94, 0.2);
    color: #f87171;
}

.badge-late {
    background: rgba(251, 191, 36, 0.2);
    color: #fbbf24;
}

.badge-excused {
    background: rgba(34, 211, 238, 0.2);
    color: #22d3ee;
}
</style>
