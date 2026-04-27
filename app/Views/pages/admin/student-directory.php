<?php $pageTitle = 'Student Lookup - EduSync MU'; ?>

<style>
.lookup-shell{display:grid;gap:20px;}
.lookup-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:24px;}
.lookup-title{font-family:'Syne',sans-serif;font-size:18px;font-weight:700;margin-bottom:16px;}
.lookup-form{display:flex;gap:12px;flex-wrap:wrap;align-items:end;}
.lookup-form label{display:block;font-size:12px;color:var(--muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:.04em;}
.lookup-form input{min-width:280px;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:10px;padding:12px 14px;color:var(--text);}
.overview-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:20px;}
.overview-stat{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.05);border-radius:12px;padding:16px;}
.overview-stat-value{font-size:24px;font-weight:800;color:var(--accent);}
.overview-stat-label{font-size:12px;color:var(--muted);margin-top:6px;text-transform:uppercase;letter-spacing:.06em;}
.lookup-list{display:grid;gap:10px;}
.lookup-item{padding:14px 16px;border:1px solid rgba(255,255,255,.06);border-radius:12px;background:rgba(255,255,255,.02);}
.lookup-subtle{font-size:12px;color:var(--muted);}
</style>

<div style="margin-bottom:20px;">
    <a href="/admin" class="btn btn-outline btn-sm">Back to Admin Panel</a>
</div>

<div class="lookup-shell">
    <div class="lookup-card">
        <div class="lookup-title">Student ID Lookup</div>
        <form method="GET" class="lookup-form">
            <div>
                <label>Student ID</label>
                <input type="text" name="student_id" value="<?= htmlspecialchars($studentId ?? '') ?>" placeholder="e.g. 252-134-021" required>
            </div>
            <button type="submit" class="btn btn-primary">Show Overview</button>
        </form>
        <p class="lookup-subtle" style="margin-top:12px;">One student ID shows roster memberships, synced subjects, and recent attendance together.</p>
    </div>

    <?php if (!empty($notFound)): ?>
    <div class="lookup-card">
        No student was found for ID <strong><?= htmlspecialchars($studentId) ?></strong>.
    </div>
    <?php endif; ?>

    <?php if (!empty($overview)): ?>
    <?php $student = $overview['user']; ?>
    <?php $summary = $overview['attendance_summary']; ?>
    <div class="lookup-card">
        <div class="lookup-title"><?= htmlspecialchars($student['name']) ?></div>
        <div class="lookup-subtle" style="margin-bottom:18px;">
            <?= htmlspecialchars($student['student_id']) ?> · Batch <?= htmlspecialchars($student['batch'] ?? '-') ?> · Semester <?= (int) ($student['semester'] ?? 0) ?> · <?= htmlspecialchars($student['email']) ?>
        </div>

        <div class="overview-grid">
            <div class="overview-stat">
                <div class="overview-stat-value"><?= count($overview['memberships']) ?></div>
                <div class="overview-stat-label">Roster Memberships</div>
            </div>
            <div class="overview-stat">
                <div class="overview-stat-value"><?= count($overview['subjects']) ?></div>
                <div class="overview-stat-label">Synced Subjects</div>
            </div>
            <div class="overview-stat">
                <div class="overview-stat-value"><?= (int) ($summary['total_classes'] ?? 0) ?></div>
                <div class="overview-stat-label">Total Classes</div>
            </div>
            <div class="overview-stat">
                <div class="overview-stat-value"><?= htmlspecialchars((string) ($summary['present_percentage'] ?? 0)) ?>%</div>
                <div class="overview-stat-label">Present Rate</div>
            </div>
        </div>

        <div class="lookup-title" style="font-size:15px;">Batch Memberships</div>
        <div class="lookup-list" style="margin-bottom:20px;">
            <?php foreach ($overview['memberships'] as $membership): ?>
            <div class="lookup-item">
                <strong>Batch <?= htmlspecialchars($membership['batch']) ?></strong> · Semester <?= (int) $membership['semester'] ?>
                <div class="lookup-subtle" style="margin-top:4px;"><?= htmlspecialchars($membership['label'] ?: 'Official roster') ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="lookup-title" style="font-size:15px;">Synced Subjects</div>
        <div class="lookup-list" style="margin-bottom:20px;">
            <?php if (!empty($overview['subjects'])): ?>
                <?php foreach ($overview['subjects'] as $subject): ?>
                <div class="lookup-item">
                    <strong><?= htmlspecialchars($subject['code'] ?: '-') ?></strong> · <?= htmlspecialchars($subject['name']) ?>
                    <div class="lookup-subtle" style="margin-top:4px;">Semester <?= (int) ($subject['semester'] ?? 0) ?> · Year Slot <?= htmlspecialchars((string) ($subject['year'] ?? '-')) ?></div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="lookup-item">No synced subjects yet.</div>
            <?php endif; ?>
        </div>

        <div class="lookup-title" style="font-size:15px;">Recent Attendance</div>
        <div class="lookup-list">
            <?php if (!empty($overview['recent_attendance'])): ?>
                <?php foreach ($overview['recent_attendance'] as $record): ?>
                <div class="lookup-item">
                    <strong><?= htmlspecialchars($record['code']) ?></strong> · <?= htmlspecialchars($record['course_name']) ?>
                    <div class="lookup-subtle" style="margin-top:4px;"><?= date('d M Y', strtotime($record['class_date'])) ?> · <?= ucfirst(htmlspecialchars($record['status'])) ?><?= !empty($record['notes']) ? ' · ' . htmlspecialchars($record['notes']) : '' ?></div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="lookup-item">No attendance records yet.</div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
