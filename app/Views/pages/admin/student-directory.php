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
        <div style="margin-top: 30px; display: flex; gap: 12px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px;">
            <a href="/admin/attendance?batch=<?= urlencode($student['batch']) ?>&semester=<?= (int)$student['semester'] ?>" class="btn btn-primary btn-sm">Manage Attendance</a>
            <a href="/admin/attendance/details?batch=<?= urlencode($student['batch']) ?>&semester=<?= (int)$student['semester'] ?>&course_id=1" class="btn btn-outline btn-sm">📊 View Full Assessment Grid</a>
        </div>
    </div>
    <?php if (empty($studentId) && !empty($allStudents)): ?>
    <div class="lookup-card">
        <div class="lookup-title">All Software Engineering Students (<?= count($allStudents) ?>)</div>
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse: collapse; min-width: 600px;">
                <thead>
                    <tr style="text-align:left; border-bottom:1px solid rgba(255,255,255,0.05);">
                        <th style="padding:12px; font-size:10px; text-transform:uppercase; color:var(--muted);">Student Name</th>
                        <th style="padding:12px; font-size:10px; text-transform:uppercase; color:var(--muted);">Student ID</th>
                        <th style="padding:12px; font-size:10px; text-transform:uppercase; color:var(--muted);">Batch</th>
                        <th style="padding:12px; font-size:10px; text-transform:uppercase; color:var(--muted);">Sem</th>
                        <th style="padding:12px; font-size:10px; text-transform:uppercase; color:var(--muted);">Presence</th>
                        <th style="padding:12px; font-size:10px; text-transform:uppercase; color:var(--muted);">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php foreach ($allStudents as $s): ?>
                    <tr class="hover:bg-white/[0.02] transition-colors">
                        <td style="padding:12px;">
                            <div class="text-sm font-bold text-white"><?= htmlspecialchars($s['name']) ?></div>
                        </td>
                        <td style="padding:12px;">
                            <div class="text-xs text-slate-400 font-mono"><?= htmlspecialchars($s['student_id']) ?></div>
                        </td>
                        <td style="padding:12px;">
                            <div class="text-xs text-slate-300"><?= htmlspecialchars($s['batch']) ?></div>
                        </td>
                        <td style="padding:12px;">
                            <div class="text-xs text-slate-300"><?= (int)$s['semester'] ?></div>
                        </td>
                        <td style="padding:12px;">
                            <div class="flex flex-col gap-1">
                                <div class="text-xs font-bold <?= ($s['attendance_rate'] ?? 0) >= 75 ? 'text-emerald-400' : 'text-orange-400' ?>">
                                    <?= (float)($s['attendance_rate'] ?? 0) ?>%
                                </div>
                                <div class="text-[9px] text-slate-500 uppercase tracking-tighter italic">
                                    <?= (int)($s['total_classes'] ?? 0) ?> classes
                                </div>
                            </div>
                        </td>
                        <td style="padding:12px;">
                            <a href="/admin/students?student_id=<?= urlencode($s['student_id']) ?>" class="btn btn-outline btn-xs" style="font-size:9px; padding:4px 8px;">View Full Profile</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
