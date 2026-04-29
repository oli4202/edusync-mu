<?php $pageTitle = 'Continuous Assessment — EduSync Admin'; ?>

<style>
.assessment-container { max-width: 100%; overflow-x: auto; padding: 20px 0; }
.assessment-grid { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 1200px; }
.assessment-grid th, .assessment-grid td { padding: 12px; border-bottom: 1px solid rgba(148,163,184,0.1); border-right: 1px solid rgba(148,163,184,0.1); font-size: 13px; text-align: center; }
.assessment-grid th { background: rgba(15,23,42,0.8); position: sticky; top: 0; z-index: 20; color: #94a3b8; text-transform: uppercase; font-size: 10px; font-weight: 700; letter-spacing: 0.05em; }
.assessment-grid .sticky-col { position: sticky; left: 0; background: #0f172a; z-index: 10; text-align: left; border-right: 2px solid rgba(34,211,238,0.2); }
.assessment-grid .sticky-header { z-index: 30; }

.status-p { color: #34d399; font-weight: 800; }
.status-a { color: #f87171; font-weight: 800; opacity: 0.5; }
.status-l { color: #fbbf24; font-weight: 800; }

.summary-card { background: rgba(255,255,255,0.03); border-radius: 12px; padding: 15px; text-align: left; border: 1px solid rgba(148,163,184,0.1); }
.summary-label { font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: 700; margin-bottom: 5px; }
.summary-value { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 800; color: #fff; }

.mark-pill { padding: 4px 8px; border-radius: 6px; background: rgba(34,211,238,0.1); color: #67e8f9; font-weight: 700; font-size: 12px; }

.search-id-container { margin-bottom: 20px; display: flex; gap: 10px; }
.search-id-input { background: rgba(15,23,42,0.7); border: 1px solid rgba(148,163,184,0.3); border-radius: 8px; padding: 8px 15px; color: #fff; font-size: 14px; width: 250px; outline: none; }
.search-id-input:focus { border-color: #22d3ee; }

.highlight-row { background: rgba(34,211,238,0.05) !important; }
</style>

<div class="space-y-6">
    <div class="flex justify-between items-end">
        <div>
            <div class="flex items-center gap-2 text-accent-cyan mb-1">
                <i data-lucide="layout-grid" class="w-4 h-4"></i>
                <span class="text-[10px] font-bold uppercase tracking-widest">Continuous Assessment Grid</span>
            </div>
            <h1 class="text-2xl font-bold text-white font-syne"><?= htmlspecialchars($course['code'] . ' — ' . $course['name']) ?></h1>
            <p class="text-sm text-slate-400">Batch <?= htmlspecialchars($selBatch) ?> · Spring 2026</p>
        </div>
        <div class="flex gap-2">
            <a href="/admin/attendance?course_id=<?= $course['id'] ?>&batch=<?= urlencode($selBatch) ?>&semester=<?= $selSemester ?>" class="btn btn-outline btn-sm">← Back to Manager</a>
            <button onclick="window.print()" class="btn btn-outline btn-sm no-print">🖨️ Export PDF</button>
        </div>
    </div>

    <div class="search-id-container no-print">
        <input type="text" id="idSearch" class="search-id-input" placeholder="Search Student ID (e.g. 241-134-036)" onkeyup="filterById()">
        <div class="text-[10px] text-slate-500 font-medium italic mt-2">Highlights the student record across the grid.</div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 no-print">
        <div class="summary-card">
            <div class="summary-label">Total Classes</div>
            <div class="summary-value"><?= count($dates) ?></div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Batch Size</div>
            <div class="summary-value"><?= count($students) ?></div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Avg Attendance</div>
            <div class="summary-value">
                <?php 
                $totalPossible = count($students) * count($dates);
                $totalPresent = 0;
                foreach($attendanceGrid as $uid => $row) {
                    foreach($row as $status) if($status === 'present') $totalPresent++;
                }
                echo $totalPossible > 0 ? round(($totalPresent / $totalPossible) * 100, 1) : 0;
                ?>%
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Components</div>
            <div class="summary-value text-accent-purple">6 Active</div>
        </div>
    </div>

    <div class="glass-card rounded-2xl border border-white/5 overflow-hidden">
        <div class="assessment-container custom-scrollbar">
            <table class="assessment-grid" id="mainGrid">
                <thead>
                    <tr>
                        <th class="sticky-col sticky-header" style="width: 250px;">Student Information</th>
                        <!-- Performance Components -->
                        <th style="background: rgba(129,140,248,0.1);">Att (10)</th>
                        <th style="background: rgba(129,140,248,0.1);">CT1 (15)</th>
                        <th style="background: rgba(129,140,248,0.1);">CT2 (15)</th>
                        <th style="background: rgba(129,140,248,0.1);">Assn (10)</th>
                        <th style="background: rgba(129,140,248,0.1);">Total</th>
                        
                        <!-- Attendance Dates -->
                        <?php foreach($dates as $date): ?>
                            <th class="rotate-header">
                                <div class="text-[9px]"><?= date('m/d', strtotime($date)) ?></div>
                                <div class="text-[8px] text-slate-500 font-normal"><?= strtoupper(date('D', strtotime($date))) ?></div>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($students as $s): 
                        $sGrades = $grades[$s['id']] ?? [];
                        $marks = [
                            'Attendance' => 0,
                            'Class Test 1' => 0,
                            'Class Test 2' => 0,
                            'Assignment' => 0
                        ];
                        foreach($sGrades as $g) {
                            if (str_contains($g['title'], 'Attendance')) $marks['Attendance'] = $g['score'];
                            if (str_contains($g['title'], 'Class Test 1')) $marks['Class Test 1'] = $g['score'];
                            if (str_contains($g['title'], 'Class Test 2')) $marks['Class Test 2'] = $g['score'];
                            if (str_contains($g['title'], 'Assignment')) $marks['Assignment'] = $g['score'];
                        }
                        $total = array_sum($marks);
                    ?>
                    <tr class="student-row" data-id="<?= htmlspecialchars($s['student_id']) ?>">
                        <td class="sticky-col">
                            <div class="flex items-center gap-3">
                                <img src="<?= avatarUrl($s['avatar'] ?? '', $s['name']) ?>" class="w-8 h-8 rounded-full border border-white/10">
                                <div>
                                    <div class="text-white font-bold truncate max-w-[150px]"><?= htmlspecialchars($s['name']) ?></div>
                                    <div class="text-[10px] text-slate-500 font-mono"><?= htmlspecialchars($s['student_id']) ?></div>
                                </div>
                            </div>
                        </td>
                        
                        <!-- Marks -->
                        <td class="font-bold text-slate-300"><?= $marks['Attendance'] ?></td>
                        <td class="font-bold text-slate-300"><?= $marks['Class Test 1'] ?></td>
                        <td class="font-bold text-slate-300"><?= $marks['Class Test 2'] ?></td>
                        <td class="font-bold text-slate-300"><?= $marks['Assignment'] ?></td>
                        <td><span class="mark-pill"><?= $total ?></span></td>

                        <!-- Attendance Dots -->
                        <?php foreach($dates as $date): 
                            $status = $attendanceGrid[$s['id']][$date] ?? '';
                            $display = match($status) {
                                'present' => 'P',
                                'absent' => 'A',
                                'late' => 'L',
                                default => '·'
                            };
                            $class = match($status) {
                                'present' => 'status-p',
                                'absent' => 'status-a',
                                'late' => 'status-l',
                                default => 'text-slate-700'
                            };
                        ?>
                            <td class="<?= $class ?>"><?= $display ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function filterById() {
    const input = document.getElementById('idSearch');
    const filter = input.value.toUpperCase();
    const rows = document.querySelectorAll('.student-row');
    
    rows.forEach(row => {
        const id = row.getAttribute('data-id');
        if (filter && id.toUpperCase().includes(filter)) {
            row.classList.add('highlight-row');
            // Smooth scroll to row if it's the exact match
            if (id.toUpperCase() === filter) {
                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        } else {
            row.classList.remove('highlight-row');
        }
    });
}
</script>
