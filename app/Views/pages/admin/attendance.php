<?php $pageTitle = 'Manage Attendance — EduSync Admin'; ?>

<style>
.stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px;}
.stat{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:18px;}
.stat-val{font-family:'Syne',sans-serif;font-size:28px;font-weight:800;color:var(--accent);}
.stat-lbl{font-size:12px;color:var(--muted);margin-top:4px;}
.card-admin{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:24px;margin-bottom:20px;}
.card-title-admin{font-family:'Syne',sans-serif;font-size:16px;font-weight:700;margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid var(--border);}
.filter-bar-admin{display:flex;gap:14px;flex-wrap:wrap;align-items:flex-end;margin-bottom:20px;}
.filter-bar-admin label{font-size:12px;color:var(--muted);display:block;margin-bottom:4px;text-transform:uppercase;letter-spacing:.4px;}
.filter-bar-admin select, .filter-bar-admin input{background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:8px;padding:10px 14px;color:var(--text);font-size:14px;font-family:inherit;outline:none;min-width:200px;}
.student-table-admin{width:100%;border-collapse:collapse;}
.student-table-admin th{text-align:left;font-size:12px;color:var(--muted);padding:10px 12px;border-bottom:1px solid var(--border);text-transform:uppercase;letter-spacing:.5px;}
.student-table-admin td{padding:12px;border-bottom:1px solid rgba(30,45,69,.5);font-size:14px;}
.bulk-actions-admin{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;}
.bulk-btn-admin{padding:6px 14px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;border:1px solid var(--border);background:rgba(255,255,255,.03);color:var(--text);transition:all .2s;}
.bulk-btn-admin:hover{border-color:var(--accent);color:var(--accent);}
.bulk-btn-admin.active{border-color:var(--accent);background:rgba(34,211,238,.1);color:var(--accent);}
.history-table-admin{width:100%;border-collapse:collapse;font-size:13px;}
.history-table-admin th{text-align:left;font-size:11px;color:var(--muted);padding:8px 10px;border-bottom:1px solid var(--border);text-transform:uppercase;}
.history-table-admin td{padding:8px 10px;border-bottom:1px solid rgba(30,45,69,.4);}
.status-present{color:var(--accent3);} .status-absent{color:var(--danger);}
.status-late{color:var(--warn);} .status-excused{color:var(--accent2);}
@media(max-width:700px){.stats-grid{grid-template-columns:1fr;}.filter-bar-admin{flex-direction:column;}}
</style>

<div style="margin-bottom: 20px;">
    <a href="/admin" class="btn btn-outline btn-sm">← Back to Admin Panel</a>
</div>

<div style="margin-bottom: 20px;">
    <a href="/admin" class="btn btn-outline btn-sm">Back to Admin Panel</a>
    <a href="/admin/students" class="btn btn-outline btn-sm" style="margin-left:10px;">Student Lookup</a>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat"><div class="stat-val"><?= $stats['totalRecords'] ?></div><div class="stat-lbl">Total Records</div></div>
    <div class="stat"><div class="stat-val"><?= $stats['todayCount'] ?></div><div class="stat-lbl">Marked Today</div></div>
    <div class="stat"><div class="stat-val"><?= count($students) ?></div><div class="stat-lbl">Total Students</div></div>
</div>

<!-- Course & Date Selector -->
<div class="card-admin">
    <div class="card-title-admin">📅 Select Course & Date</div>
    <form method="GET" class="filter-bar-admin">
        <div>
            <label>Filter Batch</label>
            <select name="batch" id="batchSelect">
                <option value="">All Batches</option>
                <?php foreach ($availableBatches as $batch): ?>
                    <option value="<?= htmlspecialchars($batch) ?>" <?= $selBatch == $batch ? 'selected' : '' ?>>Batch <?= htmlspecialchars($batch) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Semester</label>
            <select name="semester" id="semesterSelect">
                <option value="">Select batch...</option>
                <?php if ($selSemester): ?>
                    <option value="<?= $selSemester ?>" selected>Semester <?= $selSemester ?></option>
                <?php endif; ?>
            </select>
        </div>
        <div>
            <label>Course</label>
            <select name="course_id" id="courseSelect" required>
                <option value="">Select semester...</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $selCourse == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['code'].' — '.$c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Date</label>
            <input type="date" name="class_date" value="<?= $selDate ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Load Students</button>
    </form>
