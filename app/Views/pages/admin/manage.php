<?php $pageTitle = 'Admin Management — EduSync MU'; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-syne text-3xl font-bold text-white tracking-tight">Administration Console</h1>
            <p class="text-slate-400 text-sm mt-1">Manage faculty, courses, assignments, and routine from one place.</p>
        </div>
        <a href="/admin" class="px-4 py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-xs font-bold">← Back to Admin</a>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="glass-card p-5">
            <h3 class="text-white font-bold mb-3">Add Faculty</h3>
            <form method="POST" class="space-y-3">
                <input type="hidden" name="action" value="add_faculty">
                <input type="text" name="name" placeholder="Faculty full name" class="w-full bg-slate-900/70 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" required>
                <input type="email" name="email" placeholder="faculty@edusync.mu" class="w-full bg-slate-900/70 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" required>
                <input type="password" name="password" placeholder="Initial password (min 6)" class="w-full bg-slate-900/70 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" required>
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-xs font-bold">Create Faculty</button>
            </form>
        </div>

        <div class="glass-card p-5">
            <h3 class="text-white font-bold mb-3">Add Course</h3>
            <form method="POST" class="space-y-3">
                <input type="hidden" name="action" value="add_course">
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="code" placeholder="Code (e.g. SWE 315)" class="bg-slate-900/70 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" required>
                    <input type="text" name="name" placeholder="Course name" class="bg-slate-900/70 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" required>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="number" name="year" min="1" value="1" class="bg-slate-900/70 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" required>
                    <input type="number" name="semester" min="1" value="1" class="bg-slate-900/70 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" required>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="batch" placeholder="Batch (e.g. 6 or 10,11)" class="bg-slate-900/70 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white">
                    <select name="status" class="bg-slate-900/70 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white">
                        <option value="active">Active</option>
                        <option value="reserved">Reserved</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 rounded-lg bg-accent-cyan/20 border border-accent-cyan/30 text-accent-cyan text-xs font-bold">Create Course</button>
            </form>
        </div>
    </div>

    <div class="glass-card p-5">
        <h3 class="text-white font-bold mb-3">Assign Faculty to Course + Batch</h3>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
            <input type="hidden" name="action" value="assign_faculty">
            <div>
                <label class="text-[11px] text-slate-500 uppercase">Faculty</label>
                <select name="teacher_id" class="w-full mt-1 bg-slate-900/70 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" required>
                    <option value="">Select faculty</option>
                    <?php foreach ($faculties as $f): ?>
                        <option value="<?= (int)$f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="text-[11px] text-slate-500 uppercase">Course</label>
                <select name="course_id" class="w-full mt-1 bg-slate-900/70 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" required>
                    <option value="">Select course</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['code'] . ' — ' . $c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-[11px] text-slate-500 uppercase">Batch</label>
                <input type="text" name="batch" placeholder="e.g. 6" class="w-full mt-1 bg-slate-900/70 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" required>
            </div>
            <div>
                <label class="text-[11px] text-slate-500 uppercase">Semester</label>
                <input type="number" name="semester" min="1" max="12" class="w-full mt-1 bg-slate-900/70 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" required>
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-accent-purple/20 border border-accent-purple/30 text-accent-purple text-xs font-bold md:col-span-5 md:justify-self-start">Save Assignment</button>
        </form>
    </div>

    <div class="glass-card p-5">
        <h3 class="text-white font-bold mb-3">Add Routine Slot</h3>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
            <input type="hidden" name="action" value="add_routine_slot">
            <div>
                <label class="text-[11px] text-slate-500 uppercase">Day</label>
                <select name="day" class="w-full mt-1 bg-slate-900/70 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" required>
                    <?php foreach (($routineData['days'] ?? []) as $day): ?>
                        <option value="<?= htmlspecialchars($day) ?>"><?= htmlspecialchars($day) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-[11px] text-slate-500 uppercase">Batch Label</label>
                <select name="batch_label" class="w-full mt-1 bg-slate-900/70 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" required>
                    <?php foreach (($routineData['batches'] ?? []) as $batch): ?>
                        <option value="<?= htmlspecialchars($batch) ?>"><?= htmlspecialchars($batch) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-[11px] text-slate-500 uppercase">Time Slot</label>
                <select name="slot_index" class="w-full mt-1 bg-slate-900/70 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" required>
                    <?php foreach (($routineData['time_slots'] ?? []) as $idx => $slot): ?>
                        <option value="<?= (int)$idx ?>"><?= htmlspecialchars(($slot['label'] ?? ('Slot ' . $idx))) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-[11px] text-slate-500 uppercase">Course Text</label>
                <input type="text" name="course_text" placeholder="e.g. SWE-315 AI" class="w-full mt-1 bg-slate-900/70 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" required>
            </div>
            <div>
                <label class="text-[11px] text-slate-500 uppercase">Room</label>
                <input type="text" name="room" placeholder="101" class="w-full mt-1 bg-slate-900/70 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white">
            </div>
            <div>
                <label class="text-[11px] text-slate-500 uppercase">Faculty Short</label>
                <input type="text" name="faculty_short" placeholder="AAC" class="w-full mt-1 bg-slate-900/70 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white">
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-orange-500/20 border border-orange-500/30 text-orange-300 text-xs font-bold md:col-span-6 md:justify-self-start">Add Routine Slot</button>
        </form>
    </div>

    <div class="glass-card p-5">
        <h3 class="text-white font-bold mb-3">Current Faculty Assignments</h3>
        <div class="overflow-auto max-h-[420px]">
            <table class="w-full text-sm">
                <thead class="text-slate-400 border-b border-slate-700">
                    <tr>
                        <th class="text-left py-2 pr-2">Faculty</th>
                        <th class="text-left py-2 pr-2">Course</th>
                        <th class="text-left py-2 pr-2">Batch</th>
                        <th class="text-left py-2 pr-2">Semester</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    <?php foreach ($assignments as $a): ?>
                        <tr>
                            <td class="py-2 pr-2 text-white"><?= htmlspecialchars($a['faculty_name']) ?></td>
                            <td class="py-2 pr-2"><?= htmlspecialchars($a['code'] . ' — ' . $a['course_name']) ?></td>
                            <td class="py-2 pr-2">Batch <?= htmlspecialchars($a['batch']) ?></td>
                            <td class="py-2 pr-2"><?= (int)$a['semester'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
