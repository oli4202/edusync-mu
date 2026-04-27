<div class="space-y-6">
    <div class="flex justify-between items-end">
        <div>
            <h1 class="text-2xl font-bold text-white font-syne">Faculty Dashboard</h1>
            <p class="text-sm text-slate-400 mt-1">Welcome back, <?php echo htmlspecialchars($user['name']); ?></p>
        </div>
        <div class="text-right">
            <p class="text-xs text-slate-500 uppercase tracking-widest font-bold">Today</p>
            <p class="text-sm text-white font-medium"><?php echo date('l, M j, Y'); ?></p>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="glass-card p-5 rounded-2xl border border-white/5 relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-accent-cyan/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-accent-cyan/10 text-accent-cyan flex items-center justify-center">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-1">Total Students</p>
                    <p class="text-2xl font-bold text-white"><?php echo $studentCount ?? 0; ?></p>
                </div>
            </div>
        </div>

        <div class="glass-card p-5 rounded-2xl border border-white/5 relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-accent-purple/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-accent-purple/10 text-accent-purple flex items-center justify-center">
                    <i data-lucide="clipboard-check" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-1">Today's Attendance</p>
                    <p class="text-2xl font-bold text-white"><?php echo $todayAttendanceCount ?? 0; ?></p>
                </div>
            </div>
        </div>

        <div class="glass-card p-5 rounded-2xl border border-white/5 relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-yellow-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-yellow-500/10 text-yellow-500 flex items-center justify-center">
                    <i data-lucide="help-circle" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-1">Pending Questions</p>
                    <p class="text-2xl font-bold text-white"><?php echo count($pendingQuestions ?? []); ?></p>
                </div>
            </div>
        </div>

        <div class="glass-card p-5 rounded-2xl border border-white/5 relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-green-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-green-500/10 text-green-500 flex items-center justify-center">
                    <i data-lucide="message-square" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-1">Pending Answers</p>
                    <p class="text-2xl font-bold text-white"><?php echo count($pendingAnswers ?? []); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Quick Actions -->
        <div class="glass-card rounded-2xl border border-white/5 overflow-hidden flex flex-col">
            <div class="p-4 border-b border-white/5 bg-white/[0.02]">
                <h2 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="zap" class="w-4 h-4 text-accent-purple"></i>
                    Quick Actions
                </h2>
            </div>
            <div class="p-6 grid grid-cols-2 gap-4">
                <a href="/admin/attendance" class="flex flex-col items-center justify-center gap-3 p-4 rounded-xl bg-white/5 hover:bg-white/10 border border-white/5 hover:border-accent-cyan/30 transition-all group">
                    <div class="w-10 h-10 rounded-full bg-accent-cyan/20 text-accent-cyan flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs font-medium text-slate-300">Mark Attendance</span>
                </a>
                
                <a href="/announcements" class="flex flex-col items-center justify-center gap-3 p-4 rounded-xl bg-white/5 hover:bg-white/10 border border-white/5 hover:border-accent-purple/30 transition-all group">
                    <div class="w-10 h-10 rounded-full bg-accent-purple/20 text-accent-purple flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i data-lucide="megaphone" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs font-medium text-slate-300">Post Announcement</span>
                </a>
                
                <a href="/admin/students" class="flex flex-col items-center justify-center gap-3 p-4 rounded-xl bg-white/5 hover:bg-white/10 border border-white/5 hover:border-accent-emerald/30 transition-all group">
                    <div class="w-10 h-10 rounded-full bg-accent-emerald/20 text-accent-emerald flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i data-lucide="search" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs font-medium text-slate-300">Find Student</span>
                </a>

                <?php if ($this->session->userRole() === 'admin'): ?>
                <a href="/admin" class="flex flex-col items-center justify-center gap-3 p-4 rounded-xl bg-white/5 hover:bg-white/10 border border-white/5 hover:border-red-400/30 transition-all group">
                    <div class="w-10 h-10 rounded-full bg-red-400/20 text-red-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i data-lucide="shield" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs font-medium text-slate-300">Admin Panel (Pending Approvals)</span>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pending Approvals Preview -->
        <div class="glass-card rounded-2xl border border-white/5 overflow-hidden flex flex-col">
            <div class="p-4 border-b border-white/5 bg-white/[0.02] flex justify-between items-center">
                <h2 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="clock" class="w-4 h-4 text-yellow-500"></i>
                    Pending Approvals
                </h2>
                <?php if ($this->session->userRole() === 'admin'): ?>
                <a href="/admin" class="text-xs text-accent-cyan hover:underline">View All</a>
                <?php endif; ?>
            </div>
            <div class="p-0 flex-1 flex flex-col">
                <?php if (empty($pendingQuestions) && empty($pendingAnswers)): ?>
                    <div class="flex-1 flex flex-col items-center justify-center p-8 text-center opacity-50">
                        <i data-lucide="check-circle" class="w-12 h-12 text-green-500 mb-3"></i>
                        <p class="text-sm font-medium text-white">All caught up!</p>
                        <p class="text-xs text-slate-400">No pending items to approve.</p>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-white/5 overflow-y-auto max-h-[300px] custom-scrollbar">
                        <?php foreach(array_slice($pendingQuestions, 0, 3) as $q): ?>
                        <div class="p-4 hover:bg-white/5 transition-colors flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-yellow-500/20 text-yellow-500 flex items-center justify-center shrink-0">
                                <i data-lucide="help-circle" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-white truncate max-w-[200px] sm:max-w-[300px]"><?php echo htmlspecialchars($q['course_code']); ?> Question</p>
                                <p class="text-[10px] text-slate-400 mt-1 line-clamp-1"><?php echo htmlspecialchars($q['question_text']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php foreach(array_slice($pendingAnswers, 0, 3) as $a): ?>
                        <div class="p-4 hover:bg-white/5 transition-colors flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-green-500/20 text-green-500 flex items-center justify-center shrink-0">
                                <i data-lucide="message-square" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-white truncate max-w-[200px] sm:max-w-[300px]">Answer to Q#<?php echo $a['question_id']; ?></p>
                                <p class="text-[10px] text-slate-400 mt-1 line-clamp-1"><?php echo htmlspecialchars($a['compact_answer']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
