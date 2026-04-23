<?php $currentPage = 'groups'; ?>

<div x-data="{ createModalOpen: false, selectedSubject: '', groupSuggestions: [] }" 
     x-init="$watch('selectedSubject', value => refreshSuggestions(value))"
     class="space-y-10">
    
    <!-- Top Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="font-syne text-3xl font-bold text-white tracking-tight">Study Groups</h1>
            <p class="text-slate-500 text-sm mt-1 font-medium">Collaborate with classmates and study together.</p>
        </div>
        <button @click="createModalOpen = true" class="px-6 py-3 bg-gradient-to-r from-accent-cyan to-accent-purple text-dark-bg font-bold rounded-2xl shadow-lg shadow-accent-cyan/20 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 flex items-center gap-2">
            <i data-lucide="plus-circle" class="w-5 h-5"></i>
            Create Group
        </button>
    </div>

    <!-- My Groups -->
    <section class="space-y-6">
        <div class="flex items-center gap-3">
            <h2 class="font-syne text-xl font-bold text-white flex items-center gap-2">
                <i data-lucide="folder-heart" class="w-5 h-5 text-accent-cyan"></i>
                My Groups
            </h2>
            <span class="px-2 py-0.5 rounded-lg bg-white/5 border border-white/10 text-[10px] font-bold text-slate-400"><?= count($myGroupList) ?></span>
        </div>

        <?php if (empty($myGroupList)): ?>
            <div class="glass-card p-12 text-center">
                <div class="w-20 h-20 rounded-full bg-accent-cyan/5 flex items-center justify-center mx-auto mb-6 text-accent-cyan">
                    <i data-lucide="users" class="w-10 h-10"></i>
                </div>
                <h3 class="text-white font-bold text-lg">No groups yet</h3>
                <p class="text-slate-500 text-sm max-w-xs mx-auto mt-2 leading-relaxed">
                    Study groups are a great way to learn together. Join an existing group or create your own!
                </p>
                <button @click="createModalOpen = true" class="mt-6 text-xs font-bold text-accent-cyan hover:text-white transition-colors uppercase tracking-widest">Create My First Group</button>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($myGroupList as $g): ?>
                    <div class="glass-card p-6 flex flex-col h-full hover:border-white/20 transition-all duration-300 group">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-white/5 flex items-center justify-center text-accent-cyan group-hover:bg-accent-cyan/10 transition-colors">
                                <i data-lucide="users-2" class="w-6 h-6"></i>
                            </div>
                            <?php if ($g['my_role'] === 'admin'): ?>
                                <span class="px-2 py-1 rounded-lg bg-accent-cyan/10 border border-accent-cyan/20 text-[10px] font-bold text-accent-cyan tracking-tighter">ADMIN</span>
                            <?php endif; ?>
                        </div>

                        <h4 class="text-lg font-bold text-white group-hover:text-accent-cyan transition-colors line-clamp-1"><?= htmlspecialchars($g['name']) ?></h4>
                        <p class="text-sm text-slate-500 mt-2 line-clamp-2 leading-relaxed flex-1 italic">
                            <?= htmlspecialchars($g['description'] ?: 'No description provided.') ?>
                        </p>

                        <div class="mt-6 pt-4 border-t border-white/5 space-y-3">
                            <div class="flex items-center justify-between text-xs font-medium">
                                <span class="text-slate-500 flex items-center gap-1.5"><i data-lucide="user-check" class="w-3.5 h-3.5"></i> <?= $g['member_count'] ?>/<?= $g['max_members'] ?> Members</span>
                                <span class="text-accent-purple"><?= htmlspecialchars($g['subject_name'] ?: 'General') ?></span>
                            </div>
                            <div class="text-[10px] text-slate-600 line-clamp-1 uppercase tracking-tighter italic">
                                Members: <?= htmlspecialchars($g['member_names']) ?>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center gap-3">
                            <form action="/groups/leave" method="POST" class="w-full" onsubmit="return confirm('Leave this group?')">
                                <input type="hidden" name="group_id" value="<?= $g['id'] ?>">
                                <button class="w-full py-2.5 rounded-xl border border-white/5 bg-white/5 hover:bg-red-500/10 hover:border-red-500/20 hover:text-red-400 text-xs font-bold transition-all duration-200">
                                    Leave Group
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Discover Groups -->
    <?php if (!empty($discoverList)): ?>
        <section class="space-y-6">
            <h2 class="font-syne text-xl font-bold text-white flex items-center gap-2">
                <i data-lucide="compass" class="w-5 h-5 text-accent-purple"></i>
                Discover Groups
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($discoverList as $g): ?>
                    <div class="glass-card p-6 flex flex-col h-full hover:border-white/20 transition-all duration-300 group">
                        <h4 class="text-lg font-bold text-white group-hover:text-accent-purple transition-colors line-clamp-1"><?= htmlspecialchars($g['name']) ?></h4>
                        <p class="text-sm text-slate-500 mt-2 line-clamp-2 leading-relaxed flex-1">
                            <?= htmlspecialchars($g['description'] ?: 'No description provided.') ?>
                        </p>

                        <div class="mt-6 pt-4 border-t border-white/5 flex items-center justify-between text-xs font-medium">
                            <span class="text-slate-500 flex items-center gap-1.5"><i data-lucide="users" class="w-3.5 h-3.5"></i> <?= $g['member_count'] ?>/<?= $g['max_members'] ?></span>
                            <span class="text-accent-cyan"><?= htmlspecialchars($g['subject_name'] ?: 'General') ?></span>
                        </div>

                        <form action="/groups/join" method="POST" class="mt-6">
                            <input type="hidden" name="group_id" value="<?= $g['id'] ?>">
                            <button class="w-full py-2.5 rounded-xl bg-accent-purple/10 border border-accent-purple/20 text-accent-purple hover:bg-accent-purple/20 text-xs font-bold transition-all duration-200">
                                Join Group
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Create Modal (Alpine.js) -->
    <div 
        x-show="createModalOpen" 
        class="fixed inset-0 z-[60] flex items-center justify-center p-6 bg-[#0a0e1a]/80 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @keydown.escape.window="createModalOpen = false"
        x-cloak
    >
        <div 
            class="glass-card w-full max-w-lg p-8 space-y-6 animate-in zoom-in-95 duration-300"
            @click.away="createModalOpen = false"
        >
            <div class="flex items-center justify-between">
                <h3 class="font-syne text-xl font-bold text-white">Create Study Group</h3>
                <button @click="createModalOpen = false" class="text-slate-500 hover:text-white">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form action="/groups/create" method="POST" class="space-y-6">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">Group Name</label>
                    <input 
                        type="text" 
                        name="name" 
                        required 
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-accent-cyan focus:ring-1 focus:ring-accent-cyan transition-all"
                        placeholder="e.g. DSA Revision Squad"
                    >
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">Subject</label>
                        <select 
                            name="subject_id" 
                            x-model="selectedSubject"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-accent-cyan transition-all"
                        >
                            <option value="" class="bg-dark-card">General</option>
                            <?php foreach ($subjects as $s): ?>
                                <option value="<?= $s['id'] ?>" class="bg-dark-card"><?= htmlspecialchars(($s['code'] ? $s['code'] . ': ' : '') . $s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">Max Members</label>
                        <input 
                            type="number" 
                            name="max_members" 
                            value="20" 
                            min="2" 
                            max="100" 
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-accent-cyan transition-all"
                        >
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">Description</label>
                    <textarea 
                        name="description" 
                        rows="3" 
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-accent-cyan transition-all"
                        placeholder="What are the goals of this group?"
                    ></textarea>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">Invite Classmates</label>
                    <select 
                        name="member_ids[]" 
                        multiple 
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-accent-cyan transition-all min-h-[120px]"
                    >
                        <?php foreach ($classmateList as $mate): ?>
                            <option value="<?= $mate['id'] ?>" class="bg-dark-card py-1 px-2 mb-1 rounded hover:bg-accent-cyan/10 transition-colors">
                                <?= htmlspecialchars($mate['name']) ?> (Sem <?= $mate['semester'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-[10px] text-slate-600 font-medium tracking-tight italic">Hold Ctrl/Cmd to select multiple members.</p>
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button type="button" @click="createModalOpen = false" class="flex-1 py-3 border border-white/10 rounded-2xl text-sm font-bold text-slate-400 hover:bg-white/5 transition-all uppercase tracking-widest">Cancel</button>
                    <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-accent-cyan to-accent-purple text-dark-bg font-bold rounded-2xl shadow-lg shadow-accent-cyan/20 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 uppercase tracking-widest">Create Group</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function refreshSuggestions(subjectId) {
        // Logic for suggestions could go here or remain in JS
        lucide.createIcons();
    }
</script>
