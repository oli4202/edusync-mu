<?php $pageTitle = 'Manage Attendance — EduSync Admin'; ?>

<style>
.stats-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:24px;}
.stat{background:linear-gradient(145deg,rgba(255,255,255,.04),rgba(255,255,255,.02));border:1px solid rgba(148,163,184,.25);border-radius:14px;padding:18px 18px 16px;box-shadow:0 12px 30px rgba(2,6,23,.2);}
.stat-val{font-family:'Syne',sans-serif;font-size:30px;font-weight:800;color:#67e8f9;line-height:1;}
.stat-lbl{font-size:12px;color:#94a3b8;margin-top:8px;letter-spacing:.04em;text-transform:uppercase;}
.card-admin{background:linear-gradient(180deg,rgba(15,23,42,.84),rgba(15,23,42,.74));border:1px solid rgba(148,163,184,.22);border-radius:18px;padding:24px;margin-bottom:18px;box-shadow:0 20px 40px rgba(2,6,23,.18);}
.card-title-admin{font-family:'Syne',sans-serif;font-size:17px;font-weight:700;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid rgba(148,163,184,.22);}
.filter-bar-admin{display:grid;grid-template-columns:repeat(4,minmax(170px,1fr)) auto;gap:14px;align-items:end;margin-bottom:18px;}
.filter-bar-admin label{font-size:11px;color:#94a3b8;display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:.08em;font-weight:700;}
.filter-bar-admin select,.filter-bar-admin input{background:rgba(15,23,42,.75);border:1px solid rgba(148,163,184,.3);border-radius:10px;padding:11px 14px;color:#e2e8f0;font-size:14px;font-family:inherit;outline:none;width:100%;transition:border-color .2s,box-shadow .2s;}
.filter-bar-admin select:focus,.filter-bar-admin input:focus{border-color:#22d3ee;box-shadow:0 0 0 3px rgba(34,211,238,.18);}
.student-table-admin,.history-table-admin{width:100%;border-collapse:collapse;border-radius:12px;overflow:hidden;}
.student-table-admin th,.history-table-admin th{text-align:left;font-size:11px;color:#94a3b8;padding:11px 12px;border-bottom:1px solid rgba(148,163,184,.22);background:rgba(30,41,59,.45);text-transform:uppercase;letter-spacing:.08em;}
.student-table-admin td,.history-table-admin td{padding:12px;border-bottom:1px solid rgba(148,163,184,.12);font-size:14px;}
.student-table-admin tbody tr:hover,.history-table-admin tbody tr:hover{background:rgba(148,163,184,.08);}
.bulk-actions-admin{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;}
.bulk-btn-admin{padding:8px 14px;border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;border:1px solid rgba(148,163,184,.3);background:rgba(15,23,42,.72);color:#cbd5e1;transition:all .2s;}
.bulk-btn-admin:hover{border-color:#22d3ee;color:#67e8f9;}
.bulk-btn-admin.active{border-color:#22d3ee;background:rgba(34,211,238,.14);color:#67e8f9;}
.status-present{color:#34d399;} .status-absent{color:#f87171;} .status-late{color:#fbbf24;} .status-excused{color:#60a5fa;}
.action-row{display:flex;gap:12px;align-items:center;flex-wrap:wrap;}
.btn-outline[disabled]{opacity:.6;cursor:not-allowed;}
.student-avatar-admin{width:30px;height:30px;border-radius:999px;object-fit:cover;border:1px solid rgba(148,163,184,.35);margin-right:8px;vertical-align:middle;}
@media(max-width:900px){.filter-bar-admin{grid-template-columns:1fr 1fr;}.stats-grid{grid-template-columns:1fr;}}
@media(max-width:640px){.filter-bar-admin{grid-template-columns:1fr;}}
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
                    <option value="<?= $selSemester ?>" selected>Semester <?= $selSemester ?> (<?= htmlspecialchars(\App\Models\Course::getSemesterTerm((int)$selSemester)) ?>)</option>
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

    <form method="POST" class="action-row" style="margin-top:10px;">
        <input type="hidden" name="action" value="random_results">
        <input type="hidden" name="batch" value="<?= htmlspecialchars($selBatch) ?>">
        <input type="hidden" name="semester" value="<?= htmlspecialchars((string)$selSemester) ?>">
        <button type="submit" class="btn btn-outline" <?= (!$selBatch || !$selSemester) ? 'disabled' : '' ?>>
            Generate Random Results For This Batch+Semester
        </button>
        <span style="font-size:12px;color:#94a3b8;">Type 3 UI: grouped by batch and semester with auto-labeled terms.</span>
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
        <a href="/admin/attendance/details?course_id=<?= $selCourse ?>&batch=<?= urlencode($selBatch) ?>&semester=<?= $selSemester ?>" class="bulk-btn-admin no-print" style="text-decoration:none; border-color: #22d3ee; color: #22d3ee; margin-left:auto;">📊 Full Assessment Grid</a>
        <a href="/admin/attendance/sheet?course_id=<?= $selCourse ?>&batch=<?= urlencode($selBatch) ?>&semester=<?= $selSemester ?>" class="bulk-btn-admin no-print" target="_blank" style="text-decoration:none;">🖨️ Printable Sheet</a>
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
                        <img class="student-avatar-admin" src="<?= htmlspecialchars(avatarUrl($s['avatar'] ?? '', $s['name'] ?? 'Student')) ?>" alt="<?= htmlspecialchars($s['name'] ?? 'Student') ?>">
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
                <td><img class="student-avatar-admin" src="<?= htmlspecialchars(avatarUrl($r['student_avatar'] ?? '', $r['student_name'] ?? 'Student')) ?>" alt="<?= htmlspecialchars($r['student_name'] ?? 'Student') ?>"><?= htmlspecialchars($r['student_name']) ?></td>
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
                opt.value = sem.value;
                opt.textContent = sem.label;
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
                opt.value = sem.value;
                opt.textContent = sem.label;
                if (String(sem.value) === String(currentSem)) opt.selected = true;
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
