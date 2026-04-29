<?php
/**
 * Signup page - auth/signup.php
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
            <p class="mt-3 text-slate-500 font-medium text-sm uppercase tracking-widest">Join the Community</p>
        </div>

        <div class="glass-card p-8 shadow-2xl relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-accent-cyan/5 to-accent-purple/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 -z-10"></div>
            
            <h2 class="text-xl font-bold text-white mb-8 text-center font-syne uppercase tracking-tighter">Create your account</h2>

            <?php if (isset($error) && $error): ?>
                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 text-red-400 rounded-xl flex items-center gap-3 animate-in fade-in slide-in-from-top-2">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    <span class="text-xs font-bold"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="/auth/signup" class="space-y-5">
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Full Name</label>
                    
                    <!-- Student Name Input -->
                    <div class="relative group" id="studentNameContainer">
                        <i data-lucide="user" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 group-focus-within:text-accent-cyan transition-colors"></i>
                        <input 
                            type="text" 
                            name="name_student" 
                            id="nameInput"
                            class="w-full bg-white/5 border border-white/10 rounded-xl pl-12 pr-4 py-3 text-sm text-white focus:outline-none focus:border-accent-cyan transition-all"
                            placeholder="John Doe"
                            value="<?php echo htmlspecialchars($name ?? ''); ?>"
                        >
                    </div>

                    <!-- Faculty Name Select -->
                    <div class="relative group hidden" id="facultyNameContainer">
                        <i data-lucide="user-check" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 group-focus-within:text-accent-cyan transition-colors z-10"></i>
                        <select name="name_faculty" id="facultyNameSelect" class="w-full bg-white/5 border border-white/10 rounded-xl pl-12 pr-4 py-3 text-sm text-white focus:outline-none focus:border-accent-cyan transition-all appearance-none relative z-0">
                            <option value="" disabled selected>Select your official name</option>
                            <?php if (isset($facultyRoster)): ?>
                                <?php foreach ($facultyRoster as $code => $faculty): ?>
                                    <option value="<?php echo htmlspecialchars($faculty['name']); ?>" class="text-slate-900">
                                        <?php echo htmlspecialchars($faculty['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none z-10"></i>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Email Address</label>
                    <div class="relative group">
                        <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 group-focus-within:text-accent-cyan transition-colors"></i>
                        <input 
                            type="email" 
                            name="email" 
                            required 
                            class="w-full bg-white/5 border border-white/10 rounded-xl pl-12 pr-4 py-3 text-sm text-white focus:outline-none focus:border-accent-cyan transition-all"
                            placeholder="your@email.com"
                            value="<?php echo htmlspecialchars($email ?? ''); ?>"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Password</label>
                        <input 
                            type="password" 
                            name="password" 
                            required 
                            minlength="6"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-accent-cyan transition-all"
                            placeholder="••••••••"
                        >
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Confirm</label>
                        <input 
                            type="password" 
                            name="confirm_password" 
                            required 
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-accent-cyan transition-all"
                            placeholder="••••••••"
                        >
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Role</label>
                    <div class="relative group">
                        <i data-lucide="briefcase" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 group-focus-within:text-accent-cyan transition-colors z-10"></i>
                        <select name="role" id="roleSelect" required class="w-full bg-white/5 border border-white/10 rounded-xl pl-12 pr-4 py-3 text-sm text-white focus:outline-none focus:border-accent-cyan transition-all appearance-none relative z-0">
                            <option value="student" class="text-slate-900">Student</option>
                            <option value="faculty" class="text-slate-900">Faculty</option>
                        </select>
                        <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none z-10"></i>
                    </div>
                </div>

                <div class="space-y-2" id="studentIdContainer">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Student ID</label>
                    <div class="relative group">
                        <i data-lucide="id-card" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 group-focus-within:text-accent-cyan transition-colors"></i>
                        <input 
                            type="text" 
                            name="student_id" 
                            id="studentIdInput"
                            class="w-full bg-white/5 border border-white/10 rounded-xl pl-12 pr-4 py-3 text-sm text-white focus:outline-none focus:border-accent-cyan transition-all"
                            placeholder="e.g. 252-134-021"
                        >
                    </div>
                    
                    <!-- Student Info Preview -->
                    <div id="studentPreview" class="hidden mt-3 p-3 bg-accent-cyan/10 border border-accent-cyan/20 rounded-xl animate-in fade-in slide-in-from-top-1">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-accent-cyan/20 flex items-center justify-center">
                                <i data-lucide="check" class="w-4 h-4 text-accent-cyan"></i>
                            </div>
                            <div>
                                <p id="previewName" class="text-xs font-bold text-white"></p>
                                <p id="previewBatch" class="text-[10px] text-accent-cyan uppercase tracking-wider font-bold"></p>
                            </div>
                        </div>
                    </div>

                    <p id="studentHint" class="text-[10px] text-slate-500 px-1">Student accounts sync batch and semester from the official roster automatically.</p>
                </div>

                <div class="space-y-2 hidden" id="facultySecretContainer">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Faculty Short Form</label>
                    <div class="relative group">
                        <i data-lucide="shield-check" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 group-focus-within:text-accent-cyan transition-colors"></i>
                        <input 
                            type="text" 
                            name="faculty_secret" 
                            id="facultySecretInput"
                            class="w-full bg-white/5 border border-white/10 rounded-xl pl-12 pr-4 py-3 text-sm text-white focus:outline-none focus:border-accent-cyan transition-all"
                            placeholder="e.g. AAC or NSC"
                        >
                    </div>
                    <p class="text-[10px] text-slate-500 px-1">Used to verify your identity and automatically link your classes.</p>
                </div>

                <button type="submit" class="w-full mt-4 py-4 bg-gradient-to-r from-accent-cyan to-accent-purple text-dark-bg font-bold rounded-xl shadow-lg shadow-accent-cyan/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2 uppercase tracking-widest text-xs">
                    Create Account →
                </button>
            </form>

            <div class="mt-8 text-center pt-8 border-t border-white/5">
                <p class="text-xs text-slate-500 font-medium tracking-tight">
                    Already have an account? 
                    <a href="/login" class="text-accent-cyan font-bold hover:text-white transition-colors ml-1 uppercase tracking-widest">Sign in</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();

    const roleSelect = document.getElementById('roleSelect');
    const studentIdContainer = document.getElementById('studentIdContainer');
    const studentIdInput = document.getElementById('studentIdInput');
    const facultySecretContainer = document.getElementById('facultySecretContainer');
    const facultySecretInput = document.getElementById('facultySecretInput');
    const studentPreview = document.getElementById('studentPreview');
    const previewName = document.getElementById('previewName');
    const previewBatch = document.getElementById('previewBatch');
    
    const studentNameContainer = document.getElementById('studentNameContainer');
    const nameInput = document.getElementById('nameInput');
    const facultyNameContainer = document.getElementById('facultyNameContainer');
    const facultyNameSelect = document.getElementById('facultyNameSelect');

    function toggleStudentId() {
        if (roleSelect.value === 'faculty') {
            studentIdContainer.classList.add('hidden');
            studentIdInput.removeAttribute('required');
            studentPreview.classList.add('hidden');
            
            facultySecretContainer.classList.remove('hidden');
            facultySecretInput.setAttribute('required', '');

            studentNameContainer.classList.add('hidden');
            nameInput.removeAttribute('name');
            nameInput.removeAttribute('required');

            facultyNameContainer.classList.remove('hidden');
            facultyNameSelect.setAttribute('name', 'name');
            facultyNameSelect.setAttribute('required', '');

        } else {
            studentIdContainer.classList.remove('hidden');
            studentIdInput.setAttribute('required', '');
            
            facultySecretContainer.classList.add('hidden');
            facultySecretInput.removeAttribute('required');

            studentNameContainer.classList.remove('hidden');
            nameInput.setAttribute('name', 'name');
            nameInput.setAttribute('required', '');

            facultyNameContainer.classList.add('hidden');
            facultyNameSelect.removeAttribute('name');
            facultyNameSelect.removeAttribute('required');
        }
    }

    // Lookup Student ID
    let lookupTimeout;
    studentIdInput.addEventListener('input', function() {
        clearTimeout(lookupTimeout);
        const sid = this.value.trim();
        
        if (sid.length < 5) {
            studentPreview.classList.add('hidden');
            return;
        }

        lookupTimeout = setTimeout(() => {
            fetch(`/api/students/lookup?student_id=${encodeURIComponent(sid)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        previewName.textContent = data.data.name;
                        const season = data.data.season ? ` (${data.data.season})` : '';
                        previewBatch.textContent = `Batch ${data.data.batch} • Semester ${data.data.semester}${season}`;
                        studentPreview.classList.remove('hidden');
                        
                        // Auto-fill name if empty or default
                        if (!nameInput.value || nameInput.value === 'John Doe') {
                            nameInput.value = data.data.name;
                        }
                    } else {
                        studentPreview.classList.add('hidden');
                    }
                })
                .catch(() => {
                    studentPreview.classList.add('hidden');
                });
        }, 500);
    });

    roleSelect.addEventListener('change', toggleStudentId);
    
    // Initial state
    toggleStudentId();
</script>
