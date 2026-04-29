<?php 
$currentPage = 'routine'; 
?>

<div class="space-y-6">
    <!-- Header & Batch Selection -->
    <div class="glass-card p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div>
            <h1 class="font-syne text-3xl font-extrabold text-white mb-1">Class Routine</h1>
            <p class="text-sm text-slate-400 font-medium">
                <?= htmlspecialchars($routineData['department'] ?? 'Department') ?> • 
                <span class="text-accent-cyan"><?= htmlspecialchars($routineData['version'] ?? 'Version') ?></span>
            </p>
        </div>
        
        <div class="flex flex-wrap gap-2">
            <?php foreach ($routineData['batches'] as $batch): ?>
                <a href="?batch=<?= urlencode($batch) ?>" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-all border <?= $selectedBatch === $batch ? 'bg-accent-cyan text-dark-bg border-accent-cyan shadow-[0_0_15px_rgba(34,211,238,0.3)]' : 'bg-white/5 text-slate-300 border-white/10 hover:bg-white/10' ?>">
                    <?= htmlspecialchars($batch) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (!$selectedBatch): ?>
        <div class="glass-card p-12 text-center flex flex-col items-center justify-center">
            <div class="w-16 h-16 bg-white/5 rounded-full flex items-center justify-center mb-4">
                <i data-lucide="calendar" class="w-8 h-8 text-accent-cyan"></i>
            </div>
            <h2 class="font-syne text-xl font-bold text-white mb-2">Select a Batch</h2>
            <p class="text-slate-400 text-sm max-w-sm">Please select your specific batch from the options above to view the detailed class schedule.</p>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($routineData['days'] as $day): ?>
                <?php 
                $isCurrentDay = ($day === $currentDay);
                $daySchedule = $routineData['schedule'][$day][$selectedBatch] ?? [];
                
                $slotsMap = [];
                foreach ($daySchedule as $item) {
                    $slotsMap[$item[0]] = [
                        'course' => $item[1],
                        'room' => $item[2],
                        'faculty' => $item[3]
                    ];
                }
                ?>
                
                <div class="glass-card overflow-hidden transition-all duration-300 <?= $isCurrentDay ? 'border-l-4 border-l-accent-cyan shadow-[0_0_20px_rgba(34,211,238,0.1)] ring-1 ring-accent-cyan/20' : '' ?>">
                    <!-- Day Header -->
                    <div class="px-6 py-4 border-b border-white/5 bg-white/[0.02] flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <h3 class="font-syne text-lg font-bold text-white tracking-wide"><?= htmlspecialchars($day) ?></h3>
                            <?php if ($isCurrentDay): ?>
                                <span class="px-2.5 py-1 rounded bg-accent-cyan/20 text-accent-cyan text-[10px] font-extrabold uppercase tracking-widest border border-accent-cyan/20">Today</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (empty($daySchedule)): ?>
                        <div class="p-8 text-center flex flex-col items-center justify-center bg-white/[0.01]">
                            <i data-lucide="coffee" class="w-6 h-6 text-slate-500 mb-2"></i>
                            <p class="text-slate-400 text-sm font-medium">No classes scheduled for today. Take a break!</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-px bg-white/5">
                            <?php foreach ($routineData['time_slots'] as $index => $timeSlot): ?>
                                <div class="bg-[#0a0e1a] p-4 flex flex-col min-h-[140px] transition-colors hover:bg-white/[0.02]">
                                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        <?= htmlspecialchars($timeSlot['start']) ?> - <?= htmlspecialchars($timeSlot['end']) ?>
                                    </div>
                                    
                                    <?php if (isset($slotsMap[$index])): ?>
                                        <?php 
                                        $class = $slotsMap[$index];
                                        $bgColor = 'rgba(255, 255, 255, 0.05)';
                                        $borderColor = 'rgba(255, 255, 255, 0.1)';
                                        
                                        foreach ($routineData['colors'] as $prefix => $color) {
                                            if ($prefix !== 'default' && str_starts_with($class['course'], $prefix)) {
                                                $bgColor = $color;
                                                $borderColor = 'transparent';
                                                break;
                                            }
                                        }
                                        ?>
                                        <div class="flex-1 rounded-xl p-3 flex flex-col justify-between relative overflow-hidden group shadow-lg" style="background: <?= $bgColor ?>; border: 1px solid <?= $borderColor ?>;">
                                            <!-- Dark overlay to ensure text readability on the custom colored backgrounds -->
                                            <div class="absolute inset-0 bg-black/40 group-hover:bg-black/30 transition-colors"></div>
                                            
                                            <div class="relative z-10">
                                                <div class="font-bold text-white text-sm leading-tight mb-2 drop-shadow-md">
                                                    <?= htmlspecialchars($class['course']) ?>
                                                </div>
                                            </div>
                                            
                                            <div class="relative z-10 flex justify-between items-end mt-2">
                                                <div class="flex items-center gap-1 text-[11px] font-bold text-white/90 bg-black/30 px-2 py-1 rounded backdrop-blur-sm">
                                                    <i data-lucide="map-pin" class="w-3 h-3"></i>
                                                    <?= htmlspecialchars($class['room'] ?: 'TBA') ?>
                                                </div>
                                                <div class="text-xs font-black text-white bg-black/40 px-2 py-1 rounded shadow-sm">
                                                    <?= htmlspecialchars($class['faculty']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="flex-1 flex items-center justify-center">
                                            <span class="w-8 h-[1px] bg-white/10"></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
