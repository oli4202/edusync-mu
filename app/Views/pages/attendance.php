<?php
$currentPage = 'attendance';
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="font-syne text-3xl font-extrabold text-white mb-1 tracking-tight flex items-center gap-3">
                <i data-lucide="clipboard-check" class="w-8 h-8 text-accent-cyan"></i>
                Attendance Record
            </h1>
            <p class="text-sm text-slate-400 font-medium italic">Spring 2026 Semester (Jan — Apr)</p>
        </div>
        <div class="px-4 py-2 bg-accent-cyan/10 border border-accent-cyan/20 rounded-xl">
            <span class="text-[10px] font-bold text-accent-cyan uppercase tracking-widest block">Your Primary Batch</span>
            <span class="text-white font-bold text-sm">Batch <?php echo htmlspecialchars($user['batch'] ?? 'N/A'); ?></span>
        </div>
    </div>

    <!-- Stats Overview -->
    <?php
    $totalClasses = count($myAttendance);
    $presentCount = count(array_filter($myAttendance, static fn($record) => ($record['status'] ?? '') === 'present'));
    $presentRate = $totalClasses > 0 ? round(($presentCount / $totalClasses) * 100, 1) : 0;
    ?>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="glass-card p-6 rounded-2xl border border-white/5 relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-accent-cyan/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-1">Total Classes</p>
            <p class="text-3xl font-bold text-white"><?php echo $totalClasses; ?></p>
            <div class="mt-2 text-[10px] text-slate-500 font-medium italic">Classes conducted so far</div>
        </div>

        <div class="glass-card p-6 rounded-2xl border border-white/5 relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-accent-purple/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-1">Your Presence</p>
            <p class="text-3xl font-bold text-white"><?php echo $presentCount; ?></p>
            <div class="mt-2 text-[10px] text-emerald-400 font-bold flex items-center gap-1">
                <i data-lucide="trending-up" class="w-3 h-3"></i>
                Confirmed present
            </div>
        </div>

        <div class="glass-card p-6 rounded-2xl border border-white/5 relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-accent-cyan/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-1">Attendance Rate</p>
            <p class="text-3xl font-bold text-accent-cyan"><?php echo $presentRate; ?>%</p>
            <div class="mt-2 h-1.5 w-full bg-white/5 rounded-full overflow-hidden">
                <div class="h-full bg-accent-cyan" style="width: <?php echo $presentRate; ?>%"></div>
            </div>
        </div>

        <div class="glass-card p-6 rounded-2xl border border-white/5 relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-1">Batch Average</p>
            <p class="text-3xl font-bold text-slate-300">
                <?php 
                $bTotal = (int)($batchStats['total_records'] ?? 0);
                $bPresent = (int)($batchStats['present_count'] ?? 0);
                echo $bTotal > 0 ? round(($bPresent / $bTotal) * 100, 1) : 0;
                ?>%
            </p>
            <div class="mt-2 text-[10px] text-slate-500 font-medium italic">Comparison with peers</div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Subject Wise Breakdown (Requested) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between px-2">
                <h3 class="font-syne text-lg font-bold text-white">Subject Breakdown</h3>
                <span class="text-[10px] text-slate-500 uppercase font-bold tracking-widest italic">Based on Semester Routine</span>
            </div>
            
            <div class="grid grid-cols-1 gap-4">
                <?php if (!empty($subjectReport)): ?>
                    <?php foreach ($subjectReport as $report): ?>
                        <a href="/attendance/details?course_code=<?php echo urlencode($report['course_code']); ?>" class="glass-card p-5 border border-white/5 hover:border-white/10 transition-all group block">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-white/5 border border-white/10 flex flex-col items-center justify-center">
                                        <span class="text-[8px] font-bold text-slate-500 uppercase leading-none mb-1">CODE</span>
                                        <span class="text-xs font-bold text-white leading-none"><?php echo explode(' ', $report['course_code'])[0]; ?></span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-white group-hover:text-accent-cyan transition-colors"><?php echo htmlspecialchars($report['course_code']); ?></h4>
                                        <div class="flex items-center gap-2 mt-1">
                                            <i data-lucide="user" class="w-3 h-3 text-accent-purple"></i>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?php echo htmlspecialchars($report['faculty']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-8">
                                    <div class="text-center">
                                        <span class="block text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">Expected</span>
                                        <span class="text-sm font-bold text-slate-300"><?php echo $report['expected']; ?></span>
                                    </div>
                                    <div class="text-center">
                                        <span class="block text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">Conducted</span>
                                        <span class="text-sm font-bold text-slate-300"><?php echo $report['conducted']; ?></span>
                                    </div>
                                    <div class="text-center">
                                        <span class="block text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">Attended</span>
                                        <span class="text-sm font-bold text-emerald-400"><?php echo $report['attended']; ?></span>
                                    </div>
                                    <div class="w-16 h-16 rounded-full border-4 border-white/5 relative flex items-center justify-center">
                                        <svg class="w-full h-full transform -rotate-90 absolute">
                                            <circle cx="32" cy="32" r="28" fill="transparent" stroke="currentColor" stroke-width="4" class="text-white/5" />
                                            <circle cx="32" cy="32" r="28" fill="transparent" stroke="currentColor" stroke-width="4" stroke-dasharray="175.9" stroke-dashoffset="<?php echo 175.9 * (1 - ($report['rate'] / 100)); ?>" class="<?php echo $report['rate'] >= 75 ? 'text-accent-cyan' : 'text-orange-500'; ?>" />
                                        </svg>
                                        <span class="text-[10px] font-bold text-white relative z-10"><?php echo $report['rate']; ?>%</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="glass-card p-12 text-center opacity-50">
                        <i data-lucide="alert-circle" class="w-10 h-10 mx-auto mb-4 text-slate-600"></i>
                        <p class="text-sm font-medium">No subjects found for your batch in the routine.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activity Logs -->
        <div class="space-y-4">
            <h3 class="font-syne text-lg font-bold text-white px-2 flex items-center gap-2">
                <i data-lucide="history" class="w-4 h-4 text-accent-purple"></i>
                Recent History
            </h3>
            
            <div class="glass-card rounded-2xl border border-white/5 overflow-hidden">
                <div class="divide-y divide-white/5">
                    <?php if (!empty($myAttendance)): ?>
                        <?php foreach (array_slice($myAttendance, 0, 10) as $record): ?>
                            <div class="p-4 hover:bg-white/[0.02] transition-colors">
                                <div class="flex justify-between items-start mb-1">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest"><?php echo date('M d, Y', strtotime($record['class_date'])); ?></span>
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-widest 
                                        <?php echo match($record['status']) {
                                            'present' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
                                            'absent' => 'bg-red-500/10 text-red-400 border border-red-500/20',
                                            'late' => 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20',
                                            default => 'bg-white/5 text-slate-400 border border-white/10'
                                        }; ?>">
                                        <?php echo ucfirst($record['status']); ?>
                                    </span>
                                </div>
                                <h5 class="text-xs font-bold text-white mb-1"><?php echo htmlspecialchars($record['course_code']); ?></h5>
                                <?php if ($record['notes']): ?>
                                    <p class="text-[10px] text-slate-500 italic">"<?php echo htmlspecialchars($record['notes']); ?>"</p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-8 text-center opacity-30 italic text-xs">No activity logged yet.</div>
                    <?php endif; ?>
                </div>
                <?php if (count($myAttendance) > 10): ?>
                    <div class="p-3 bg-white/[0.02] text-center border-t border-white/5">
                        <button class="text-[10px] font-bold text-accent-cyan uppercase tracking-widest hover:underline">View Full History</button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Batch Progress -->
            <div class="glass-card p-5 border border-white/5 bg-gradient-to-br from-accent-purple/5 to-transparent">
                <h4 class="text-xs font-bold text-white uppercase tracking-widest mb-4">Batch Presence</h4>
                <div class="space-y-4">
                    <div class="flex justify-between items-center text-[10px] font-bold uppercase tracking-widest">
                        <span class="text-slate-400">Class Progress</span>
                        <span class="text-white"><?php echo $presentRate; ?>%</span>
                    </div>
                    <div class="h-2 w-full bg-white/5 rounded-full overflow-hidden">
                        <div class="h-full bg-accent-purple shadow-[0_0_10px_rgba(129,140,248,0.5)] transition-all duration-1000" style="width: <?php echo $presentRate; ?>%"></div>
                    </div>
                    <p class="text-[10px] text-slate-500 italic leading-relaxed">
                        Attendance criteria requires at least 75% for exam eligibility.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
