<?php $pageTitle = 'Academic Result Sheet — EduSync Admin'; ?>

<style>
.result-sheet-table { width: 100%; border-collapse: collapse; background: rgba(15,23,42,0.6); border-radius: 12px; overflow: hidden; }
.result-sheet-table th, .result-sheet-table td { padding: 12px 15px; border: 1px solid rgba(148,163,184,0.1); text-align: center; }
.result-sheet-table th { background: rgba(30,41,59,0.8); color: #94a3b8; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; }
.result-sheet-table td { font-size: 14px; color: #e2e8f0; }
.student-info-cell { text-align: left !important; min-width: 250px; }
.total-cell { font-weight: 800; color: #22d3ee; background: rgba(34,211,238,0.05); }
.grade-cell { font-weight: 800; font-family: 'Syne', sans-serif; }
.grade-A-plus { color: #34d399; }
.grade-A { color: #10b981; }
.grade-F { color: #f87171; }

.filter-card { background: rgba(15,23,42,0.8); border: 1px solid rgba(148,163,184,0.2); border-radius: 16px; padding: 20px; margin-bottom: 24px; }
.filter-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end; }
.filter-grid label { font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 700; margin-bottom: 5px; display: block; }
.filter-grid select { width: 100%; background: #0f172a; border: 1px solid rgba(148,163,184,0.3); border-radius: 8px; padding: 10px; color: #fff; outline: none; }
.filter-grid select:focus { border-color: #22d3ee; }

.breakdown-label { font-size: 9px; color: #64748b; margin-top: 2px; }
</style>

<div class="space-y-6">
    <div class="flex justify-between items-end">
        <div>
            <div class="flex items-center gap-2 text-accent-cyan mb-1">
                <i data-lucide="award" class="w-4 h-4"></i>
                <span class="text-[10px] font-bold uppercase tracking-widest">Academic Records</span>
            </div>
            <h1 class="text-2xl font-bold text-white font-syne">Batch Result Sheet</h1>
            <p class="text-sm text-slate-400">Official marks breakdown and grade summary for Metropolitan University</p>
        </div>
        <div class="flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm no-print">🖨️ Print Result Sheet</button>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card no-print">
        <form method="GET" action="/admin/results" class="filter-grid">
            <div>
                <label>Select Batch</label>
                <select name="batch" id="batchSelect" required>
                    <option value="">Choose Batch...</option>
                    <?php foreach ($availableBatches as $batch): ?>
                        <option value="<?= htmlspecialchars($batch) ?>" <?= $selBatch == $batch ? 'selected' : '' ?>>Batch <?= htmlspecialchars($batch) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Select Subject</label>
                <select name="course_id" id="courseSelect" required>
                    <option value="">Select batch first...</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $selCourse == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['code'] . ' — ' . $c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary w-full" style="padding: 11px;">Generate Summary</button>
            </div>
        </form>
    </div>

    <?php if ($selCourse && !empty($students)): ?>
    <div class="glass-card rounded-2xl border border-white/5 overflow-hidden">
        <div class="p-6 border-b border-white/5 bg-white/[0.02] flex justify-between items-center">
            <div>
                <h2 class="text-lg font-bold text-white"><?= htmlspecialchars($course['name']) ?></h2>
                <p class="text-xs text-slate-400"><?= htmlspecialchars($course['code']) ?> · Batch <?= htmlspecialchars($selBatch) ?></p>
            </div>
            <div class="text-right">
                <div class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Grading Standard</div>
                <div class="text-xs text-accent-cyan font-bold">60 (Continuous) + 40 (Final) = 100 Total</div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="result-sheet-table">
                <thead>
                    <tr>
                        <th class="student-info-cell">Student Name & ID</th>
                        <th>Continuous (60)<br><span class="breakdown-label">Att(10)+CT1(15)+CT2(15)+Assn/Quiz(20)</span></th>
                        <th>Final Exam (40)</th>
                        <th>Total (100)</th>
                        <th>Grade</th>
                        <th>GP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $s): 
                        $sGrades = $grades[$s['id']] ?? [];
                        
                        // Break down marks
                        $att = 0; $ct1 = 0; $ct2 = 0; $assn = 0; $final = 0;
                        
                        foreach ($sGrades as $g) {
                            $title = strtolower($g['title']);
                            if (str_contains($title, 'attendance')) $att = $g['score'];
                            elseif (str_contains($title, 'test 1')) $ct1 = $g['score'];
                            elseif (str_contains($title, 'test 2')) $ct2 = $g['score'];
                            elseif (str_contains($title, 'assignment') || str_contains($title, 'quiz')) $assn += $g['score'];
                            elseif (str_contains($title, 'final')) $final = $g['score'];
                        }
                        
                        // Limit Assn/Quiz to 20
                        $assn = min(20, $assn);
                        $continuous = $att + $ct1 + $ct2 + $assn;
                        $total = $continuous + $final;
                        
                        // Calculate grade using our model's logic
                        $percent = $total; // Since total is out of 100
                        $gradeInfo = \App\Models\Grade::letterGradeFromPercent($percent);
                        $gradeClass = 'grade-' . str_replace(['+', '-'], ['-plus', ''], $gradeInfo['grade']);
                    ?>
                    <tr>
                        <td class="student-info-cell">
                            <div class="flex items-center gap-3">
                                <img src="<?= avatarUrl($s['avatar'] ?? '', $s['name']) ?>" class="w-8 h-8 rounded-full">
                                <div>
                                    <div class="font-bold text-white"><?= htmlspecialchars($s['name']) ?></div>
                                    <div class="text-[10px] text-slate-500 font-mono"><?= htmlspecialchars($s['student_id']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="font-bold"><?= $continuous ?></div>
                            <div class="text-[9px] text-slate-500"><?= $att ?>+<?= $ct1 ?>+<?= $ct2 ?>+<?= $assn ?></div>
                        </td>
                        <td><?= $final ?></td>
                        <td class="total-cell"><?= round($total, 1) ?></td>
                        <td class="grade-cell <?= $gradeClass ?>"><?= $gradeInfo['grade'] ?></td>
                        <td class="font-mono text-xs"><?= number_format($gradeInfo['gp'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php elseif ($selCourse): ?>
    <div class="p-12 text-center glass-card rounded-2xl border border-white/5">
        <i data-lucide="users-2" class="w-12 h-12 text-slate-600 mx-auto mb-4"></i>
        <h3 class="text-white font-bold">No students found</h3>
        <p class="text-slate-400 text-sm">There are no students registered for Batch <?= htmlspecialchars($selBatch) ?> in this subject.</p>
    </div>
    <?php else: ?>
    <div class="p-12 text-center glass-card rounded-2xl border border-white/5">
        <i data-lucide="filter" class="w-12 h-12 text-slate-600 mx-auto mb-4"></i>
        <h3 class="text-white font-bold">Please select a batch and subject</h3>
        <p class="text-slate-400 text-sm">Use the filters above to generate a professional result sheet.</p>
    </div>
    <?php endif; ?>
</div>

<script>
// Dynamic subject loading based on batch (same logic as attendance page)
document.getElementById('batchSelect').addEventListener('change', function() {
    const batch = this.value;
    const courseSelect = document.getElementById('courseSelect');
    
    courseSelect.innerHTML = '<option value="">Loading courses...</option>';

    if (!batch) {
        courseSelect.innerHTML = '<option value="">Select batch first...</option>';
        return;
    }

    fetch(`/api/courses/filter_all?batch=${batch}`)
        .then(res => res.json())
        .then(data => {
            courseSelect.innerHTML = '<option value="">Select subject...</option>';
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = `${c.code} — ${c.name}`;
                if (c.id == "<?= $selCourse ?>") opt.selected = true;
                courseSelect.appendChild(opt);
            });
        });
});

// Trigger change on load if batch is selected
if (document.getElementById('batchSelect').value) {
    document.getElementById('batchSelect').dispatchEvent(new Event('change'));
}
</script>
