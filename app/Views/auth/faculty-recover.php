<?php
/**
 * Faculty recovery page - auth/faculty-recover.php
 */
?>
<div class="min-h-screen flex items-center justify-center relative p-6 overflow-hidden">
    <div class="absolute top-0 right-0 -z-10 w-[500px] h-[500px] bg-accent-cyan/5 blur-[120px] rounded-full translate-x-1/4 -translate-y-1/4"></div>
    <div class="absolute bottom-0 left-0 -z-10 w-[500px] h-[500px] bg-accent-purple/5 blur-[120px] rounded-full -translate-x-1/4 translate-y-1/4"></div>

    <div class="w-full max-w-md space-y-8">
        <div class="text-center">
            <h1 class="font-syne text-4xl font-extrabold bg-gradient-to-r from-accent-cyan to-accent-purple bg-clip-text text-transparent italic tracking-tight">
                Faculty Recovery
            </h1>
            <p class="mt-3 text-slate-500 font-medium text-sm uppercase tracking-widest">Reset faculty ID or password</p>
        </div>

        <div class="glass-card p-8 shadow-2xl relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-accent-cyan/5 to-accent-purple/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 -z-10"></div>

            <?php if (isset($error) && $error): ?>
                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 text-red-400 rounded-xl flex items-center gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    <span class="text-xs font-bold"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($flash) && $flash): ?>
                <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                    <span class="text-xs font-bold"><?php echo htmlspecialchars($flash['message']); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="/auth/faculty-recover" class="space-y-5">
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Faculty Verification Code</label>
                    <input
                        type="text"
                        name="faculty_code"
                        required
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 text-sm text-white focus:outline-none focus:border-accent-cyan transition-all uppercase"
                        placeholder="e.g., AAC"
                    >
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">New Login ID (Email)</label>
                    <input
                        type="email"
                        name="new_identifier"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 text-sm text-white focus:outline-none focus:border-accent-cyan transition-all"
                        placeholder="new-email@example.com"
                    >
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">New Password</label>
                    <input
                        type="password"
                        name="new_password"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 text-sm text-white focus:outline-none focus:border-accent-cyan transition-all"
                        placeholder="At least 6 characters"
                    >
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Confirm New Password</label>
                    <input
                        type="password"
                        name="confirm_password"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 text-sm text-white focus:outline-none focus:border-accent-cyan transition-all"
                        placeholder="Retype password"
                    >
                </div>

                <p class="text-[10px] text-slate-500">Fill at least one field: new login ID or new password.</p>

                <button type="submit" class="w-full py-4 bg-gradient-to-r from-accent-cyan to-accent-purple text-dark-bg font-bold rounded-xl shadow-lg shadow-accent-cyan/20 hover:scale-[1.02] active:scale-[0.98] transition-all uppercase tracking-widest text-xs">
                    Update Credentials
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="/login" class="text-xs text-accent-cyan font-bold hover:text-white transition-colors uppercase tracking-widest">
                    Back to Login
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
</script>
