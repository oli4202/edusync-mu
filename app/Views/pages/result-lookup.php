<?php $currentPage = 'result-lookup'; ?>

<style>
.result-wrap { display:grid; gap:18px; }
.result-card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:20px; }
.result-title { font-family:'Syne',sans-serif; font-size:18px; font-weight:800; margin-bottom:6px; }
.result-sub { color:var(--muted); font-size:13px; }
.filter-grid { display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:12px; align-items:end; }
.field label { font-size:11px; color:var(--muted); text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px; display:block; }
.field input, .field select { width:100%; }
.sheet-table-wrap { overflow:auto; border:1px solid var(--border); border-radius:12px; }
.sheet-table { width:100%; border-collapse:collapse; min-width:920px; }
.sheet-table th { background:#0f172a; color:#94a3b8; font-size:11px; text-transform:uppercase; letter-spacing:.05em; padding:10px; border-bottom:1px solid var(--border); }
.sheet-table td { padding:10px; border-bottom:1px solid rgba(148,163,184,.12); font-size:13px; }
.sheet-table tbody tr:hover { background:rgba(34,211,238,.06); }
.sheet-row-active { background:rgba(34,211,238,.12) !important; }
.g-a { color:#34d399; font-weight:700; }
.g-b { color:#22d3ee; font-weight:700; }
.g-c { color:#fbbf24; font-weight:700; }
.g-d { color:#fb923c; font-weight:700; }
.g-f { color:#f87171; font-weight:700; }
.student-chip { display:flex; align-items:center; gap:8px; }
.avatar-sm { width:28px; height:28px; border-radius:999px; object-fit:cover; border:1px solid var(--border); }
.detail-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-top:10px; }
.detail-box { border:1px solid var(--border); border-radius:10px; padding:10px; background:rgba(255,255,255,.02); }
.detail-box .lbl { color:var(--muted); font-size:11px; text-transform:uppercase; letter-spacing:.05em; }
.detail-box .val { font-size:14px; font-weight:700; margin-top:4px; }
.subject-breakdown { margin-top:14px; border:1px solid var(--border); border-radius:10px; overflow:hidden; }
.subject-breakdown table { width:100%; border-collapse:collapse; }
.subject-breakdown th, .subject-breakdown td { padding:10px; border-bottom:1px solid rgba(148,163,184,.12); font-size:13px; text-align:left; }
@media (max-width: 980px) { .filter-grid { grid-template-columns:1fr; } .detail-grid { grid-template-columns:1fr 1fr; } }
</style>

<?php
$courses = $sheet['courses'] ?? [];
$rows = $sheet['students'] ?? [];
?>

<div class="result-wrap">
    <div class="result-card">
        <div class="result-title">Batch-Wise Semester Result Sheet</div>
        <div class="result-sub">Marks are normalized to 100 per subject. Click any student row or search by student ID to highlight and view full semester breakdown.</div>
    </div>

    <div class="result-card">
        <form method="GET" action="/result-lookup" class="filter-grid">
            <div class="field">
                <label>Batch</label>
                <select name="batch" required>
                    <option value="">Select batch</option>
                    <?php foreach ($batchOptions as $b): ?>
                        <option value="<?= htmlspecialchars($b) ?>" <?= $selectedBatch === $b ? 'selected' : '' ?>>Batch <?= htmlspecialchars($b) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Semester</label>
                <select name="semester" required>
                    <option value="">Select semester</option>
                    <?php foreach ($semesterOptions as $opt): ?>
                        <option value="<?= (int)$opt['value'] ?>" <?= $selectedSemester === (int)$opt['value'] ? 'selected' : '' ?>><?= htmlspecialchars($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Search Student ID</label>
                <input type="text" name="student_id" placeholder="e.g. 241-134-010" value="<?= htmlspecialchars($searchStudentId ?? '') ?>">
            </div>
            <button class="btn btn-primary" type="submit">Show Result Sheet</button>
        </form>
    </div>

    <?php if ($selectedBatch === '' || $selectedSemester <= 0): ?>
        <div class="result-card">
            <div class="result-sub">Select a batch and semester to view results.</div>
        </div>
    <?php elseif (empty($rows)): ?>
        <div class="result-card">
            <div class="result-sub">No grade records found for Batch <?= htmlspecialchars($selectedBatch) ?>, Semester <?= (int)$selectedSemester ?>.</div>
        </div>
    <?php else: ?>
        <div class="result-card">
            <div class="sheet-table-wrap">
                <table class="sheet-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <?php foreach ($courses as $course): ?>
                                <th><?= htmlspecialchars($course['code']) ?><br><span style="font-size:10px;color:#64748b;"><?= htmlspecialchars($course['name']) ?></span></th>
                            <?php endforeach; ?>
                            <th>Overall /100</th>
                            <th>Grade</th>
                            <th>CG</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <?php
                            $sid = (string)$row['student']['student_id'];
                            $isActive = ($searchStudentId !== '' && strcasecmp($sid, (string)$searchStudentId) === 0);
                            $gradeText = (string)$row['overall_grade'];
                            $gradeClass = 'g-f';
                            if (str_starts_with($gradeText, 'A')) $gradeClass = 'g-a';
                            elseif (str_starts_with($gradeText, 'B')) $gradeClass = 'g-b';
                            elseif (str_starts_with($gradeText, 'C')) $gradeClass = 'g-c';
                            elseif ($gradeText === 'D') $gradeClass = 'g-d';
                            ?>
                            <tr class="<?= $isActive ? 'sheet-row-active' : '' ?>" onclick="window.location.href='/result-lookup?batch=<?= urlencode($selectedBatch) ?>&semester=<?= (int)$selectedSemester ?>&student_id=<?= urlencode($sid) ?>'">
                                <td><strong><?= htmlspecialchars($sid) ?></strong></td>
                                <td>
                                    <div class="student-chip">
                                        <img class="avatar-sm" src="<?= htmlspecialchars(avatarUrl($row['student']['avatar'] ?? '', $row['student']['name'] ?? 'Student')) ?>" alt="<?= htmlspecialchars($row['student']['name'] ?? 'Student') ?>">
                                        <span><?= htmlspecialchars($row['student']['name']) ?></span>
                                    </div>
                                </td>
                                <?php
                                $subjectMap = [];
                                foreach ($row['subjects'] as $subject) {
                                    $subjectMap[$subject['code']] = $subject;
                                }
                                foreach ($courses as $course):
                                    $code = (string)$course['code'];
                                    $sub = $subjectMap[$code] ?? ['marks_100' => 0, 'grade' => 'F'];
                                    $subGrade = (string)$sub['grade'];
                                    $subClass = 'g-f';
                                    if (str_starts_with($subGrade, 'A')) $subClass = 'g-a';
                                    elseif (str_starts_with($subGrade, 'B')) $subClass = 'g-b';
                                    elseif (str_starts_with($subGrade, 'C')) $subClass = 'g-c';
                                    elseif ($subGrade === 'D') $subClass = 'g-d';
                                ?>
                                    <td style="text-align:center;"><?= number_format((float)$sub['marks_100'], 1) ?> <span class="<?= $subClass ?>"><?= htmlspecialchars($subGrade) ?></span></td>
                                <?php endforeach; ?>
                                <td style="text-align:center;"><strong><?= number_format((float)$row['overall_marks'], 2) ?></strong></td>
                                <td style="text-align:center;" class="<?= $gradeClass ?>"><?= htmlspecialchars($row['overall_grade']) ?></td>
                                <td style="text-align:center;"><?= number_format((float)$row['overall_gp'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (!empty($selectedRow)): ?>
            <?php
            $selectedGrade = (string)$selectedRow['overall_grade'];
            $selectedGradeClass = 'g-f';
            if (str_starts_with($selectedGrade, 'A')) $selectedGradeClass = 'g-a';
            elseif (str_starts_with($selectedGrade, 'B')) $selectedGradeClass = 'g-b';
            elseif (str_starts_with($selectedGrade, 'C')) $selectedGradeClass = 'g-c';
            elseif ($selectedGrade === 'D') $selectedGradeClass = 'g-d';
            ?>
            <div class="result-card">
                <div class="result-title">Highlighted Student Result</div>
                <div class="detail-grid">
                    <div class="detail-box"><div class="lbl">Student</div><div class="val"><?= htmlspecialchars($selectedRow['student']['name']) ?></div></div>
                    <div class="detail-box"><div class="lbl">Student ID</div><div class="val"><?= htmlspecialchars($selectedRow['student']['student_id']) ?></div></div>
                    <div class="detail-box"><div class="lbl">Overall Marks</div><div class="val"><?= number_format((float)$selectedRow['overall_marks'], 2) ?>/100</div></div>
                    <div class="detail-box"><div class="lbl">Overall Grade</div><div class="val <?= $selectedGradeClass ?>"><?= htmlspecialchars($selectedRow['overall_grade']) ?> (CG <?= number_format((float)$selectedRow['overall_gp'], 2) ?>)</div></div>
                </div>

                <div class="subject-breakdown">
                    <table>
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Marks /100</th>
                                <th>Grade</th>
                                <th>GP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($selectedRow['subjects'] as $sub): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($sub['code']) ?></strong> - <?= htmlspecialchars($sub['name']) ?></td>
                                    <td><?= number_format((float)$sub['marks_100'], 1) ?></td>
                                    <td><?= htmlspecialchars($sub['grade']) ?></td>
                                    <td><?= number_format((float)$sub['gp'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
