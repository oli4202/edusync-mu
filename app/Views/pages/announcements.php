<?php 
$currentPage = 'announcements'; 
$typeColors = [
    'general'    => ['bg-accent-cyan/10 text-accent-cyan border-accent-cyan/20', '📢'],
    'exam'       => ['bg-red-500/10 text-red-400 border-red-500/20', '📝'],
    'assignment' => ['bg-accent-purple/10 text-accent-purple border-accent-purple/20', '📋'],
    'event'      => ['bg-emerald-500/10 text-emerald-400 border-emerald-500/20', '🎉'],
    'urgent'     => ['bg-orange-500/10 text-orange-400 border-orange-500/20 shadow-[0_0_15px_rgba(251,191,36,0.1)]', '🚨'],
];
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div>
            <h1 class="font-syne text-3xl font-extrabold text-white mb-1 tracking-tight flex items-center gap-3">
                <i data-lucide="megaphone" class="w-8 h-8 text-accent-cyan"></i>
                Announcements
            </h1>
            <p class="text-sm text-slate-400 font-medium">Department notices, exam alerts, and academic updates</p>
        </div>
        <?php if ($isFaculty): ?>
        <button onclick="openModal('postModal')" class="btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Post Announcement
        </button>
        <?php endif; ?>
    </div>

    <!-- Filter Tabs -->
    <div class="flex flex-wrap gap-2 pb-2 overflow-x-auto custom-scrollbar">
        <button class="tab-btn px-5 py-2 rounded-xl text-xs font-bold transition-all border bg-accent-cyan text-dark-bg border-accent-cyan shadow-[0_0_15px_rgba(34,211,238,0.3)]" onclick="filterAnn('all', this)">All</button>
        <button class="tab-btn px-5 py-2 rounded-xl text-xs font-bold transition-all border border-white/10 text-slate-400 hover:bg-white/5 hover:text-white" onclick="filterAnn('urgent', this)">🚨 Urgent</button>
        <button class="tab-btn px-5 py-2 rounded-xl text-xs font-bold transition-all border border-white/10 text-slate-400 hover:bg-white/5 hover:text-white" onclick="filterAnn('exam', this)">📝 Exam</button>
        <button class="tab-btn px-5 py-2 rounded-xl text-xs font-bold transition-all border border-white/10 text-slate-400 hover:bg-white/5 hover:text-white" onclick="filterAnn('assignment', this)">📋 Assignment</button>
        <button class="tab-btn px-5 py-2 rounded-xl text-xs font-bold transition-all border border-white/10 text-slate-400 hover:bg-white/5 hover:text-white" onclick="filterAnn('event', this)">🎉 Event</button>
        <button class="tab-btn px-5 py-2 rounded-xl text-xs font-bold transition-all border border-white/10 text-slate-400 hover:bg-white/5 hover:text-white" onclick="filterAnn('general', this)">📢 General</button>
    </div>

    <!-- Announcements List -->
    <?php if (empty($announcements)): ?>
    <div class="glass-card p-16 text-center">
        <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-6">
            <i data-lucide="inbox" class="w-10 h-10 text-slate-600"></i>
        </div>
        <h3 class="font-syne text-xl font-bold text-white mb-2">No announcements yet</h3>
        <p class="text-slate-500 text-sm max-w-xs mx-auto">When official notices are posted, they will appear here for everyone to see.</p>
        <?php if ($isFaculty): ?>
        <button onclick="openModal('postModal')" class="mt-6 btn-primary">Post First Announcement</button>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div id="annList" class="grid grid-cols-1 gap-6">
        <?php foreach ($announcements as $a):
            [$tClasses, $tIcon] = $typeColors[$a['type']] ?? $typeColors['general'];
            $isUrgent = $a['type'] === 'urgent';
        ?>
        <div class="glass-card overflow-hidden group transition-all duration-300 <?= $a['is_pinned'] ? 'border-l-4 border-l-accent-cyan ring-1 ring-accent-cyan/10' : '' ?> <?= $isUrgent ? 'border-l-4 border-l-orange-500 ring-1 ring-orange-500/10' : '' ?> ann-item" data-type="<?= $a['type'] ?>">
            <div class="p-6 md:p-8">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border <?= $tClasses ?>">
                            <span><?= $tIcon ?></span>
                            <?= ucfirst($a['type']) ?>
                        </span>
                        <?php if ($a['is_pinned']): ?>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-accent-cyan/10 text-accent-cyan text-[10px] font-bold border border-accent-cyan/20">
                            <i data-lucide="pin" class="w-3 h-3"></i>
                            PINNED
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="text-[11px] font-bold text-slate-500 flex items-center gap-1.5 bg-white/5 px-2.5 py-1 rounded-lg">
                        <i data-lucide="calendar" class="w-3 h-3"></i>
                        <?= date('M j, Y', strtotime($a['created_at'])) ?>
                    </div>
                </div>

                <h3 class="font-syne text-xl font-bold text-white mb-4 group-hover:text-accent-cyan transition-colors">
                    <?= htmlspecialchars($a['title']) ?>
                </h3>
                
                <div class="text-slate-300 text-sm leading-relaxed mb-6 whitespace-pre-wrap">
                    <?= htmlspecialchars($a['content']) ?>
                </div>

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pt-6 border-t border-white/5">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-accent-purple/20 flex items-center justify-center text-accent-purple">
                                <i data-lucide="user" class="w-3.5 h-3.5"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-400"><?= htmlspecialchars($a['posted_by']) ?></span>
                        </div>
                        
                        <?php if ($a['target_semester'] > 0): ?>
                        <div class="flex items-center gap-2 text-xs font-bold text-slate-400">
                            <i data-lucide="graduation-cap" class="w-3.5 h-3.5 text-accent-cyan"></i>
                            Semester <?= $a['target_semester'] ?>
                        </div>
                        <?php else: ?>
                        <div class="flex items-center gap-2 text-xs font-bold text-slate-400">
                            <i data-lucide="users" class="w-3.5 h-3.5 text-accent-cyan"></i>
                            Everyone
                        </div>
                        <?php endif; ?>

                        <?php if ($a['expires_at']): ?>
                        <div class="flex items-center gap-2 text-xs font-bold text-orange-400/80 bg-orange-400/5 px-2 py-1 rounded-lg border border-orange-400/10">
                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                            Ends: <?= date('M j', strtotime($a['expires_at'])) ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($isFaculty): ?>
                    <div class="flex items-center gap-2">
                        <form action="/announcements/pin" method="POST" class="inline">
                            <input type="hidden" name="ann_id" value="<?= $a['id'] ?>">
                            <button type="submit" class="p-2 rounded-xl bg-white/5 border border-white/10 text-slate-400 hover:text-accent-cyan hover:border-accent-cyan/30 transition-all" title="<?= $a['is_pinned'] ? 'Unpin' : 'Pin' ?>">
                                <i data-lucide="pin" class="w-4 h-4 <?= $a['is_pinned'] ? 'fill-accent-cyan text-accent-cyan' : '' ?>"></i>
                            </button>
                        </form>
                        <form action="/announcements/delete" method="POST" class="inline" onsubmit="return confirm('Delete this announcement?')">
                            <input type="hidden" name="ann_id" value="<?= $a['id'] ?>">
                            <button type="submit" class="p-2 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500 hover:text-white transition-all" title="Delete">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Post Announcement Modal -->
