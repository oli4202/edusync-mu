<?php

namespace App\Support;

/**
 * Attendance Helper Logic
 */
class AttendanceHelper
{
    /**
     * Calculate how many times a course was scheduled between two dates according to the routine.
     */
    public static function calculateExpectedClasses(string $batch, string $courseCode, string $startDate, string $endDate): int
    {
        $routineData = require __DIR__ . '/../Data/routine_data.php';
        
        // Find which days of the week this course is scheduled for this batch
        $scheduledDays = [];
        foreach ($routineData['schedule'] as $day => $batches) {
            if (isset($batches[$batch])) {
                foreach ($batches[$batch] as $slot) {
                    // Check if course code matches (ignoring extra text like 'Lab')
                    if (str_contains($slot[1], $courseCode)) {
                        $scheduledDays[] = self::mapDayToPhp($day);
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
        
        return $count;
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
                    $code = $slot[1];
                    $faculty = $slot[3];
                    
                    if (!isset($courses[$code])) {
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
            $stmt->execute([$batch, "%$code%", "%$code%", $startDate, $endDate]);
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
        $routineCourses = self::getBatchCoursesFromRoutine($batch);
        $facultyRoster = require __DIR__ . '/../Data/faculty_data.php';
        $db = \getDB();
        
        $report = [];
        
        foreach ($routineCourses as $course) {
            $code = $course['code'];
            $facultyShort = $course['faculty_short'];
            $facultyName = isset($facultyRoster[$facultyShort]) ? $facultyRoster[$facultyShort]['name'] : 'Unknown';
            
            $expected = self::calculateExpectedClasses($batch, $code, $startDate, $endDate);
            
            // Get actual classes attended by this student
            $stmt = $db->prepare("
                SELECT COUNT(*) 
                FROM attendance a
                INNER JOIN courses c ON c.id = a.course_id
                WHERE a.user_id = ? AND (c.code LIKE ? OR c.name LIKE ?) 
                AND a.status = 'present' AND a.class_date BETWEEN ? AND ?
            ");
            $stmt->execute([$userId, "%$code%", "%$code%", $startDate, $endDate]);
            $attended = (int)$stmt->fetchColumn();
            
            // Get total classes conducted for this course and batch
            $stmt = $db->prepare("
                SELECT COUNT(DISTINCT a.class_date) 
                FROM attendance a
                INNER JOIN courses c ON c.id = a.course_id
                INNER JOIN users u ON u.id = a.user_id
                WHERE u.batch = ? AND (c.code LIKE ? OR c.name LIKE ?) AND a.class_date BETWEEN ? AND ?
            ");
            $stmt->execute([$batch, "%$code%", "%$code%", $startDate, $endDate]);
            $conducted = (int)$stmt->fetchColumn();
            
            $report[] = [
                'course_code' => $code,
                'faculty' => $facultyName,
                'expected' => $expected,
                'conducted' => $conducted,
                'attended' => $attended,
                'rate' => $conducted > 0 ? round(($attended / $conducted) * 100, 1) : 0
            ];
        }
        
        return $report;
    }
}
