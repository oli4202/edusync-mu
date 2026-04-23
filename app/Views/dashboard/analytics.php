<?php
$currentPage = 'analytics';
$dailyLabels = array_map(static fn ($row) => date('M j', strtotime($row['date'])), $studyLogs ?? []);
$dailyValues = array_map(static fn ($row) => (float) ($row['total'] ?? 0), $studyLogs ?? []);
$gradeLabels = array_map(static fn ($row) => 'Subject #' . ($row['subject_id'] ?? '?'), $grades ?? []);
$gradeValues = array_map(static fn ($row) => round((float) ($row['avg_score'] ?? 0), 1), $grades ?? []);
$totalStudyHours = array_sum($dailyValues);
$activeStudyDays = count(array_filter($dailyValues, static fn ($hours) => $hours > 0));
$averageScore = !empty($gradeValues) ? round(array_sum($gradeValues) / count($gradeValues), 1) : 0;
?>

<div class="analytics-page">
    <div class="analytics-header">
        <div>
            <h2>Analytics</h2>
            <p class="analytics-subtitle">Track study consistency and subject performance from the new MVC dashboard.</p>
        </div>
    </div>

    <div class="analytics-stats">
        <div class="analytics-stat-card">
            <span class="analytics-stat-label">Study Hours (30 days)</span>
            <strong><?php echo number_format($totalStudyHours, 1); ?>h</strong>
        </div>
        <div class="analytics-stat-card">
            <span class="analytics-stat-label">Active Study Days</span>
            <strong><?php echo $activeStudyDays; ?></strong>
        </div>
        <div class="analytics-stat-card">
            <span class="analytics-stat-label">Subjects With Grades</span>
            <strong><?php echo count($gradeValues); ?></strong>
        </div>
        <div class="analytics-stat-card">
            <span class="analytics-stat-label">Average Score</span>
            <strong><?php echo number_format($averageScore, 1); ?>%</strong>
        </div>
    </div>

    <div class="analytics-grid">
        <section class="analytics-card">
            <h3>Study Hours by Day</h3>
            <?php if (!empty($dailyValues)): ?>
                <div class="chart-wrap">
                    <canvas id="studyHoursChart"></canvas>
                </div>
            <?php else: ?>
                <p class="analytics-empty">No study logs found in the last 30 days.</p>
            <?php endif; ?>
        </section>

        <section class="analytics-card">
            <h3>Average Score by Subject</h3>
            <?php if (!empty($gradeValues)): ?>
                <div class="chart-wrap">
                    <canvas id="gradeTrendChart"></canvas>
                </div>
            <?php else: ?>
                <p class="analytics-empty">No graded records are available yet.</p>
            <?php endif; ?>
        </section>
    </div>
</div>

<style>
.analytics-page {
    display: grid;
    gap: 24px;
}

.analytics-header h2 {
    margin: 0 0 8px;
    color: #e2e8f0;
}

.analytics-subtitle {
    margin: 0;
    color: #94a3b8;
}

.analytics-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
}

.analytics-stat-card,
.analytics-card {
    background: #111827;
    border: 1px solid #1e2d45;
    border-radius: 12px;
    padding: 20px;
}

.analytics-stat-card strong {
    display: block;
    margin-top: 8px;
    font-size: 28px;
    color: #22d3ee;
}

.analytics-stat-label {
    color: #94a3b8;
    font-size: 13px;
}

.analytics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 20px;
}

.analytics-card h3 {
    margin-top: 0;
    color: #e2e8f0;
}

.chart-wrap {
    position: relative;
    height: 320px;
}

.analytics-empty {
    margin: 0;
    color: #94a3b8;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const studyLabels = <?php echo json_encode($dailyLabels); ?>;
const studyValues = <?php echo json_encode($dailyValues); ?>;
const gradeLabels = <?php echo json_encode($gradeLabels); ?>;
const gradeValues = <?php echo json_encode($gradeValues); ?>;

if (studyValues.length) {
    new Chart(document.getElementById('studyHoursChart'), {
        type: 'line',
        data: {
            labels: studyLabels,
            datasets: [{
                label: 'Hours',
                data: studyValues,
                borderColor: '#22d3ee',
                backgroundColor: 'rgba(34, 211, 238, 0.15)',
                fill: true,
                tension: 0.35,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(148, 163, 184, 0.08)' } },
                y: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(148, 163, 184, 0.08)' }, beginAtZero: true }
            }
        }
    });
}

if (gradeValues.length) {
    new Chart(document.getElementById('gradeTrendChart'), {
        type: 'bar',
        data: {
            labels: gradeLabels,
            datasets: [{
                label: 'Average Score',
                data: gradeValues,
                backgroundColor: 'rgba(52, 211, 153, 0.55)',
                borderColor: '#34d399',
                borderWidth: 1.5,
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: '#94a3b8' }, grid: { display: false } },
                y: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(148, 163, 184, 0.08)' }, beginAtZero: true, max: 100 }
            }
        }
    });
}
</script>
