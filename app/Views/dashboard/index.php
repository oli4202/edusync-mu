<?php
/**
 * Dashboard page - dashboard/index.php
 */
$currentPage = 'dashboard';
?>

<div class="space-y-10">
    <!-- Welcome Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="font-syne text-3xl font-bold text-white tracking-tight">
                Welcome back, <span class="bg-gradient-to-r from-accent-cyan to-accent-purple bg-clip-text text-transparent italic"><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></span>
            </h1>
            <p class="text-slate-500 text-sm mt-1 font-medium">Here's what's happening with your studies today.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="/tasks" class="px-4 py-2 bg-accent-cyan/10 hover:bg-accent-cyan/20 border border-accent-cyan/20 text-accent-cyan rounded-xl text-xs font-bold transition-all duration-200 flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                New Task
            </a>
            <a href="/ai" class="px-4 py-2 bg-accent-purple/10 hover:bg-accent-purple/20 border border-accent-purple/20 text-accent-purple rounded-xl text-xs font-bold transition-all duration-200 flex items-center gap-2">
                <i data-lucide="sparkles" class="w-4 h-4"></i>
                AI Help
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Stat Card -->
        <div class="glass-card p-6 flex flex-col gap-4 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-accent-cyan/5 rounded-full blur-2xl group-hover:bg-accent-cyan/10 transition-colors duration-500"></div>
            <div class="w-12 h-12 rounded-2xl bg-accent-cyan/10 flex items-center justify-center text-accent-cyan">
                <i data-lucide="clipboard-list" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="text-3xl font-syne font-extrabold text-white"><?php echo $tasksDueCount; ?></div>
                <div class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Tasks Due Soon</div>
            </div>
        </div>

        <div class="glass-card p-6 flex flex-col gap-4 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl group-hover:bg-emerald-500/10 transition-colors duration-500"></div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="text-3xl font-syne font-extrabold text-white"><?php echo $doneCount; ?></div>
                <div class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Completed Tasks</div>
            </div>
        </div>

        <div class="glass-card p-6 flex flex-col gap-4 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-accent-purple/5 rounded-full blur-2xl group-hover:bg-accent-purple/10 transition-colors duration-500"></div>
            <div class="w-12 h-12 rounded-2xl bg-accent-purple/10 flex items-center justify-center text-accent-purple">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="text-3xl font-syne font-extrabold text-white"><?php echo number_format($weekHours, 1); ?></div>
                <div class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Study Hours (Wk)</div>
            </div>
        </div>

        <div class="glass-card p-6 flex flex-col gap-4 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-orange-500/5 rounded-full blur-2xl group-hover:bg-orange-500/10 transition-colors duration-500"></div>
            <div class="w-12 h-12 rounded-2xl bg-orange-500/10 flex items-center justify-center text-orange-400">
                <i data-lucide="flame" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="text-3xl font-syne font-extrabold text-white"><?php echo $user['streak'] ?? 0; ?></div>
                <div class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Learning Streak</div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Upcoming Tasks (Left 2/3) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="font-syne text-xl font-bold text-white flex items-center gap-2">
                    <i data-lucide="calendar" class="w-5 h-5 text-accent-cyan"></i>
                    Upcoming Deadlines
                </h3>
                <a href="/tasks" class="text-xs font-bold text-slate-500 hover:text-accent-cyan transition-colors uppercase tracking-widest">View All</a>
            </div>

            <div class="space-y-3">
                <?php if (!empty($upcomingTasks)): ?>
                    <?php foreach ($upcomingTasks as $task): ?>
                        <div class="glass-card p-4 flex items-center gap-4 hover:border-white/20 transition-all duration-300 group">
                            <div class="w-2 h-10 rounded-full" style="background-color: <?php echo htmlspecialchars($task['color'] ?? '#818cf8'); ?>"></div>
                            <div class="flex-1">
                                <h4 class="text-sm font-bold text-white group-hover:text-accent-cyan transition-colors"><?php echo htmlspecialchars($task['title']); ?></h4>
                                <p class="text-xs text-slate-500 mt-1"><?php echo htmlspecialchars($task['subject_name'] ?? 'General'); ?></p>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-bold text-white"><?php echo date('M d', strtotime($task['due_date'])); ?></div>
                                <div class="text-[10px] text-slate-500 uppercase tracking-tighter mt-1"><?php echo (strtotime($task['due_date']) - time() < 86400) ? '<span class="text-red-400 font-bold">Soon</span>' : 'Upcoming'; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="glass-card p-10 text-center">
                        <div class="w-16 h-16 rounded-full bg-emerald-500/10 flex items-center justify-center mx-auto mb-4 text-emerald-400">
                            <i data-lucide="check-circle" class="w-8 h-8"></i>
                        </div>
                        <h4 class="text-sm font-bold text-white">All caught up!</h4>
                        <p class="text-xs text-slate-500 mt-2">No upcoming tasks for now. Time to relax or start something new.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activity / Questions (Right 1/3) -->
        <div class="space-y-6">
            <h3 class="font-syne text-xl font-bold text-white flex items-center gap-2">
                <i data-lucide="help-circle" class="w-5 h-5 text-accent-purple"></i>
                Community Questions
            </h3>

            <div class="space-y-4">
                <?php if (!empty($recentQuestions)): ?>
                    <?php foreach (array_slice($recentQuestions, 0, 4) as $q): ?>
                        <a href="/question-bank/<?php echo (int) $q['id']; ?>" class="block glass-card p-4 hover:bg-white/5 transition-all duration-300 group">
                            <p class="text-sm text-slate-300 line-clamp-2 group-hover:text-white leading-relaxed">
                                <?php echo htmlspecialchars($q['question_text']); ?>
                            </p>
                            <div class="flex items-center justify-between mt-4">
                                <span class="text-[10px] font-bold text-accent-purple uppercase tracking-tighter">Read More</span>
                                <i data-lucide="arrow-right" class="w-3 h-3 text-slate-600 group-hover:translate-x-1 group-hover:text-accent-purple transition-all"></i>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    <a href="/question-bank" class="block text-center p-3 rounded-xl border border-dashed border-slate-800 text-xs font-bold text-slate-500 hover:border-slate-700 hover:text-slate-400 transition-all uppercase tracking-widest">
                        Browse Question Bank
                    </a>
                <?php else: ?>
                    <div class="glass-card p-8 text-center">
                        <p class="text-xs text-slate-500 italic uppercase tracking-widest">No activity found</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
