<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'EduSync MU'; ?></title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    
    <!-- Frameworks -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            bg: '#0a0e1a',
                            card: '#111827',
                            border: '#1e2d45',
                        },
                        accent: {
                            cyan: '#22d3ee',
                            purple: '#818cf8',
                            emerald: '#34d399',
                        }
                    },
                    fontFamily: {
                        syne: ['Syne', 'sans-serif'],
                        sans: ['DM Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <style type="text/tailwindcss">
        @layer components {
            .glass-card {
                @apply bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl;
            }
            .nav-link-active {
                @apply bg-gradient-to-r from-accent-cyan/10 to-accent-purple/10 border-accent-purple/20 text-white;
            }
        }
    </style>
    
    <?php if (isset($extraStyles)) echo $extraStyles; ?>
</head>
<body class="bg-[#0a0e1a] text-slate-200 font-sans antialiased min-h-screen" x-data="{ sidebarOpen: true }">
    <div class="flex">
        <?php if (isset($user) && $user): ?>
            <!-- Sidebar -->
            <aside 
                class="fixed inset-y-0 left-0 z-50 w-72 bg-[#0f172a]/95 backdrop-blur-xl border-r border-slate-800 transition-transform duration-300 transform lg:translate-x-0 lg:static lg:inset-0"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            >
                <div class="flex flex-col h-full p-6">
                    <div class="mb-10">
                        <h1 class="font-syne text-2xl font-extrabold bg-gradient-to-r from-accent-cyan to-accent-purple bg-clip-text text-transparent italic">
                            EduSync
                        </h1>
                        <p class="text-[10px] text-slate-500 uppercase tracking-[0.2em] mt-1">METROPOLITAN UNIVERSITY</p>
                    </div>

                    <nav class="flex-1 space-y-1 overflow-y-auto pr-2 custom-scrollbar">
                        <!-- General (Shared) -->
                        <div class="text-[10px] font-bold text-accent-cyan uppercase tracking-widest px-4 mb-2 opacity-60">General</div>

                        <a href="/dashboard" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 group <?php echo ($currentPage ?? '') === 'dashboard' ? 'nav-link-active' : 'text-slate-400 hover:bg-white/5 hover:text-white'; ?>">
                            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Dashboard</span>
                        </a>

                        <?php if ($this->session->isStudent()): ?>
                        <a href="/analytics" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 group <?php echo ($currentPage ?? '') === 'analytics' ? 'nav-link-active' : 'text-slate-400 hover:bg-white/5 hover:text-white'; ?>">
                            <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Analytics</span>
                        </a>

                        <!-- Academic (Student) -->
                        <div class="pt-3 pb-1">
                            <div class="text-[10px] font-bold text-accent-cyan uppercase tracking-widest px-4 mb-2 opacity-60">Academic</div>
                        </div>

                        <a href="/subjects" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 group <?php echo ($currentPage ?? '') === 'subjects' ? 'nav-link-active' : 'text-slate-400 hover:bg-white/5 hover:text-white'; ?>">
                            <i data-lucide="book-open" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Subjects</span>
                        </a>

                        <a href="/tasks" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 group <?php echo ($currentPage ?? '') === 'tasks' ? 'nav-link-active' : 'text-slate-400 hover:bg-white/5 hover:text-white'; ?>">
                            <i data-lucide="check-square" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Tasks</span>
                        </a>

                        <a href="/grades" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 group <?php echo ($currentPage ?? '') === 'grades' ? 'nav-link-active' : 'text-slate-400 hover:bg-white/5 hover:text-white'; ?>">
                            <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Grades</span>
                        </a>

                        <a href="/attendance" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 group <?php echo ($currentPage ?? '') === 'attendance' ? 'nav-link-active' : 'text-slate-400 hover:bg-white/5 hover:text-white'; ?>">
                            <i data-lucide="calendar-check" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Attendance</span>
                        </a>
                        <?php endif; ?>

                        <?php if ($this->session->isFaculty()): ?>
                        <!-- Class Management (Faculty) -->
                        <div class="pt-3 pb-1">
                            <div class="text-[10px] font-bold text-accent-cyan uppercase tracking-widest px-4 mb-2 opacity-60">Class Management</div>
                        </div>

                        <a href="/admin/attendance" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 group <?php echo ($currentPage ?? '') === 'attendance' ? 'nav-link-active' : 'text-slate-400 hover:bg-white/5 hover:text-white'; ?>">
                            <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Mark Attendance</span>
                        </a>
                        <?php endif; ?>

                        <a href="/routine" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 group <?php echo ($currentPage ?? '') === 'routine' ? 'nav-link-active' : 'text-slate-400 hover:bg-white/5 hover:text-white'; ?>">
                            <i data-lucide="clock" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Routine</span>
                        </a>

                        <!-- Shared Calendar -->
                        <a href="/calendar" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 group <?php echo ($currentPage ?? '') === 'calendar' ? 'nav-link-active' : 'text-slate-400 hover:bg-white/5 hover:text-white'; ?>">
                            <i data-lucide="calendar" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Calendar</span>
                        </a>

                        <!-- Resources (Shared) -->
                        <div class="pt-3 pb-1">
                            <div class="text-[10px] font-bold text-accent-cyan uppercase tracking-widest px-4 mb-2 opacity-60">Resources</div>
                        </div>

                        <a href="/question-bank" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 group <?php echo ($currentPage ?? '') === 'question-bank' ? 'nav-link-active' : 'text-slate-400 hover:bg-white/5 hover:text-white'; ?>">
                            <i data-lucide="database" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Question Bank</span>
                        </a>

                        <?php if ($this->session->isStudent()): ?>
                        <a href="/question-bank/submit" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 group <?php echo ($currentPage ?? '') === 'submit-question' ? 'nav-link-active' : 'text-slate-400 hover:bg-white/5 hover:text-white'; ?>">
                            <i data-lucide="file-plus" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Submit Question</span>
                        </a>

                        <a href="/flashcards" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 group <?php echo ($currentPage ?? '') === 'flashcards' ? 'nav-link-active' : 'text-slate-400 hover:bg-white/5 hover:text-white'; ?>">
                            <i data-lucide="layers" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Flashcards</span>
                        </a>
                        <?php endif; ?>

                        <a href="/learn" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 group <?php echo ($currentPage ?? '') === 'learn' ? 'nav-link-active' : 'text-slate-400 hover:bg-white/5 hover:text-white'; ?>">
                            <i data-lucide="book-marked" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Learn</span>
                        </a>

                        <?php if ($this->session->isStudent()): ?>
                        <!-- AI Tools (Student) -->
                        <div class="pt-3 pb-1">
                            <div class="text-[10px] font-bold text-accent-cyan uppercase tracking-widest px-4 mb-2 opacity-60">AI Tools</div>
                        </div>

                        <a href="/ai" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 group <?php echo ($currentPage ?? '') === 'ai' ? 'nav-link-active' : 'text-slate-400 hover:bg-white/5 hover:text-white'; ?>">
                            <i data-lucide="bot" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">AI Assistant</span>
                        </a>

                        <a href="/suggestions" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 group <?php echo ($currentPage ?? '') === 'suggestions' ? 'nav-link-active' : 'text-slate-400 hover:bg-white/5 hover:text-white'; ?>">
                            <i data-lucide="lightbulb" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Suggestions</span>
                        </a>

                        <a href="/playground" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 group <?php echo ($currentPage ?? '') === 'playground' ? 'nav-link-active' : 'text-slate-400 hover:bg-white/5 hover:text-white'; ?>">
                            <i data-lucide="terminal" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Playground</span>
                        </a>
                        <?php endif; ?>

                        <!-- Community (Shared/Student) -->
                        <div class="pt-3 pb-1">
                            <div class="text-[10px] font-bold text-accent-cyan uppercase tracking-widest px-4 mb-2 opacity-60">Community</div>
                        </div>

                        <?php if ($this->session->isStudent()): ?>
                        <a href="/groups" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 group <?php echo ($currentPage ?? '') === 'groups' ? 'nav-link-active' : 'text-slate-400 hover:bg-white/5 hover:text-white'; ?>">
                            <i data-lucide="users" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Study Groups</span>
                        </a>
                        <?php endif; ?>

                        <a href="/announcements" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 group <?php echo ($currentPage ?? '') === 'announcements' ? 'nav-link-active' : 'text-slate-400 hover:bg-white/5 hover:text-white'; ?>">
                            <i data-lucide="megaphone" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Announcements</span>
                        </a>

                        <?php if ($this->session->isStudent()): ?>
                        <a href="/partners" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 group <?php echo ($currentPage ?? '') === 'partners' ? 'nav-link-active' : 'text-slate-400 hover:bg-white/5 hover:text-white'; ?>">
                            <i data-lucide="user-plus" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Partners</span>
                        </a>

                        <!-- Career & Finance (Student) -->
                        <div class="pt-3 pb-1">
                            <div class="text-[10px] font-bold text-accent-cyan uppercase tracking-widest px-4 mb-2 opacity-60">Career & Finance</div>
                        </div>

                        <a href="/jobs" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 group <?php echo ($currentPage ?? '') === 'jobs' ? 'nav-link-active' : 'text-slate-400 hover:bg-white/5 hover:text-white'; ?>">
                            <i data-lucide="briefcase" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Jobs</span>
                        </a>

                        <a href="/fees" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 group <?php echo ($currentPage ?? '') === 'fees' ? 'nav-link-active' : 'text-slate-400 hover:bg-white/5 hover:text-white'; ?>">
                            <i data-lucide="credit-card" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Fees</span>
                        </a>
                        <?php endif; ?>

                        <a href="/prospectus" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 group <?php echo ($currentPage ?? '') === 'prospectus' ? 'nav-link-active' : 'text-slate-400 hover:bg-white/5 hover:text-white'; ?>">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                            <span class="text-sm font-medium">Prospectus</span>
                        </a>

                        <!-- Administration (admin only) -->
                        <?php if ($this->session->userRole() === 'admin'): ?>
                            <div class="pt-3 pb-1">
                                <div class="text-[10px] font-bold text-red-400 uppercase tracking-widest px-4 mb-2 opacity-60">Administration</div>
                            </div>
                            <a href="/admin" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 group <?php echo ($currentPage ?? '') === 'admin' ? 'nav-link-active' : 'text-slate-400 hover:bg-white/5 hover:text-white'; ?>">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                                <span class="text-sm font-medium">Admin Panel</span>
                            </a>
                        <?php endif; ?>
                    </nav>

                    <div class="mt-auto pt-6 border-t border-slate-800">
                        <div class="flex items-center gap-3 px-2 mb-4">
                            <img
                                src="<?php echo htmlspecialchars(avatarUrl($user['avatar'] ?? '', $user['name'] ?? 'User')); ?>"
                                alt="<?php echo htmlspecialchars($user['name'] ?? 'User'); ?>"
                                class="w-10 h-10 rounded-xl object-cover border border-slate-700 shadow-lg shadow-accent-cyan/20"
                            >
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold truncate"><?php echo htmlspecialchars($user['name'] ?? ''); ?></p>
                                <p class="text-[10px] text-slate-500 truncate uppercase tracking-tighter"><?php echo htmlspecialchars($user['role'] ?? 'Student'); ?></p>
                            </div>
                        </div>
                        <a href="/logout" class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-800 bg-white/5 hover:bg-red-500/10 hover:border-red-500/20 hover:text-red-400 transition-all duration-200 text-xs font-semibold">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col min-h-screen relative overflow-hidden">
                <!-- Background Decorations -->
                <div class="absolute top-0 right-0 -z-10 w-96 h-96 bg-accent-cyan/10 blur-[120px] rounded-full translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 left-0 -z-10 w-96 h-96 bg-accent-purple/10 blur-[120px] rounded-full -translate-x-1/2 translate-y-1/2"></div>

                <!-- Top Header -->
                <header class="h-20 flex items-center justify-between px-8 bg-[#0a0e1a]/50 backdrop-blur-md sticky top-0 z-40 border-b border-white/5">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-slate-400 hover:text-white">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>

                    <div class="flex-1 flex items-center justify-end gap-6">
                        <div class="hidden md:flex items-center gap-4 text-xs font-medium text-slate-400">
                            <span class="flex items-center gap-1.5"><i data-lucide="flame" class="w-4 h-4 text-orange-500"></i> <?php echo $user['streak'] ?? 0; ?> Day Streak</span>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 p-8">
                    <?php if (isset($flash) && $flash): ?>
                        <div 
                            x-data="{ show: true }" 
                            x-show="show" 
                            x-init="setTimeout(() => show = false, 5000)"
                            class="mb-8 p-4 rounded-xl flex items-center gap-3 animate-in fade-in slide-in-from-top-4 duration-300 <?php echo $flash['type'] === 'success' ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-400' : 'bg-red-500/10 border border-red-500/20 text-red-400'; ?>"
                        >
                            <i data-lucide="<?php echo $flash['type'] === 'success' ? 'check-circle' : 'alert-circle'; ?>" class="w-5 h-5"></i>
                            <span class="text-sm font-medium"><?php echo htmlspecialchars($flash['message']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="animate-in fade-in duration-700">
                        <?php echo $content; ?>
                    </div>
                </main>
            </div>
        <?php else: ?>
            <!-- Auth Pages -->
            <main class="flex-1">
                <?php echo $content; ?>
            </main>
        <?php endif; ?>
    </div>

    <!-- Scripts -->
    <script>
        lucide.createIcons();
    </script>
    <script src="/assets/js/main.js"></script>
    <?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>

