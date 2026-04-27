<?php
/**
 * scratch/seed_attendance.php
 * REFINED: Generates random attendance for all students, ensuring they have data
 * even if their batch/semester doesn't strictly match the course catalog.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers.php';

use App\Models\User;
use App\Models\Course;
use App\Models\Attendance;

User::ensureRosterSynced();
$db = App\getDB();

echo "Starting REFINED attendance seeding...\n";

// Get all student memberships
$memberships = $db->query("
    SELECT m.user_id, m.batch, m.semester, u.name 
    FROM student_batch_memberships m
    JOIN users u ON u.id = m.user_id
    WHERE u.role = 'student'
")->fetchAll();

echo "Found " . count($memberships) . " student-batch memberships.\n";

$statuses = ['present', 'present', 'present', 'present', 'absent', 'late', 'present'];
$count = 0;
$days = 30;
$today = time();

foreach ($memberships as $m) {
    $userId = (int)$m['user_id'];
    $batch = $m['batch'];
    $semester = (int)$m['semester'];
    $name = $m['name'];

    // 1. Try strict match (Batch + Semester)
    $courses = Course::findByBatchAndSemester($batch, $semester);
    
    // 2. If no strict match, try any course for that Semester
    if (empty($courses)) {
        $stmt = $db->prepare("SELECT * FROM courses WHERE semester = ? LIMIT 5");
        $stmt->execute([$semester]);
        $courses = $stmt->fetchAll();
    }

    // 3. If STILL no match, try courses with NO batch assigned for that semester
    if (empty($courses)) {
        $stmt = $db->prepare("SELECT * FROM courses WHERE (batch IS NULL OR batch = '') AND semester = 1 LIMIT 5");
        $stmt->execute([]);
        $courses = $stmt->fetchAll();
    }

    foreach ($courses as $course) {
        $courseId = (int)$course['id'];
        for ($i = 1; $i <= $days; $i++) {
            $timestamp = $today - ($i * 86400);
            $dayOfWeek = date('N', $timestamp);
            if ($dayOfWeek <= 5) {
                $date = date('Y-m-d', $timestamp);
                $status = $statuses[array_rand($statuses)];
                Attendance::record($userId, $courseId, $date, $status, 'System Randomized');
                $count++;
            }
        }
    }
}

echo "Seeding complete! Total records: $count\n";
