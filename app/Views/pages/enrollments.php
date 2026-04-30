<?php $currentPage = 'enrollments'; ?>

<div class="space-y-8">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-syne text-3xl font-bold text-white tracking-tight">My Course Enrollments</h1>
            <p class="text-slate-500 text-sm mt-1 font-medium">Manage your enrolled courses for this semester.</p>
        </div>
        <a href="/dashboard" class="px-4 py-2 bg-accent-cyan/10 hover:bg-accent-cyan/20 border border-accent-cyan/20 text-accent-cyan rounded-xl text-xs font-bold transition-all">
            ← Back to Dashboard
        </a>
    </div>

    <!-- Enrolled Courses Grid -->
    <?php if (!empty($enrolledCourses)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($enrolledCourses as $course): ?>
                <div class="glass-card p-6 hover:border-white/20 transition-all duration-300 group">
                    <!-- Course Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-white group-hover:text-accent-cyan transition-colors">
                                <?php echo htmlspecialchars($course['code']); ?>
                            </h3>
                            <p class="text-xs text-slate-500 uppercase tracking-wider mt-1 font-semibold">
                                <?php echo htmlspecialchars($course['name']); ?>
                            </p>
                        </div>
                        <span class="px-3 py-1 bg-accent-cyan/10 text-accent-cyan text-xs font-bold rounded-lg">
                            Sem <?php echo (int)$course['semester']; ?>
                        </span>
                    </div>

                    <!-- Course Details -->
                    <div class="space-y-2 mb-4 text-sm text-slate-400">
                        <div class="flex items-center gap-2">
                            <i data-lucide="calendar" class="w-4 h-4 text-accent-purple"></i>
                            <span>Batch <?php echo htmlspecialchars($course['batch'] ?? $course['batch']); ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="book-open" class="w-4 h-4 text-emerald-400"></i>
                            <span>Year <?php echo (int)$course['year']; ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="user-round" class="w-4 h-4 text-sky-400"></i>
                            <span>
                                Faculty:
                                <?php echo htmlspecialchars($course['assigned_faculty'] ?: 'Not assigned yet'); ?>
                            </span>
                        </div>
                        <?php if (isset($course['enrolled_at'])): ?>
                            <div class="flex items-center gap-2">
                                <i data-lucide="clock" class="w-4 h-4 text-orange-400"></i>
                                <span>Enrolled <?php echo date('M d, Y', strtotime($course['enrolled_at'])); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Course Description -->
                    <?php if (isset($course['description']) && $course['description']): ?>
                        <p class="text-xs text-slate-500 mb-4 line-clamp-2">
                            <?php echo htmlspecialchars($course['description']); ?>
                        </p>
                    <?php endif; ?>

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <button 
                            onclick="unenrollCourse(<?php echo (int)$course['id']; ?>, '<?php echo htmlspecialchars($course['code']); ?>')"
                            class="flex-1 px-3 py-2 bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 text-red-400 rounded-lg text-xs font-bold transition-all"
                        >
                            <i data-lucide="x" class="w-3 h-3 inline mr-1"></i>
                            Unenroll
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- Empty State -->
        <div class="glass-card p-16 text-center">
            <div class="w-16 h-16 rounded-full bg-accent-purple/10 flex items-center justify-center mx-auto mb-4 text-accent-purple">
                <i data-lucide="inbox" class="w-8 h-8"></i>
            </div>
            <h3 class="font-bold text-white text-lg mb-2">No Courses Enrolled</h3>
            <p class="text-slate-500 text-sm mb-6">You haven't enrolled in any courses yet. Browse available courses for your batch.</p>
            <a href="/dashboard" class="inline-block px-4 py-2 bg-accent-cyan/10 hover:bg-accent-cyan/20 border border-accent-cyan/20 text-accent-cyan rounded-xl text-xs font-bold transition-all">
                Browse Courses
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
    function unenrollCourse(courseId, courseCode) {
        if (!confirm(`Are you sure you want to unenroll from ${courseCode}?`)) {
            return;
        }

        fetch('/api/unenroll', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `course_id=${courseId}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Show success message and reload
                alert('Unenrolled successfully!');
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to unenroll'));
            }
        })
        .catch(err => {
            alert('Error: ' + err.message);
        });
    }
</script>

<style>
    .glass-card { 
        background: rgba(15, 23, 42, 0.5); 
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 1rem;
    }
</style>
