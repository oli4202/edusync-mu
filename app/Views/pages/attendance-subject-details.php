<?php $pageTitle = 'Subject Details — EduSync'; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 text-accent-cyan mb-1">
                <i data-lucide="arrow-left" class="w-4 h-4 cursor-pointer" onclick="history.back()"></i>
                <span class="text-[10px] font-bold uppercase tracking-widest">Attendance Details</span>
            </div>
            <h1 class="font-syne text-3xl font-extrabold text-white tracking-tight">
                <?= htmlspecialchars($courseCode) ?>
            </h1>
            <p class="text-sm text-slate-400 font-medium italic">Detailed Class-by-Class Attendance Record</p>
        </div>
        <a href="/attendance" class="btn btn-outline btn-sm">← Back to Overview</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main List -->
        <div class="lg:col-span-2">
            <div class="glass-card rounded-2xl border border-white/5 overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-white/[0.02] border-bottom border-white/5">
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Date</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Notes / Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php if (empty($details)): ?>
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-slate-500 italic text-sm">
                                    No attendance records found for this subject.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($details as $record): ?>
                                <tr class="hover:bg-white/[0.01] transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-white"><?= date('d F, Y', strtotime($record['class_date'])) ?></div>
                                        <div class="text-[10px] text-slate-500 uppercase font-medium"><?= date('l', strtotime($record['class_date'])) ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest 
                                            <?= match($record['status']) {
                                                'present' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
                                                'absent' => 'bg-red-500/10 text-red-400 border border-red-500/20',
                                                'late' => 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20',
                                                default => 'bg-white/5 text-slate-400 border border-white/10'
                                            }; ?>">
                                            <?= htmlspecialchars($record['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-400 italic">
                                        <?= $record['notes'] ? '"' . htmlspecialchars($record['notes']) . '"' : '—' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sidebar Summary -->
        <div class="space-y-4">
            <div class="glass-card p-6 rounded-2xl border border-white/5 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <i data-lucide="pie-chart" class="w-16 h-16"></i>
                </div>
                <h3 class="text-xs font-bold text-white uppercase tracking-widest mb-6">Quick Summary</h3>
                
                <?php
                $pCount = count(array_filter($details, fn($r) => $r['status'] === 'present'));
                $aCount = count(array_filter($details, fn($r) => $r['status'] === 'absent'));
                $lCount = count(array_filter($details, fn($r) => $r['status'] === 'late'));
                $total = count($details);
                $rate = $total > 0 ? round(($pCount / $total) * 100, 1) : 0;
                ?>

                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-400 font-medium">Present</span>
                        <span class="text-sm font-bold text-emerald-400"><?= $pCount ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-400 font-medium">Absent</span>
                        <span class="text-sm font-bold text-red-400"><?= $aCount ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-400 font-medium">Late</span>
                        <span class="text-sm font-bold text-yellow-400"><?= $lCount ?></span>
                    </div>
                    <div class="pt-4 border-t border-white/5">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-bold text-white uppercase tracking-widest">Percentage</span>
                            <span class="text-lg font-black text-accent-cyan"><?= $rate ?>%</span>
                        </div>
                        <div class="h-1.5 w-full bg-white/5 rounded-full overflow-hidden">
                            <div class="h-full bg-accent-cyan shadow-[0_0_10px_rgba(34,211,238,0.5)]" style="width: <?= $rate ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="glass-card p-6 rounded-2xl border border-white/5 bg-gradient-to-br from-accent-purple/5 to-transparent">
                <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Requirement</h3>
                <p class="text-xs text-white font-medium leading-relaxed">
                    Maintain at least <span class="text-accent-purple">75%</span> attendance in each subject to qualify for final examinations.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
