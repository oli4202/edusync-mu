<?php
$currentPage = 'grades';
?>

<div class="page-header">
    <h2>My Grades</h2>
    <div style="display:flex; gap:10px;">
        <button class="btn btn-primary" onclick="openModal('addGrade')">+ Add Grade</button>
        <a href="/result-lookup" class="result-link">MU Official Result</a>
    </div>
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

<style>
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

<div class="modal-overlay" id="addGrade">
    <div class="modal">
        <h3>🎓 Add Grade</h3>
        <form method="POST" action="/grades">
            <input type="hidden" name="action" value="add">
            
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
                <label>Exam Name *</label>
                <input type="text" name="exam_name" required placeholder="e.g. Midterm">
            </div>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label>Marks Obtained *</label>
                    <input type="number" step="0.1" name="marks_obtained" required>
                </div>
                <div class="form-group">
                    <label>Total Marks *</label>
                    <input type="number" step="0.1" name="total_marks" required value="100">
                </div>
            </div>

            <div class="form-group">
                <label>Date</label>
                <input type="date" name="exam_date" value="<?= date('Y-m-d') ?>">
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:10px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('addGrade')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Grade</button>
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

document.getElementById('addGrade').addEventListener('click', function(e){
    if(e.target===this) this.classList.remove('active');
});
</script>
