<?php

namespace App\Support;

/**
 * Attendance Helper Logic
 */
class AttendanceHelper
{
    private static function normalizeBatchForDb(string $batch): string
    {
        $normalized = preg_replace('/[^0-9]/', '', $batch);
        return trim((string)$normalized);
    }

    private static function extractCanonicalCode(string $raw): string
    {
        $raw = strtoupper(trim($raw));
        if ($raw === '') {
            return '';
        }

        if (preg_match('/([A-Z]{2,4})\s*[- ]?\s*(\d{3})/', $raw, $matches)) {
            return $matches[1] . ' ' . $matches[2];
        }

        return preg_replace('/\s+/', ' ', str_replace('-', ' ', $raw));
    }

    private static function normalizeCodeForDb(string $code): string
    {
        return strtoupper(str_replace(' ', '', str_replace('-', '', trim($code))));
    }

    /**
     * Calculate how many times a course was scheduled between two dates according to the routine.
     */
    public static function calculateExpectedClasses(string $batch, string $courseCode, string $startDate, string $endDate): int
    {
        $routineData = require __DIR__ . '/../Data/routine_data.php';
        $targetCode = self::extractCanonicalCode($courseCode);
        if ($targetCode === '') {
            return 0;
        }
        
        // Find which days of the week this course is scheduled for this batch
        $scheduledDays = [];
        $scheduledSlots = 0;
        foreach ($routineData['schedule'] as $day => $batches) {
            if (isset($batches[$batch])) {
                foreach ($batches[$batch] as $slot) {
                    $slotCode = self::extractCanonicalCode((string)($slot[1] ?? ''));
                    if ($slotCode === $targetCode) {
                        $scheduledDays[] = self::mapDayToPhp($day);
                        $scheduledSlots++;
                    }
                }
            }
        }
        
        $scheduledDays = array_unique($scheduledDays);
        if (empty($scheduledDays)) return 0;
        
        $count = 0;
        $current = strtotime($startDate);
        $last = strtotime($endDate);
        
        while ($current <= $last) {
            $dayOfWeek = date('N', $current); // 1 (Mon) to 7 (Sun)
            if (in_array($dayOfWeek, $scheduledDays)) {
                $count++;
            }
            $current = strtotime('+1 day', $current);
        }
        
        // If a course appears multiple slots per day in routine, scale accordingly.
        $slotsPerScheduledDay = count($scheduledDays) > 0 ? max(1, (int)round($scheduledSlots / count($scheduledDays))) : 1;
        return $count * $slotsPerScheduledDay;
    }

    /**
     * Map routine day codes to PHP day numbers (1-7)
     */
    private static function mapDayToPhp(string $day): int
    {
        return match ($day) {
            'MON'  => 1,
            'TUES' => 2,
            'WED'  => 3,
            'THU'  => 4,
            'FRI'  => 5,
            'SAT'  => 6,
            'SUN'  => 7,
            default => 0
        };
    }

