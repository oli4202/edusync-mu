<?php $pageTitle = 'Student Attendance History — EduSync'; ?>

<style>
.summary-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-bottom:18px;}
.summary-card{background:rgba(15,23,42,.75);border:1px solid rgba(148,163,184,.2);border-radius:12px;padding:14px;}
.summary-label{font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;}
.summary-value{font-size:22px;font-weight:800;color:#e2e8f0;margin-top:4px;}
.history-table{width:100%;border-collapse:collapse;}
.history-table th,.history-table td{padding:10px 12px;border-bottom:1px solid rgba(148,163,184,.15);text-align:left;}
.history-table th{font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;}
.tag{padding:4px 8px;border-radius:999px;font-size:11px;font-weight:700;}
.tag-present{background:rgba(52,211,153,.18);color:#34d399;}
.tag-late{background:rgba(251,191,36,.18);color:#fbbf24;}
.tag-absent{background:rgba(248,113,113,.18);color:#f87171;}
.tag-excused{background:rgba(96,165,250,.18);color:#60a5fa;}
@media(max-width:900px){.summary-grid{grid-template-columns:1fr 1fr;}}
</style>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-syne text-2xl font-bold text-white">Student Previous Attendance</h1>
            <p class="text-sm text-slate-400 mt-1">
                <?= htmlspecialchars($student['name'] ?? '') ?> (<?= htmlspecialchars($student['student_id'] ?? 'N/A') ?>)
                • <?= htmlspecialchars($course['code'] ?? '') ?> — <?= htmlspecialchars($course['name'] ?? '') ?>
            </p>
        </div>
        <a href="/admin/attendance?course_id=<?= (int)($course['id'] ?? 0) ?>&batch=<?= urlencode((string)$batch) ?>&semester=<?= (int)$semester ?>" class="btn btn-outline btn-sm">← Back to Attendance</a>
    </div>

    <div class="summary-grid">
        <div class="summary-card"><div class="summary-label">Total Classes</div><div class="summary-value"><?= (int)$totalClasses ?></div></div>
        <div class="summary-card"><div class="summary-label">Present</div><div class="summary-value" style="color:#34d399;"><?= (int)$presentCount ?></div></div>
        <div class="summary-card"><div class="summary-label">Late</div><div class="summary-value" style="color:#fbbf24;"><?= (int)$lateCount ?></div></div>
        <div class="summary-card"><div class="summary-label">Absent</div><div class="summary-value" style="color:#f87171;"><?= (int)$absentCount ?></div></div>
        <div class="summary-card"><div class="summary-label">Attendance Rate</div><div class="summary-value"><?= htmlspecialchars((string)$attendanceRate) ?>%</div></div>
    </div>

    <div class="glass-card p-4">
        <table class="history-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($history)): ?>
                    <tr><td colspan="3" style="color:#94a3b8;">No previous attendance records found for this student in this course.</td></tr>
                <?php else: ?>
                    <?php foreach ($history as $row): ?>
                        <?php
                        $status = (string)($row['status'] ?? '');
                        $class = 'tag';
                        if ($status === 'present') $class .= ' tag-present';
                        if ($status === 'late') $class .= ' tag-late';
                        if ($status === 'absent') $class .= ' tag-absent';
                        if ($status === 'excused') $class .= ' tag-excused';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars(date('D, M j, Y', strtotime((string)$row['class_date']))) ?></td>
                            <td><span class="<?= htmlspecialchars($class) ?>"><?= htmlspecialchars(ucfirst($status)) ?></span></td>
                            <td style="color:#94a3b8;"><?= htmlspecialchars((string)($row['notes'] ?? '—')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