<?php if ($isFaculty): ?>
<div id="postModal" class="fixed inset-0 z-[1000] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeModal('postModal')"></div>
    <div class="glass-card w-full max-w-lg relative z-10 p-0 overflow-hidden border-t-2 border-t-accent-cyan transform scale-95 opacity-0 transition-all duration-300 modal-content">
        <div class="p-6 border-b border-white/10 bg-white/[0.02] flex justify-between items-center">
            <h3 class="font-syne text-xl font-bold text-white flex items-center gap-3">
                <i data-lucide="send" class="w-5 h-5 text-accent-cyan"></i>
                New Announcement
            </h3>
            <button onclick="closeModal('postModal')" class="text-slate-500 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <form action="/announcements/post" method="POST" class="p-6 space-y-5">
            <div class="space-y-2">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Announcement Title *</label>
                <input type="text" name="title" required placeholder="Important update for upcoming exams..." class="form-input">
            </div>
            
            <div class="space-y-2">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Detailed Content *</label>
                <textarea name="content" rows="6" required placeholder="Enter the full announcement details here..." class="form-input resize-none"></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Notice Category</label>
                    <select name="type" class="form-input appearance-none bg-white/5">
                        <option value="general" class="text-dark-bg">📢 General Notice</option>
                        <option value="exam" class="text-dark-bg">📝 Exam Alert</option>
                        <option value="assignment" class="text-dark-bg">📋 Assignment Task</option>
                        <option value="event" class="text-dark-bg">🎉 Campus Event</option>
                        <option value="urgent" class="text-dark-bg">🚨 Urgent Action Required</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Target Semester</label>
                    <select name="target_semester" class="form-input appearance-none bg-white/5">
                        <option value="0" class="text-dark-bg">🎓 All Students (Default)</option>
                        <?php for($i=1;$i<=8;$i++): ?>
                        <option value="<?= $i ?>" class="text-dark-bg">🎓 Semester <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-1">Expiration Date</label>
                    <input type="date" name="expires_at" class="form-input [color-scheme:dark]">
                </div>
                <div class="flex items-center gap-3 pt-6 px-2">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_pinned" value="1" class="sr-only peer">
                        <div class="w-10 h-5 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-accent-cyan"></div>
                        <span class="ml-3 text-xs font-bold text-slate-400">Pin Announcement</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-3 pt-6 border-t border-white/5">
                <button type="button" onclick="closeModal('postModal')" class="flex-1 py-3 bg-white/5 hover:bg-white/10 text-white text-xs font-bold uppercase tracking-widest rounded-xl transition-colors border border-white/10">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-accent-cyan to-accent-purple text-dark-bg text-xs font-bold uppercase tracking-widest rounded-xl shadow-lg shadow-accent-cyan/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                    Post Notice
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        const backdrop = modal.querySelector('.absolute');
        const content = modal.querySelector('.modal-content');
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        const content = modal.querySelector('.modal-content');
        
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 300);
    }

    function filterAnn(type, btn) {
        // Update tab buttons
        document.querySelectorAll('.tab-btn').forEach(t => {
            t.classList.remove('bg-accent-cyan', 'text-dark-bg', 'border-accent-cyan', 'shadow-[0_0_15px_rgba(34,211,238,0.3)]');
            t.classList.add('border-white/10', 'text-slate-400');
            t.classList.remove('text-white');
        });
        
        btn.classList.remove('border-white/10', 'text-slate-400');
        btn.classList.add('bg-accent-cyan', 'text-dark-bg', 'border-accent-cyan', 'shadow-[0_0_15px_rgba(34,211,238,0.3)]');

        // Filter items
        const items = document.querySelectorAll('.ann-item');
        items.forEach(item => {
            if (type === 'all' || item.dataset.type === type) {
                item.style.display = 'block';
                item.classList.add('animate-in', 'fade-in', 'zoom-in-95', 'duration-300');
            } else {
                item.style.display = 'none';
            }
        });
    }

    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