</div>

<!-- Bulk Attendance Form -->
<?php if ($selCourse && !empty($students)): ?>
<div class="card-admin">
    <div class="card-title-admin">
        👥 Mark Attendance
        <span style="font-size:12px;color:var(--muted);font-weight:400;margin-left:10px;">
            <?php
            $selCourseName = '';
            foreach ($courses as $c) { if ($c['id'] == $selCourse) { $selCourseName = $c['code'].' — '.$c['name']; break; } }
            echo htmlspecialchars($selCourseName) . ' · ' . date('D, M j Y', strtotime($selDate));
            ?>
        </span>
    </div>

    <div class="bulk-actions-admin">
        <button type="button" class="bulk-btn-admin" onclick="setAll('present')">✅ All Present</button>
        <button type="button" class="bulk-btn-admin" onclick="setAll('absent')">❌ All Absent</button>
        <button type="button" class="bulk-btn-admin" onclick="setAll('late')">⏰ All Late</button>
        <a href="/admin/attendance/sheet?course_id=<?= $selCourse ?>&batch=<?= urlencode($selBatch) ?>&semester=<?= $selSemester ?>" class="bulk-btn-admin no-print" target="_blank" style="margin-left:auto; text-decoration:none;">🖨️ Printable Sheet</a>
    </div>

    <form method="POST">
        <input type="hidden" name="action" value="bulk_mark">
        <input type="hidden" name="course_id" value="<?= $selCourse ?>">
        <input type="hidden" name="class_date" value="<?= htmlspecialchars($selDate) ?>">
        <input type="hidden" name="batch" value="<?= htmlspecialchars($selBatch) ?>">
        <input type="hidden" name="semester" value="<?= htmlspecialchars((string) $selSemester) ?>">
        
        <table class="student-table-admin">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>ID</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($students as $s):
                    $existing = $existingAtt[$s['id']] ?? null;
                    $curStatus = $existing['status'] ?? 'present';
                    $curNote   = $existing['notes'] ?? '';
                ?>
                <tr>
                    <td style="color:var(--muted);font-size:12px;"><?= $i++ ?></td>
                    <td>
                        <div style="font-weight:500;"><?= htmlspecialchars($s['name']) ?></div>
                        <div style="font-size:11px;color:var(--muted);"><?= htmlspecialchars($s['email']) ?></div>
                        <?php if (!empty($s['memberships'])): ?>
                        <div style="font-size:11px;color:var(--muted);margin-top:4px;"><?= htmlspecialchars($s['memberships']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:13px;color:var(--muted);"><?= htmlspecialchars($s['student_id'] ?: '—') ?></td>
                    <td>
                        <select name="status[<?= $s['id'] ?>]" class="att-select">
                            <option value="present" <?= $curStatus === 'present' ? 'selected' : '' ?>>✅ Present</option>
                            <option value="absent"  <?= $curStatus === 'absent'  ? 'selected' : '' ?>>❌ Absent</option>
                            <option value="late"    <?= $curStatus === 'late'    ? 'selected' : '' ?>>⏰ Late</option>
                            <option value="excused" <?= $curStatus === 'excused' ? 'selected' : '' ?>>📋 Excused</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" name="note[<?= $s['id'] ?>]" value="<?= htmlspecialchars($curNote) ?>" placeholder="Optional note..." style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:6px;padding:6px 10px;color:var(--text);font-size:13px;width:100%;outline:none;">
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top:18px;display:flex;gap:10px;">
            <button type="submit" class="btn btn-primary">💾 Save Attendance</button>
            <span style="font-size:13px;color:var(--muted);padding:10px;"><?= count($students) ?> students</span>
        </div>
    </form>
</div>
<?php elseif ($selCourse && empty($students)): ?>
<div class="card-admin"><div class="empty">No students registered yet.</div></div>
<?php endif; ?>

<!-- Recent History -->
<?php if ($selCourse && !empty($recentHistory)): ?>
<div class="card-admin">
    <div class="card-title-admin">📊 Recent Attendance History</div>
    <table class="history-table-admin">
        <thead>
            <tr><th>Date</th><th>Student</th><th>ID</th><th>Status</th><th>Notes</th></tr>
        </thead>
        <tbody>
            <?php
            $lastDate = '';
            foreach ($recentHistory as $r):
                $dateStr = date('D, M j', strtotime($r['class_date']));
                $showDate = ($dateStr !== $lastDate);
                $lastDate = $dateStr;
            ?>
            <tr>
                <td><?= $showDate ? '<strong>'.$dateStr.'</strong>' : '' ?></td>
                <td><?= htmlspecialchars($r['student_name']) ?></td>
                <td style="color:var(--muted);"><?= htmlspecialchars($r['sid'] ?: '—') ?></td>
                <td class="status-<?= $r['status'] ?>" style="font-weight:600;"><?= ucfirst($r['status']) ?></td>
                <td style="color:var(--muted);"><?= htmlspecialchars($r['notes'] ?: '—') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<script>
function setAll(status) {
    document.querySelectorAll('.att-select').forEach(sel => sel.value = status);
    document.querySelectorAll('.bulk-btn-admin').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
}

document.getElementById('batchSelect').addEventListener('change', function() {
    const batch = this.value;
    const semSelect = document.getElementById('semesterSelect');
    const courseSelect = document.getElementById('courseSelect');
    
    semSelect.innerHTML = '<option value="">Loading...</option>';
    courseSelect.innerHTML = '<option value="">Select semester...</option>';

    if (!batch) {
        semSelect.innerHTML = '<option value="">Select batch...</option>';
        return;
    }

    fetch(`/api/courses/semesters?batch=${batch}`)
        .then(res => res.json())
        .then(data => {
            semSelect.innerHTML = '<option value="">Select semester...</option>';
            data.forEach(sem => {
                const opt = document.createElement('option');
                opt.value = sem;
                opt.textContent = `Semester ${sem}`;
                semSelect.appendChild(opt);
            });
            // If we have a selected semester, trigger its change
            if (semSelect.value) semSelect.dispatchEvent(new Event('change'));
        });
});

document.getElementById('semesterSelect').addEventListener('change', function() {
    const batch = document.getElementById('batchSelect').value;
    const semester = this.value;
    const courseSelect = document.getElementById('courseSelect');

    if (!batch || !semester) {
        courseSelect.innerHTML = '<option value="">Select semester...</option>';
        return;
    }

    courseSelect.innerHTML = '<option value="">Loading courses...</option>';

    fetch(`/api/courses/filter?batch=${batch}&semester=${semester}`)
        .then(res => res.json())
        .then(data => {
            courseSelect.innerHTML = '<option value="">Select course...</option>';
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = `${c.code} — ${c.name}`;
                courseSelect.appendChild(opt);
            });
        });
});

