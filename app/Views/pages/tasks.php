<?php
$currentPage = 'tasks';
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="font-syne text-3xl font-extrabold text-white mb-1">My Tasks</h2>
            <p class="text-sm text-slate-400 font-medium">Manage your assignments and deadlines</p>
        </div>
        <button onclick="openModal('addTaskModal')" class="px-5 py-2.5 bg-gradient-to-r from-accent-cyan to-accent-purple text-dark-bg font-bold rounded-xl shadow-lg shadow-accent-cyan/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center gap-2 text-sm uppercase tracking-widest">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add Task
        </button>
    </div>

    <div class="glass-card p-6">
        <!-- Filters -->
        <div class="flex gap-2 mb-6 overflow-x-auto pb-2 custom-scrollbar">
            <button class="px-4 py-2 rounded-xl text-xs font-bold transition-all bg-accent-cyan text-dark-bg border border-accent-cyan shadow-[0_0_15px_rgba(34,211,238,0.3)]">All Tasks</button>
            <button class="px-4 py-2 rounded-xl text-xs font-bold transition-all bg-white/5 text-slate-300 border border-white/10 hover:bg-white/10">Pending</button>
            <button class="px-4 py-2 rounded-xl text-xs font-bold transition-all bg-white/5 text-slate-300 border border-white/10 hover:bg-white/10">Completed</button>
        </div>

        <!-- Task List -->
        <div class="space-y-3">
            <?php if (!empty($tasks)): ?>
                <?php foreach ($tasks as $task): ?>
                    <div class="flex items-start sm:items-center gap-4 p-4 rounded-xl bg-white/[0.02] border border-white/5 hover:bg-white/[0.04] transition-colors group">
                        <div class="pt-1 sm:pt-0">
                            <label class="relative flex items-center cursor-pointer">
                                <input type="checkbox" class="peer sr-only" <?php echo $task['status'] === 'done' ? 'checked' : ''; ?>>
                                <div class="w-5 h-5 border-2 border-slate-500 rounded flex items-center justify-center peer-checked:bg-accent-cyan peer-checked:border-accent-cyan transition-all">
                                    <i data-lucide="check" class="w-3.5 h-3.5 text-dark-bg opacity-0 peer-checked:opacity-100 scale-50 peer-checked:scale-100 transition-all"></i>
                                </div>
                            </label>
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <h4 class="text-white font-bold text-sm truncate group-hover:text-accent-cyan transition-colors <?php echo $task['status'] === 'done' ? 'line-through text-slate-500' : ''; ?>">
                                <?php echo htmlspecialchars($task['title']); ?>
                            </h4>
                            <?php if (!empty($task['description'])): ?>
                                <p class="text-xs text-slate-400 truncate mt-1"><?php echo htmlspecialchars($task['description']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex items-center gap-2 whitespace-nowrap text-xs font-medium <?php echo $task['status'] === 'done' ? 'text-slate-500' : 'text-accent-purple'; ?> bg-white/5 px-2.5 py-1.5 rounded-lg border border-white/5">
                            <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                            <?php echo $task['due_date'] ? date('M d', strtotime($task['due_date'])) : 'No date'; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-12 bg-white/[0.01] rounded-xl border border-white/5 border-dashed">
                    <div class="w-16 h-16 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="check-circle" class="w-8 h-8 text-slate-500"></i>
                    </div>
                    <p class="text-slate-400 font-medium">No tasks yet. Create one to get started!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="addTaskModal" class="fixed inset-0 z-[200] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm modal-backdrop transition-opacity opacity-0" onclick="closeModal('addTaskModal')"></div>
    
    <div class="glass-card w-full max-w-md relative z-10 transform scale-95 opacity-0 transition-all duration-300 modal-content border-t-accent-cyan border-t-2 p-0 overflow-hidden">
        <div class="p-6 border-b border-white/10 bg-white/[0.02]">
            <h3 class="font-syne text-xl font-bold text-white flex items-center gap-2">
                <i data-lucide="check-square" class="text-accent-cyan w-5 h-5"></i>
                Create New Task
            </h3>
        </div>
        
        <form method="POST" action="/tasks" class="p-6 space-y-5">
            <input type="hidden" name="action" value="add">
            
            <div class="space-y-2">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Subject</label>
                <div class="relative group">
                    <i data-lucide="book" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 group-focus-within:text-accent-cyan transition-colors z-10"></i>
                    <select name="subject_id" required class="w-full bg-white/5 border border-white/10 rounded-xl pl-12 pr-4 py-3 text-sm text-white focus:outline-none focus:border-accent-cyan transition-all appearance-none relative z-0">
                        <option value="" disabled selected>Select Subject</option>
                        <?php if (!empty($subjects)): ?>
                            <?php foreach ($subjects as $sub): ?>
                                <option value="<?= $sub['id'] ?>" class="text-slate-900"><?= htmlspecialchars($sub['name']) ?></option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" class="text-slate-900">No subjects added yet</option>
                        <?php endif; ?>
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none z-10"></i>
                </div>
            </div>
            
            <div class="space-y-2">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Task Title</label>
                <div class="relative group">
                    <i data-lucide="type" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 group-focus-within:text-accent-cyan transition-colors"></i>
                    <input type="text" name="title" required placeholder="e.g. Finish Assignment 1" class="w-full bg-white/5 border border-white/10 rounded-xl pl-12 pr-4 py-3 text-sm text-white focus:outline-none focus:border-accent-cyan transition-all">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Type</label>
                    <div class="relative group">
                        <select name="type" class="w-full bg-white/5 border border-white/10 rounded-xl pl-4 pr-10 py-3 text-sm text-white focus:outline-none focus:border-accent-cyan transition-all appearance-none">
                            <option value="assignment" class="text-slate-900">Assignment</option>
                            <option value="project" class="text-slate-900">Project</option>
                            <option value="exam" class="text-slate-900">Exam</option>
                            <option value="reading" class="text-slate-900">Reading</option>
                            <option value="other" class="text-slate-900">Other</option>
                        </select>
                        <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none"></i>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Due Date</label>
                    <input type="date" name="due_date" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-accent-cyan transition-all [color-scheme:dark]">
                </div>
            </div>
            
            <div class="flex gap-3 pt-4 border-t border-white/5">
                <button type="button" onclick="closeModal('addTaskModal')" class="flex-1 py-3 bg-white/5 hover:bg-white/10 text-white text-xs font-bold uppercase tracking-widest rounded-xl transition-colors border border-white/10">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-accent-cyan to-accent-purple text-dark-bg text-xs font-bold uppercase tracking-widest rounded-xl shadow-lg shadow-accent-cyan/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                    Save Task
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Modal Logic
    function openModal(id) {
        const modal = document.getElementById(id);
        const backdrop = modal.querySelector('.modal-backdrop');
        const content = modal.querySelector('.modal-content');
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Trigger reflow
        void modal.offsetWidth;
        
        backdrop.classList.remove('opacity-0');
        backdrop.classList.add('opacity-100');
        
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        const backdrop = modal.querySelector('.modal-backdrop');
        const content = modal.querySelector('.modal-content');
        
        backdrop.classList.remove('opacity-100');
        backdrop.classList.add('opacity-0');
        
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 300);
    }

    // Dynamic Task Title Logic
    const subjectSelect = document.querySelector('select[name="subject_id"]');
    const titleInput = document.querySelector('input[name="title"]');
    const datalistId = 'task-title-suggestions';

    if (subjectSelect && titleInput) {
        let datalist = document.createElement('datalist');
        datalist.id = datalistId;
        document.body.appendChild(datalist);
        titleInput.setAttribute('list', datalistId);

        function updateTitleSuggestions() {
            const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
            if (!selectedOption || !selectedOption.value) return;
            
            const subjectName = selectedOption.text.toLowerCase();
            let suggestions = [];
            
            if (subjectName.includes('lab') || subjectName.includes('practical')) {
                suggestions = ['Lab Report 1', 'Lab Report 2', 'Lab Final Project', 'Practical Assignment', 'Code Submission'];
            } else if (subjectName.includes('mat-') || subjectName.includes('math') || subjectName.includes('calculus')) {
                suggestions = ['Problem Set 1', 'Problem Set 2', 'Midterm Practice', 'Formula Sheet Preparation'];
            } else if (subjectName.includes('project') || subjectName.includes('thesis')) {
                suggestions = ['Project Proposal', 'Progress Report 1', 'Final Presentation', 'Project Documentation'];
            } else {
                suggestions = ['Assignment 1', 'Assignment 2', 'Midterm Prep', 'Final Exam Prep', 'Reading Summary', 'Presentation Slides'];
            }
            
            datalist.innerHTML = '';
            suggestions.forEach(s => {
                const option = document.createElement('option');
                option.value = s;
                datalist.appendChild(option);
            });
            
            if (!titleInput.value || titleInput.value.startsWith('Assignment') || titleInput.value.startsWith('Lab') || titleInput.value.startsWith('e.g.')) {
                titleInput.placeholder = "e.g. " + suggestions[0];
            }
        }

        subjectSelect.addEventListener('change', updateTitleSuggestions);
        updateTitleSuggestions();
    }
</script>
