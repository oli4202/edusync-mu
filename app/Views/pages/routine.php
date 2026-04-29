<?php 
$currentPage = 'routine'; 
?>

<style>
.routine-container {
    max-width: 1000px;
    margin: 0 auto;
    font-family: 'Inter', sans-serif;
    color: var(--text);
}

.routine-header {
    background: #fff;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.routine-title h1 {
    font-size: 24px;
    font-weight: 800;
    color: #1a2a6c;
    margin: 0 0 4px 0;
}

.routine-title p {
    font-size: 13px;
    color: var(--muted);
    margin: 0;
}

.batch-selector {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.batch-pill {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    background: #f1f5f9;
    color: #475569;
    text-decoration: none;
    transition: all 0.2s;
    border: 1px solid transparent;
}

.batch-pill:hover {
    background: #e2e8f0;
}

.batch-pill.active {
    background: #c8102e;
    color: #fff;
    box-shadow: 0 2px 8px rgba(200,16,46,0.3);
}

.routine-grid {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.day-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    overflow: hidden;
    border-left: 4px solid transparent;
}

.day-card.current-day {
    border-left-color: #c8102e;
}

.day-header {
    padding: 16px 20px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.day-name {
    font-size: 18px;
    font-weight: 800;
    color: #1e293b;
    letter-spacing: 0.5px;
}

.current-badge {
    background: #c8102e;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 4px;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.slots-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1px;
    background: #e2e8f0;
}

.slot {
    background: #fff;
    padding: 16px;
    display: flex;
    flex-direction: column;
    min-height: 120px;
    transition: background 0.2s;
}

.slot:hover {
    background: #f8fafc;
}

.slot-time {
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    margin-bottom: 8px;
}

.course-card {
    background: #f1f5f9;
    border-radius: 8px;
    padding: 10px;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    border-left: 3px solid #cbd5e1;
}

.course-code {
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
    margin-bottom: 6px;
}

.course-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 11px;
    font-weight: 600;
}

.room-badge {
    background: rgba(0,0,0,0.05);
    padding: 2px 6px;
    border-radius: 4px;
    color: #475569;
}

.faculty-badge {
    color: #c8102e;
}

.empty-slot {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 1;
    color: #cbd5e1;
    font-size: 12px;
    font-style: italic;
}

/* Empty state for a whole day */
.day-empty {
    padding: 30px;
    text-align: center;
    color: #94a3b8;
    font-size: 14px;
    background: #fff;
}

@media(max-width: 768px) {
    .slots-container {
        grid-template-columns: 1fr;
    }
    .slot {
        min-height: auto;
    }
}
</style>

<div class="routine-container">
    <div class="routine-header">
        <div class="routine-title">
            <h1>Class Routine</h1>
            <p><?= htmlspecialchars($routineData['department'] ?? 'Department') ?> • <?= htmlspecialchars($routineData['version'] ?? 'Version') ?></p>
        </div>
        <div class="batch-selector">
            <?php foreach ($routineData['batches'] as $batch): ?>
                <a href="?batch=<?= urlencode($batch) ?>" class="batch-pill <?= $selectedBatch === $batch ? 'active' : '' ?>">
                    <?= htmlspecialchars($batch) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (!$selectedBatch): ?>
        <div style="background: #fff; padding: 40px; border-radius: 12px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
            <div style="font-size: 48px; margin-bottom: 16px;">📅</div>
            <h2 style="font-size: 20px; color: #1e293b; margin-bottom: 8px;">Select a Batch</h2>
            <p style="color: #64748b;">Please select your batch from the options above to view the routine.</p>
        </div>
    <?php else: ?>
        <div class="routine-grid">
            <?php foreach ($routineData['days'] as $day): ?>
                <?php 
                $isCurrentDay = ($day === $currentDay);
                $daySchedule = $routineData['schedule'][$day][$selectedBatch] ?? [];
                
                // Map the day schedule by slot index for easy access
                $slotsMap = [];
                foreach ($daySchedule as $item) {
                    $slotsMap[$item[0]] = [
                        'course' => $item[1],
                        'room' => $item[2],
                        'faculty' => $item[3]
                    ];
                }
                ?>
                <div class="day-card <?= $isCurrentDay ? 'current-day' : '' ?>">
                    <div class="day-header">
                        <div class="day-name"><?= htmlspecialchars($day) ?></div>
                        <?php if ($isCurrentDay): ?>
                            <div class="current-badge">Today</div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (empty($daySchedule)): ?>
                        <div class="day-empty">No classes scheduled for today. Take a break! ☕</div>
                    <?php else: ?>
                        <div class="slots-container">
                            <?php foreach ($routineData['time_slots'] as $index => $timeSlot): ?>
                                <?php 
                                // Only show slot if it has a class, or maybe we want to show all slots?
                                // Usually showing all slots looks better in a grid.
                                ?>
                                <div class="slot">
                                    <div class="slot-time"><?= htmlspecialchars($timeSlot['start']) ?> - <?= htmlspecialchars($timeSlot['end']) ?></div>
                                    
                                    <?php if (isset($slotsMap[$index])): ?>
                                        <?php 
                                        $class = $slotsMap[$index];
                                        // Simple color matching
                                        $bgColor = '#f1f5f9';
                                        $borderColor = '#cbd5e1';
                                        foreach ($routineData['colors'] as $prefix => $color) {
                                            if ($prefix !== 'default' && str_starts_with($class['course'], $prefix)) {
                                                $bgColor = $color;
                                                // Darken border color slightly
                                                $borderColor = 'rgba(0,0,0,0.1)';
                                                break;
                                            }
                                        }
                                        ?>
                                        <div class="course-card" style="background: <?= $bgColor ?>; border-left-color: <?= $borderColor ?>;">
                                            <div class="course-code"><?= htmlspecialchars($class['course']) ?></div>
                                            <div class="course-meta">
                                                <span class="room-badge">📍 <?= htmlspecialchars($class['room'] ?: 'TBA') ?></span>
                                                <span class="faculty-badge"><?= htmlspecialchars($class['faculty']) ?></span>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-slot">-</div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
