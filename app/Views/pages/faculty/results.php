<?php $currentPage = 'faculty-results'; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-syne text-3xl font-bold text-white tracking-tight">Faculty Result Evaluation</h1>
            <p class="text-slate-400 text-sm mt-1">Evaluate assigned courses with flexible components (CT, Assignment, Viva, etc.).</p>
        </div>
    </div>

    <div class="glass-card p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div class="md:col-span-2">
                <label class="text-xs text-slate-400 uppercase tracking-wide font-semibold">Course</label>
                <select name="course_id" class="w-full mt-1 bg-slate-900/70 border border-slate-700 rounded-xl px-3 py-2 text-sm" required>
                    <option value="">Select assigned course</option>
                    <?php foreach ($courses as $course): ?>
                        <?php
                        $batchLabel = trim((string)($course['batch'] ?? ''));
                        $semesterLabel = (int)($course['semester'] ?? 0);
                        ?>
                        <option
                            value="<?= (int)$course['id'] ?>"
                            data-batch="<?= htmlspecialchars($batchLabel) ?>"
                            data-semester="<?= $semesterLabel ?>"
                            <?= $selCourse == $course['id'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($course['code'] . ' - ' . $course['name']) ?>
                            <?= $batchLabel !== '' ? ' (Batch ' . htmlspecialchars($batchLabel) . ')' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-xs text-slate-400 uppercase tracking-wide font-semibold">Batch</label>
                <input type="text" name="batch" value="<?= htmlspecialchars($selBatch) ?>" class="w-full mt-1 bg-slate-900/70 border border-slate-700 rounded-xl px-3 py-2 text-sm" placeholder="e.g. 6">
            </div>
            <div>
                <label class="text-xs text-slate-400 uppercase tracking-wide font-semibold">Semester</label>
                <input type="number" min="1" max="12" name="semester" value="<?= (int)$selSemester ?>" class="w-full mt-1 bg-slate-900/70 border border-slate-700 rounded-xl px-3 py-2 text-sm" placeholder="e.g. 2">
            </div>
            <div>
                <button type="submit" class="w-full px-4 py-2 rounded-xl bg-accent-cyan/20 hover:bg-accent-cyan/30 border border-accent-cyan/30 text-accent-cyan text-sm font-bold">
                    Load Students
                </button>
            </div>
        </form>
    </div>

    <?php if ($selectedCourse && !empty($students)): ?>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="save_results">
            <input type="hidden" name="course_id" value="<?= (int)$selCourse ?>">
            <input type="hidden" name="batch" value="<?= htmlspecialchars($selBatch) ?>">
            <input type="hidden" name="semester" value="<?= (int)$selSemester ?>">

            <div class="glass-card p-6">
                <div class="flex flex-wrap items-end gap-4">
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wide">Selected Course</p>
                        <p class="text-sm text-white font-semibold">
                            <?= htmlspecialchars($selectedCourse['code'] . ' - ' . $selectedCourse['name']) ?>
                        </p>
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 uppercase tracking-wide font-semibold">Evaluation Date</label>
                        <input type="date" name="exam_date" value="<?= htmlspecialchars($selDate) ?>" class="mt-1 bg-slate-900/70 border border-slate-700 rounded-xl px-3 py-2 text-sm">
                    </div>
                    <div class="text-xs text-slate-500">
                        Students loaded: <span class="text-white font-semibold"><?= count($students) ?></span>
                    </div>
                </div>
            </div>

            <div class="glass-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-white">Evaluation Components</h3>
                    <button type="button" id="addComponentBtn" class="px-3 py-1.5 rounded-lg border border-accent-purple/30 bg-accent-purple/15 text-accent-purple text-xs font-bold">
                        + Add Component
                    </button>
                </div>
                <div id="componentsContainer" class="space-y-3"></div>
            </div>

            <div class="glass-card p-6 overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead>
                        <tr class="text-left text-slate-400 border-b border-slate-700/60">
                            <th class="py-2 pr-3">Student</th>
                            <th class="py-2 pr-3">ID</th>
                            <th class="py-2 pr-3">Scores by Component</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td class="py-3 pr-3 text-white font-medium"><?= htmlspecialchars($student['name']) ?></td>
                                <td class="py-3 pr-3 text-slate-400"><?= htmlspecialchars($student['student_id'] ?: '-') ?></td>
                                <td class="py-3 pr-3">
                                    <div class="student-score-grid grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-2" data-student-id="<?= (int)$student['id'] ?>"></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 border border-emerald-500/30 text-emerald-300 text-sm font-bold">
                    Save Evaluation
                </button>
            </div>
        </form>
    <?php elseif ($selectedCourse): ?>
        <div class="glass-card p-10 text-center text-slate-400">
            No enrolled students found for this course/batch/semester.
        </div>
    <?php endif; ?>
</div>

<script>
(() => {
    const existingComponents = <?= json_encode($components ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const scoreMap = <?= json_encode($scoreMap ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const componentsContainer = document.getElementById('componentsContainer');
    const addComponentBtn = document.getElementById('addComponentBtn');
    const courseSelect = document.querySelector('select[name="course_id"]');
    const batchInput = document.querySelector('input[name="batch"]');
    const semesterInput = document.querySelector('input[name="semester"]');

    function autoFillBatchSemester() {
        if (!courseSelect || !batchInput || !semesterInput) return;
        const selected = courseSelect.options[courseSelect.selectedIndex];
        if (!selected) return;
        const dataBatch = selected.getAttribute('data-batch') || '';
        const dataSemester = selected.getAttribute('data-semester') || '';
        if (!batchInput.value && dataBatch) batchInput.value = dataBatch;
        if (!semesterInput.value && dataSemester) semesterInput.value = dataSemester;
    }

    function renderScoreInputs() {
        const componentRows = componentsContainer ? componentsContainer.querySelectorAll('.component-row') : [];
        document.querySelectorAll('.student-score-grid').forEach((grid) => {
            const studentId = grid.getAttribute('data-student-id');
            grid.innerHTML = '';

            componentRows.forEach((row, idx) => {
                const nameInput = row.querySelector('.component-name');
                const maxInput = row.querySelector('.component-max');
                const componentName = (nameInput ? nameInput.value : '').trim();
                const maxValue = parseFloat(maxInput ? maxInput.value : '0') || 0;
                const existingScore = scoreMap?.[studentId]?.[componentName] ?? '';

                const wrap = document.createElement('div');
                wrap.className = 'border border-slate-700 rounded-lg p-2 bg-slate-900/40';
                wrap.innerHTML = `
                    <div class="text-[11px] text-slate-300 mb-1">${componentName || `Component ${idx + 1}`}</div>
                    <div class="flex items-center gap-2">
                        <input
                            type="number"
                            name="scores[${studentId}][${idx}]"
                            value="${existingScore}"
                            min="0"
                            step="0.01"
                            ${maxValue > 0 ? `max="${maxValue}"` : ''}
                            class="w-full bg-slate-800 border border-slate-600 rounded-md px-2 py-1.5 text-xs text-white"
                            placeholder="0"
                        >
                        <span class="text-[10px] text-slate-500 whitespace-nowrap">/ ${maxValue || 0}</span>
                    </div>
                `;
                grid.appendChild(wrap);
            });
        });
    }

    function addComponent(name = '', max = '') {
        if (!componentsContainer) return;
        const row = document.createElement('div');
        row.className = 'component-row grid grid-cols-1 md:grid-cols-[1fr_180px_auto] gap-2 items-end';
        row.innerHTML = `
            <div>
                <label class="text-[10px] text-slate-500 uppercase tracking-wide font-semibold">Component Name</label>
                <input type="text" name="component_name[]" class="component-name w-full mt-1 bg-slate-900/70 border border-slate-700 rounded-lg px-3 py-2 text-sm" placeholder="e.g. CT-1 / Assignment / Viva" value="${name}">
            </div>
            <div>
                <label class="text-[10px] text-slate-500 uppercase tracking-wide font-semibold">Max Marks</label>
                <input type="number" name="component_max[]" min="0.01" step="0.01" class="component-max w-full mt-1 bg-slate-900/70 border border-slate-700 rounded-lg px-3 py-2 text-sm" placeholder="e.g. 15" value="${max}">
            </div>
            <button type="button" class="remove-component px-3 py-2 rounded-lg border border-red-500/30 text-red-400 text-xs font-bold">Remove</button>
        `;
        componentsContainer.appendChild(row);

        row.querySelectorAll('input').forEach((input) => {
            input.addEventListener('input', renderScoreInputs);
        });
        row.querySelector('.remove-component').addEventListener('click', () => {
            row.remove();
            renderScoreInputs();
        });

        renderScoreInputs();
    }

    if (courseSelect) {
        courseSelect.addEventListener('change', autoFillBatchSemester);
        autoFillBatchSemester();
    }

    if (componentsContainer) {
        if (existingComponents.length > 0) {
            existingComponents.forEach((component) => addComponent(component.name || '', component.max || ''));
        } else {
            addComponent('CT-1', 15);
            addComponent('Assignment', 10);
            addComponent('Viva', 10);
        }
    }

    if (addComponentBtn) {
        addComponentBtn.addEventListener('click', () => addComponent());
    }
})();
</script>
