<?php
/**
 * Login page - auth/login.php
 */
?>
<div class="min-h-screen flex items-center justify-center relative p-6 overflow-hidden">
    <!-- Background Decorations -->
    <div class="absolute top-0 right-0 -z-10 w-[500px] h-[500px] bg-accent-cyan/5 blur-[120px] rounded-full translate-x-1/4 -translate-y-1/4"></div>
    <div class="absolute bottom-0 left-0 -z-10 w-[500px] h-[500px] bg-accent-purple/5 blur-[120px] rounded-full -translate-x-1/4 translate-y-1/4"></div>

    <div class="w-full max-w-md space-y-8">
        <div class="text-center">
            <h1 class="font-syne text-5xl font-extrabold bg-gradient-to-r from-accent-cyan to-accent-purple bg-clip-text text-transparent italic tracking-tight">
                EduSync
            </h1>
            <p class="mt-3 text-slate-500 font-medium text-sm uppercase tracking-widest">Student Portal — MU Sylhet</p>
        </div>

        <div class="glass-card p-8 shadow-2xl relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-accent-cyan/5 to-accent-purple/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 -z-10"></div>
            
            <h2 class="text-xl font-bold text-white mb-8 text-center font-syne uppercase tracking-tighter">Sign in to your account</h2>

            <?php if (isset($error) && $error): ?>
                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 text-red-400 rounded-xl flex items-center gap-3 animate-in fade-in slide-in-from-top-2">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    <span class="text-xs font-bold"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($flash) && $flash): ?>
                <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl flex items-center gap-3 animate-in fade-in slide-in-from-top-2">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                    <span class="text-xs font-bold"><?php echo htmlspecialchars($flash['message']); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="/auth/login" class="space-y-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Student ID or Email</label>
                    <div class="relative group">
                        <i data-lucide="id-card" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 group-focus-within:text-accent-cyan transition-colors"></i>
                        <input 
                            type="text" 
                            name="identifier" 
                            required 
                            class="w-full bg-white/5 border border-white/10 rounded-xl pl-12 pr-4 py-3.5 text-sm text-white focus:outline-none focus:border-accent-cyan transition-all"
                            placeholder="e.g. 252-134-021 or your@email.com"
                        >
                    </div>
                    <p class="text-[10px] text-slate-500 px-1">Roster accounts are auto-created. Default student password is the same as the student ID until changed.</p>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between px-1">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Password</label>
                        <a href="#" class="text-[10px] font-bold text-accent-cyan hover:text-white transition-colors uppercase tracking-widest">Forgot?</a>
                    </div>
                    <div class="relative group">
                        <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 group-focus-within:text-accent-cyan transition-colors"></i>
                        <input 
                            type="password" 
                            name="password" 
                            required 
                            class="w-full bg-white/5 border border-white/10 rounded-xl pl-12 pr-4 py-3.5 text-sm text-white focus:outline-none focus:border-accent-cyan transition-all"
                            placeholder="••••••••"
                        >
                    </div>
                </div>

                <button type="submit" class="w-full py-4 bg-gradient-to-r from-accent-cyan to-accent-purple text-dark-bg font-bold rounded-xl shadow-lg shadow-accent-cyan/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2 uppercase tracking-widest text-xs">
                    Sign In →
                </button>
            </form>

            <div class="mt-8 text-center pt-8 border-t border-white/5">
                <p class="text-xs text-slate-500 font-medium tracking-tight">
                    New to EduSync? 
                    <a href="/signup" class="text-accent-cyan font-bold hover:text-white transition-colors ml-1 uppercase tracking-widest">Create an account</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
</script>