// Initialize on page load if batch is selected
if (document.getElementById('batchSelect').value) {
    const batch = document.getElementById('batchSelect').value;
    const currentSem = "<?= $selSemester ?>";
    const currentCourse = "<?= $selCourse ?>";

    fetch(`/api/courses/semesters?batch=${batch}`)
        .then(res => res.json())
        .then(data => {
            const semSelect = document.getElementById('semesterSelect');
            semSelect.innerHTML = '<option value="">Select semester...</option>';
            data.forEach(sem => {
                const opt = document.createElement('option');
                opt.value = sem;
                opt.textContent = `Semester ${sem}`;
                if (sem == currentSem) opt.selected = true;
                semSelect.appendChild(opt);
            });

            if (currentSem) {
                fetch(`/api/courses/filter?batch=${batch}&semester=${currentSem}`)
                    .then(res => res.json())
                    .then(data => {
                        const courseSelect = document.getElementById('courseSelect');
                        courseSelect.innerHTML = '<option value="">Select course...</option>';
                        data.forEach(c => {
                            const opt = document.createElement('option');
                            opt.value = c.id;
                            opt.textContent = `${c.code} — ${c.name}`;
                            if (c.id == currentCourse) opt.selected = true;
                            courseSelect.appendChild(opt);
                        });
                    });
            }
        });
}
</script>
