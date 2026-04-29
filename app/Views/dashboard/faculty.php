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

        </div>

        <!-- My Assigned Subjects (New) -->
        <div class="glass-card rounded-2xl border border-white/5 overflow-hidden flex flex-col">
            <div class="p-4 border-b border-white/5 bg-white/[0.02] flex justify-between items-center">
                <h2 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="book-open" class="w-4 h-4 text-accent-cyan"></i>
                    My Assigned Subjects
                </h2>
                <span class="text-[10px] text-slate-500 uppercase font-bold tracking-widest">Spring 2026</span>
            </div>
            <div class="p-4 flex-1">
                <?php if (empty($assignedSubjects)): ?>
                    <div class="flex flex-col items-center justify-center p-8 text-center opacity-40">
                        <i data-lucide="book-x" class="w-10 h-10 mb-2"></i>
                        <p class="text-xs font-medium">No subjects assigned in routine.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <?php foreach ($assignedSubjects as $subject): ?>
                            <div class="p-3 rounded-xl bg-white/5 border border-white/5 hover:border-accent-cyan/20 transition-all group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-accent-cyan/10 text-accent-cyan flex items-center justify-center text-[10px] font-bold">
                                        <?php echo explode('-', $subject['course'])[0]; ?>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-white truncate"><?php echo htmlspecialchars($subject['course']); ?></p>
                                        <p class="text-[10px] text-slate-500 font-medium">Batch <?php echo htmlspecialchars($subject['batch']); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="p-3 bg-white/[0.02] border-t border-white/5 text-center">
                <a href="/admin/attendance" class="text-[10px] font-bold text-accent-cyan uppercase tracking-widest hover:underline">Manage All Attendance →</a>
            </div>
        </div>
    </div>
</div>
