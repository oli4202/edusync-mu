<?php
$currentPage = 'attendance';
?>

<div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
    <h2>Attendance</h2>
    <div class="badge badge-excused">Batch <?php echo htmlspecialchars($user['batch'] ?? 'N/A'); ?></div>
</div>

<?php
$totalClasses = count($myAttendance);
$presentCount = count(array_filter($myAttendance, static fn($record) => ($record['status'] ?? '') === 'present'));
$presentRate = $totalClasses > 0 ? round(($presentCount / $totalClasses) * 100, 1) : 0;
?>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:20px;">
    <div class="attendance-stat-card">
        <div class="attendance-stat-value"><?php echo $totalClasses; ?></div>
        <div class="attendance-stat-label">Your Total Classes</div>
    </div>
    <div class="attendance-stat-card">
        <div class="attendance-stat-value"><?php echo $presentCount; ?></div>
        <div class="attendance-stat-label">Your Presence</div>
    </div>
    <div class="attendance-stat-card">
        <div class="attendance-stat-value"><?php echo $presentRate; ?>%</div>
        <div class="attendance-stat-label">Your Rate</div>
    </div>
    <div class="attendance-stat-card">
        <div class="attendance-stat-value" style="color:#818cf8;">
            <?php 
            $bTotal = (int)($batchStats['total_records'] ?? 0);
            $bPresent = (int)($batchStats['present_count'] ?? 0);
            echo $bTotal > 0 ? round(($bPresent / $bTotal) * 100, 1) : 0;
            ?>%
        </div>
        <div class="attendance-stat-label">Batch Average</div>
    </div>
</div>

<div class="tabs-container">
    <div class="tabs-header">
        <button class="tab-btn active" onclick="openTab(event, 'myAtt')">My Records</button>
        <button class="tab-btn" onclick="openTab(event, 'batchAtt')">Batch Records</button>
    </div>

    <div id="myAtt" class="tab-content active">
        <div class="attendance-table">
            <?php if (!empty($myAttendance)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Course</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myAttendance as $record): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($record['class_date'])); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($record['course_code'] ?? ''); ?></strong>
                                    <div style="font-size:12px;color:#64748b;margin-top:4px;"><?php echo htmlspecialchars($record['course_name'] ?? ''); ?></div>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo htmlspecialchars($record['status']); ?>">
                                        <?php echo ucfirst($record['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($record['notes'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty-state">No attendance records yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div id="batchAtt" class="tab-content">
        <div class="attendance-table">
            <?php if (!empty($batchAttendance)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($batchAttendance as $record): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($record['class_date'])); ?></td>
                                <td>
                                    <img class="student-avatar" src="<?php echo htmlspecialchars(avatarUrl($record['student_avatar'] ?? '', $record['student_name'] ?? 'Student')); ?>" alt="<?php echo htmlspecialchars($record['student_name'] ?? 'Student'); ?>">
                                    <strong><?php echo htmlspecialchars($record['student_name'] ?? ''); ?></strong>
                                    <div style="font-size:11px;color:#64748b;"><?php echo htmlspecialchars($record['sid'] ?? ''); ?></div>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($record['course_code'] ?? ''); ?></strong>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo htmlspecialchars($record['status']); ?>">
                                        <?php echo ucfirst($record['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty-state">No batch attendance records yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function openTab(evt, tabName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
        tabcontent[i].classList.remove("active");
    }
    tablinks = document.getElementsByClassName("tab-btn");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].classList.remove("active");
    }
    document.getElementById(tabName).style.display = "block";
    document.getElementById(tabName).classList.add("active");
    evt.currentTarget.classList.add("active");
}
</script>

<style>
.tabs-container { margin-top: 24px; }
.tabs-header { display: flex; gap: 8px; margin-bottom: 16px; border-bottom: 1px solid #1e2d45; padding-bottom: 1px; }
.tab-btn { 
    background: transparent; border: none; color: #64748b; padding: 10px 20px; cursor: pointer; 
    font-weight: 600; font-size: 14px; position: relative; transition: all 0.2s;
}
.tab-btn:hover { color: #e2e8f0; }
.tab-btn.active { color: #22d3ee; }
.tab-btn.active::after { 
    content: ''; position: absolute; bottom: -1px; left: 0; width: 100%; height: 2px; background: #22d3ee; 
}
.tab-content { display: none; }
.tab-content.active { display: block; }

.attendance-table {
    background: #111827;
    border: 1px solid #1e2d45;
    border-radius: 8px;
    overflow: hidden;
}

.attendance-stat-card {
    background: #111827;
    border: 1px solid #1e2d45;
    border-radius: 12px;
    padding: 18px;
}

.attendance-stat-value {
    font-size: 28px;
    font-weight: 700;
    color: #22d3ee;
}

.attendance-stat-label {
    color: #64748b;
    font-size: 12px;
    margin-top: 6px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: #0f172a;
}

th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #64748b;
}

td {
    padding: 15px;
    border-top: 1px solid #1e2d45;
    color: #e2e8f0;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.badge-present {
    background: rgba(52, 211, 153, 0.2);
    color: #34d399;
}

.badge-absent {
    background: rgba(244, 63, 94, 0.2);
    color: #f87171;
}

.badge-late {
    background: rgba(251, 191, 36, 0.2);
    color: #fbbf24;
}

.badge-excused {
    background: rgba(34, 211, 238, 0.2);
    color: #22d3ee;
}

.empty-state { padding: 40px; text-align: center; color: #64748b; font-style: italic; }
.student-avatar { width:28px; height:28px; border-radius:999px; object-fit:cover; border:1px solid #1e2d45; margin-right:8px; vertical-align:middle; }
</style>
