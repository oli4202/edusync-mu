<?php $pageTitle = 'Attendance Sheet — EduSync Admin'; ?>

<style>
@media print {
    .no-print { display: none !important; }
    body { background: white !important; color: black !important; padding: 0 !important; }
    .sheet-container { border: none !important; box-shadow: none !important; padding: 0 !important; width: 100% !important; margin: 0 !important; }
    table { width: 100% !important; border-collapse: collapse !important; }
    th, td { border: 1px solid black !important; padding: 6px 10px !important; }
}

body { font-family: 'DM Sans', sans-serif; background: #0a0e1a; color: #e2e8f0; padding: 40px 20px; }
.sheet-container { max-width: 900px; margin: 0 auto; background: white; color: #111; padding: 40px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); }
.sheet-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
.university-name { font-family: 'Syne', sans-serif; font-size: 24px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
.dept-name { font-size: 16px; font-weight: 600; color: #555; margin-bottom: 15px; }
.sheet-title { font-size: 20px; font-weight: 700; text-decoration: underline; margin-bottom: 20px; }

.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px; text-align: left; }
.info-item { font-size: 14px; }
.info-label { font-weight: 700; color: #333; min-width: 100px; display: inline-block; }
.info-value { border-bottom: 1px dotted #333; padding: 0 5px; flex-grow: 1; }

.attendance-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
.attendance-table th { background: #f3f4f6; border: 1px solid #333; padding: 10px; font-size: 13px; text-transform: uppercase; }
.attendance-table td { border: 1px solid #333; padding: 8px 10px; font-size: 14px; }
.col-num { width: 40px; text-align: center; }
.col-id { width: 120px; }
.col-name { text-align: left; }
.col-sign { width: 150px; }

.footer { margin-top: 50px; display: flex; justify-content: space-between; }
.signature-box { border-top: 1px solid #333; width: 200px; text-align: center; padding-top: 8px; font-size: 13px; font-weight: 600; }

.btn-print { background: #4f46e5; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; margin-bottom: 20px; }
.btn-print:hover { background: #4338ca; }
</style>

<div class="no-print" style="max-width: 900px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
    <a href="/admin/attendance?course_id=<?= $course['id'] ?? '' ?>&batch=<?= urlencode($selBatch) ?>&semester=<?= $selSemester ?>" style="color: var(--accent); text-decoration: none; font-size: 14px;">← Back to Manager</a>
    <button onclick="window.print()" class="btn-print">🖨️ Print Attendance Sheet</button>
</div>

<div class="sheet-container">
    <div class="sheet-header">
        <div class="university-name">Metropolitan University</div>
        <div class="dept-name">Department of Software Engineering</div>
        <div class="sheet-title">STUDENT ATTENDANCE SHEET</div>

        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Subject:</span>
                <span class="info-value"><?= htmlspecialchars($course['name'] ?? '___________________________') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Subject Code:</span>
                <span class="info-value"><?= htmlspecialchars($course['code'] ?? '___________________________') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Semester:</span>
                <span class="info-value"><?= $selSemester ?: (isset($course['semester']) ? $course['semester'] : '________') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Batch:</span>
                <span class="info-value"><?= htmlspecialchars($selBatch ?: (isset($course['batch']) ? $course['batch'] : '________')) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Date:</span>
                <span class="info-value"><?= date('d / m / Y') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Time:</span>
                <span class="info-value">___________________________</span>
            </div>
        </div>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th class="col-num">SL</th>
                <th class="col-id">Student ID</th>
                <th class="col-name">Student Name</th>
                <th class="col-sign">Signature</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($students)): ?>
                <tr>
                    <td colspan="4" style="text-align: center; padding: 40px; color: #777;">
                        No students found for the selected criteria.
                    </td>
                </tr>
            <?php else: ?>
                <?php $i = 1; foreach ($students as $s): ?>
                <tr>
                    <td class="col-num"><?= $i++ ?></td>
                    <td class="col-id"><?= htmlspecialchars($s['student_id']) ?></td>
                    <td class="col-name"><?= htmlspecialchars($s['name']) ?></td>
                    <td class="col-sign"></td>
                </tr>
                <?php endforeach; ?>
                <!-- Add some blank rows -->
                <?php for ($j = 0; $j < 3; $j++): ?>
                <tr>
                    <td class="col-num"><?= $i++ ?></td>
                    <td class="col-id"></td>
                    <td class="col-name"></td>
                    <td class="col-sign"></td>
                </tr>
                <?php endfor; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <div class="signature-box">Class Representative</div>
        <div class="signature-box">Course Teacher</div>
    </div>
</div>
