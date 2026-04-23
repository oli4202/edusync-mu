<?php
$currentPage = 'grades';
?>

<div class="page-header">
    <h2>My Grades</h2>
    <a href="/result-lookup" class="result-link">MU Official Result</a>
</div>

<div class="grades-table">
    <?php if (!empty($grades)): ?>
        <table>
            <thead>
                <tr>
                    <th>Exam</th>
                    <th>Marks</th>
                    <th>Percentage</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grades as $grade): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($grade['exam_name']); ?></td>
                        <td><?php echo $grade['marks_obtained']; ?> / <?php echo $grade['total_marks']; ?></td>
                        <td>
                            <?php 
                                $percentage = ($grade['marks_obtained'] / $grade['total_marks']) * 100;
                                echo number_format($percentage, 1);
                            ?>%
                        </td>
                        <td><?php echo date('M d, Y', strtotime($grade['exam_date'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="empty-state">No grades recorded yet.</p>
    <?php endif; ?>
</div>

<style>
.grades-table {
    background: #111827;
    border: 1px solid #1e2d45;
    border-radius: 8px;
    overflow: hidden;
}

table {
    width: 100%;
    border-collapse: collapse;
}

.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 16px;
}

.result-link {
    display: inline-flex;
    align-items: center;
    padding: 10px 14px;
    border: 1px solid #1e2d45;
    border-radius: 8px;
    color: #22d3ee;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
}

.result-link:hover {
    border-color: #22d3ee;
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
</style>