    /**
     * Get all courses for a batch from the routine
     */
    public static function getBatchCoursesFromRoutine(string $batch): array
    {
        $routineData = require __DIR__ . '/../Data/routine_data.php';
        $courses = [];
        
        foreach ($routineData['schedule'] as $day => $batches) {
            if (isset($batches[$batch])) {
                foreach ($batches[$batch] as $slot) {
                    $rawCode = (string)($slot[1] ?? '');
                    $code = self::extractCanonicalCode($rawCode);
                    $faculty = $slot[3];
                    
                    if ($code !== '' && !isset($courses[$code])) {
                        $courses[$code] = [
                            'code' => $code,
                            'faculty_short' => $faculty,
                        ];
                    }
                }
            }
        }
        
        return array_values($courses);
    }
    /**
     * Get a detailed report for a batch including expected classes and teacher info.
     */
    public static function getBatchAttendanceReport(string $batch, string $startDate, string $endDate): array
    {
        $routineCourses = self::getBatchCoursesFromRoutine($batch);
        $facultyRoster = require __DIR__ . '/../Data/faculty_data.php';
        $db = \getDB();
        $dbBatch = self::normalizeBatchForDb($batch);
        
        $report = [];
        
        foreach ($routineCourses as $course) {
            $code = $course['code'];
            $facultyShort = $course['faculty_short'];
            $facultyName = isset($facultyRoster[$facultyShort]) ? $facultyRoster[$facultyShort]['name'] : 'Unknown';
            
            $expected = self::calculateExpectedClasses($batch, $code, $startDate, $endDate);
            
            // Get actual classes conducted (distinct dates for this course and batch)
            $stmt = $db->prepare("
                SELECT COUNT(DISTINCT a.class_date) 
                FROM attendance a
                INNER JOIN courses c ON c.id = a.course_id
                INNER JOIN users u ON u.id = a.user_id
                WHERE u.batch = ? AND (c.code LIKE ? OR c.name LIKE ?) AND a.class_date BETWEEN ? AND ?
            ");
            $stmt->execute([$dbBatch, "%$code%", "%$code%", $startDate, $endDate]);
            $conducted = (int)$stmt->fetchColumn();
            
            $report[] = [
                'course_code' => $code,
                'faculty' => $facultyName,
                'faculty_short' => $facultyShort,
                'expected_classes' => $expected,
                'conducted_classes' => $conducted,
            ];
        }
        
        return $report;
    }

    /**
     * Get individual student report per subject
     */
    public static function getStudentSubjectReport(int $userId, string $batch, string $startDate, string $endDate): array
    {
        $db = \getDB();
        $dbBatch = self::normalizeBatchForDb($batch);
        $routineCourses = self::getBatchCoursesFromRoutine($batch);

        $facultyStmt = $db->prepare("
            SELECT u.name
            FROM teacher_course_assignments tca
            INNER JOIN users u ON u.id = tca.teacher_id
            WHERE tca.course_id = ?
              AND (tca.batch = ? OR tca.batch IS NULL OR tca.batch = '')
            ORDER BY u.name
            LIMIT 1
        ");

        $attendedStmt = $db->prepare("
            SELECT
                c.id AS course_id,
                c.code AS course_code,
                COUNT(*) AS attended
            FROM attendance a
            INNER JOIN courses c ON c.id = a.course_id
            WHERE a.user_id = ?
              AND a.status IN ('present', 'late', 'excused')
              AND a.class_date BETWEEN ? AND ?
            GROUP BY c.id, c.code
        ");
        $attendedStmt->execute([$userId, $startDate, $endDate]);
        $attendanceRows = $attendedStmt->fetchAll();

        $courseMap = [];
        foreach ($attendanceRows as $row) {
            $code = (string)($row['course_code'] ?? '');
            if ($code === '') {
                continue;
            }
            $courseMap[self::normalizeCodeForDb($code)] = [
                'course_id' => (int)$row['course_id'],
                'course_code' => $code,
            ];
        }

        // Include enrolled courses even if attendance not marked yet
        $enrolledStmt = $db->prepare("
            SELECT c.id AS course_id, c.code AS course_code
            FROM student_course_enrollments sce
            INNER JOIN courses c ON c.id = sce.course_id
            WHERE sce.user_id = ?
        ");
        $enrolledStmt->execute([$userId]);
        foreach ($enrolledStmt->fetchAll() as $row) {
            $code = (string)($row['course_code'] ?? '');
            if ($code === '') {
                continue;
            }
            $courseMap[self::normalizeCodeForDb($code)] = [
                'course_id' => (int)$row['course_id'],
                'course_code' => $code,
            ];
        }

        // Also include routine courses if they can be resolved to real courses
        $resolveRoutineStmt = $db->prepare("
            SELECT id, code
            FROM courses
            WHERE UPPER(REPLACE(REPLACE(code, '-', ''), ' ', '')) = ?
            LIMIT 1
        ");
        foreach ($routineCourses as $routineCourse) {
            $routineCode = (string)($routineCourse['code'] ?? '');
            $normalizedRoutine = self::normalizeCodeForDb($routineCode);
            if ($normalizedRoutine === '' || isset($courseMap[$normalizedRoutine])) {
                continue;
            }

            $resolveRoutineStmt->execute([$normalizedRoutine]);
            $resolved = $resolveRoutineStmt->fetch();
            if ($resolved) {
                $courseMap[$normalizedRoutine] = [
                    'course_id' => (int)$resolved['id'],
                    'course_code' => (string)$resolved['code'],
                ];
            }
        }

        $attendedByCourseStmt = $db->prepare("
            SELECT COUNT(*)
            FROM attendance
            WHERE user_id = ?
              AND course_id = ?
              AND status IN ('present', 'late', 'excused')
              AND class_date BETWEEN ? AND ?
        ");
        $conductedByCourseStmt = $db->prepare("
            SELECT COUNT(DISTINCT a.class_date)
            FROM attendance a
            INNER JOIN users u ON u.id = a.user_id
            WHERE u.batch = ?
              AND a.course_id = ?
              AND a.class_date BETWEEN ? AND ?
        ");

        $report = [];
        foreach ($courseMap as $normalizedCode => $courseRef) {
            $courseId = (int)$courseRef['course_id'];
            $code = (string)$courseRef['course_code'];

            $attendedByCourseStmt->execute([$userId, $courseId, $startDate, $endDate]);
            $attended = (int)$attendedByCourseStmt->fetchColumn();

            $conductedByCourseStmt->execute([$dbBatch, $courseId, $startDate, $endDate]);
            $conducted = (int)$conductedByCourseStmt->fetchColumn();

            $expected = self::calculateExpectedClasses($batch, $code, $startDate, $endDate);
            if ($expected <= 0) {
                $expected = $conducted;
            }

            $facultyStmt->execute([$courseId, $dbBatch]);
            $facultyName = (string)($facultyStmt->fetchColumn() ?: 'Not assigned');

            $report[] = [
                'course_code' => $code,
                'faculty' => $facultyName,
                'expected' => $expected,
                'conducted' => $conducted,
                'attended' => $attended,
                'rate' => $conducted > 0 ? round(($attended / $conducted) * 100, 1) : 0.0,
            ];
        }

        usort($report, static fn (array $a, array $b): int => strcmp((string)$a['course_code'], (string)$b['course_code']));
        return $report;
    }
}
