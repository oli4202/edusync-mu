<?php

namespace App\Models;

use function getDB;

/**
 * Course Model
 */
class Course
{
    private const TERM_MAP = [
        1 => 'Spring',
        2 => 'Summer',
        0 => 'Winter',
    ];
    private static bool $facultyAssignmentsSynced = false;

    public static function ensureHardcodedFacultyAssignments(): void
    {
        if (self::$facultyAssignmentsSynced) {
            return;
        }

        $db = getDB();
        $facultyIds = $db->query("SELECT id FROM users WHERE role = 'faculty' ORDER BY id ASC")->fetchAll();
        if (empty($facultyIds)) {
            self::$facultyAssignmentsSynced = true;
            return;
        }

        $courses = $db->query("SELECT id, batch, semester FROM courses WHERE status = 'active' ORDER BY id ASC")->fetchAll();
        $facultyCount = count($facultyIds);
        $insertStmt = $db->prepare("
            INSERT IGNORE INTO teacher_course_assignments (teacher_id, course_id, batch, semester)
            VALUES (?, ?, ?, ?)
        ");
        $countStmt = $db->prepare("SELECT COUNT(*) FROM teacher_course_assignments WHERE course_id = ?");

        foreach ($courses as $idx => $course) {
            $courseId = (int)$course['id'];
            if ($courseId <= 0) {
                continue;
            }

            $countStmt->execute([$courseId]);
            if ((int)$countStmt->fetchColumn() > 0) {
                continue;
            }

            $teacherId = (int)$facultyIds[$idx % $facultyCount]['id'];
            $semester = (int)($course['semester'] ?? 0);
            $batchTokens = array_values(array_filter(array_map(
                static fn ($token) => preg_replace('/[^0-9]/', '', trim((string)$token)),
                explode(',', (string)($course['batch'] ?? ''))
            )));

            if (empty($batchTokens)) {
                $insertStmt->execute([$teacherId, $courseId, null, $semester]);
                continue;
            }

            foreach ($batchTokens as $batchToken) {
                $insertStmt->execute([$teacherId, $courseId, $batchToken, $semester]);
            }
        }

        self::$facultyAssignmentsSynced = true;
    }
    /**
     * Get all courses
     */
    public static function getAll(): array
    {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM courses ORDER BY year, semester, name");
        return $stmt->fetchAll();
    }

    /**
     * Find course by ID
     */
    public static function findById(int $id): ?array
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByCode(string $code): ?array
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM courses WHERE code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch() ?: null;
    }

    public static function findByCodeFlexible(string $code): ?array
    {
        $code = trim($code);
        if ($code === '') {
            return null;
        }

        $db = getDB();
        $stmt = $db->prepare("
            SELECT *
            FROM courses
            WHERE UPPER(REPLACE(code, ' ', '')) = UPPER(REPLACE(?, ' ', ''))
            LIMIT 1
        ");
        $stmt->execute([$code]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get courses by year and semester
     */
    public static function findBySemester(int $year, int $semester): array
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM courses WHERE year = ? AND semester = ? ORDER BY name");
        $stmt->execute([$year, $semester]);
        return $stmt->fetchAll();
    }

    /**
     * Get courses by batch
     */
    public static function findByBatch(string $batch): array
    {
        $db = getDB();
        // Clean batch name (e.g., "Batch 5" -> "5")
        $batchNum = preg_replace('/[^0-9]/', '', $batch);
        
        $stmt = $db->prepare("SELECT * FROM courses WHERE (FIND_IN_SET(?, batch) OR batch IS NULL) AND status = 'active' ORDER BY year, semester, name");
        $stmt->execute([$batchNum]);
        return $stmt->fetchAll();
    }

    /**
     * Get distinct semesters for a specific batch
     */
    public static function getSemestersByBatch(string $batch): array
    {
        $db = getDB();
        $batchNum = preg_replace('/[^0-9]/', '', $batch);
        $stmt = $db->prepare("SELECT DISTINCT semester FROM courses WHERE (FIND_IN_SET(?, batch) OR batch IS NULL) AND status = 'active' ORDER BY semester");
        $stmt->execute([$batchNum]);
        return array_column($stmt->fetchAll(), 'semester');
    }

    public static function getSemesterOptionsByBatch(string $batch): array
    {
        $semesters = self::getSemestersByBatch($batch);
        sort($semesters, SORT_NUMERIC);

        $options = [];
        foreach ($semesters as $semester) {
            $sem = (int)$semester;
            $options[] = [
                'value' => $sem,
                'label' => sprintf('Semester %d (%s)', $sem, self::getSemesterTerm($sem)),
                'term' => self::getSemesterTerm($sem),
                'year_index' => (int)ceil($sem / 3),
            ];
        }

        return $options;
    }

    public static function getSemesterTerm(int $semester): string
    {
        if ($semester <= 0) {
            return 'Unknown';
        }

        $mod = $semester % 3;
        return self::TERM_MAP[$mod] ?? 'Unknown';
    }

    /**
     * Find courses by batch and semester
     */
    public static function findByBatchAndSemester(string $batch, int $semester): array
    {
        $db = getDB();
        $batchNum = preg_replace('/[^0-9]/', '', $batch);
        $stmt = $db->prepare("SELECT * FROM courses WHERE (FIND_IN_SET(?, batch) OR batch IS NULL) AND semester = ? AND status = 'active' ORDER BY name");
        $stmt->execute([$batchNum, $semester]);
        return $stmt->fetchAll();
    }

    public static function getDistinctBatches(): array
    {
        $db = getDB();
        $stmt = $db->query("SELECT DISTINCT batch FROM courses WHERE batch IS NOT NULL AND batch != '' ORDER BY batch");
        $batches = [];

        foreach ($stmt->fetchAll() as $row) {
            foreach (explode(',', (string) $row['batch']) as $batch) {
                $batch = trim($batch);
                if ($batch !== '') {
                    $batches[$batch] = $batch;
                }
            }
        }

        ksort($batches, SORT_NATURAL);

        return array_values($batches);
    }

    /**
     * Check if a teacher is assigned to teach a course
     * 
     * @param int $teacherId The teacher's user ID
     * @param int $courseId The course ID
     * @param string $batch Optional: batch to check for
     * @return bool True if teacher teaches this course
     */
    public static function isTeacherAssigned(int $teacherId, int $courseId, string $batch = ''): bool
    {
        self::ensureHardcodedFacultyAssignments();
        $db = getDB();
        
        if (!empty($batch)) {
            $batchNum = preg_replace('/[^0-9]/', '', $batch);
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM teacher_course_assignments 
                WHERE teacher_id = ? AND course_id = ? AND batch = ?
            ");
            $stmt->execute([$teacherId, $courseId, $batchNum]);
            return (int)$stmt->fetchColumn() > 0;
        } else {
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM teacher_course_assignments 
                WHERE teacher_id = ? AND course_id = ?
            ");
            $stmt->execute([$teacherId, $courseId]);
            return (int)$stmt->fetchColumn() > 0;
        }
    }

    /**
     * Get all courses assigned to a teacher
     * 
     * @param int $teacherId The teacher's user ID
     * @return array List of courses the teacher teaches
     */
    public static function getTeacherCourses(int $teacherId): array
    {
        self::ensureHardcodedFacultyAssignments();
        $db = getDB();
        $stmt = $db->prepare("
            SELECT c.*, tca.batch, tca.semester
            FROM courses c
            JOIN teacher_course_assignments tca ON c.id = tca.course_id
            WHERE tca.teacher_id = ?
            ORDER BY tca.batch, c.semester, c.name
        ");
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }

    /**
     * Get courses assigned to a teacher for a specific batch
     * 
     * @param int $teacherId The teacher's user ID
     * @param string $batch The batch
     * @return array List of courses for that batch
     */
    public static function getTeacherCoursesByBatch(int $teacherId, string $batch): array
    {
        self::ensureHardcodedFacultyAssignments();
        $db = getDB();
        $batchNum = preg_replace('/[^0-9]/', '', $batch);
        $stmt = $db->prepare("
            SELECT c.*, tca.batch, tca.semester
            FROM courses c
            JOIN teacher_course_assignments tca ON c.id = tca.course_id
            WHERE tca.teacher_id = ? AND tca.batch = ?
            ORDER BY c.semester, c.name
        ");
        $stmt->execute([$teacherId, $batchNum]);
        return $stmt->fetchAll();
    }

    /**
     * Enroll a student in a course
     * 
     * @param int $userId The student's user ID
     * @param int $courseId The course ID
     * @param string $batch The batch
     * @param int $semester The semester
     * @return array Result with success and message
     */
    public static function enrollStudent(int $userId, int $courseId, string $batch, int $semester): array
    {
        $db = getDB();
        
        // Check if already enrolled
        $stmt = $db->prepare("SELECT id FROM student_course_enrollments WHERE user_id = ? AND course_id = ?");
        $stmt->execute([$userId, $courseId]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Already enrolled in this course'];
        }

        try {
            $stmt = $db->prepare("
                INSERT INTO student_course_enrollments (user_id, course_id, batch, semester)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $courseId, $batch, $semester]);
            return ['success' => true, 'message' => 'Enrolled successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Enrollment failed: ' . $e->getMessage()];
        }
    }

    /**
     * Unenroll a student from a course
     * 
     * @param int $userId The student's user ID
     * @param int $courseId The course ID
     * @return array Result with success and message
     */
    public static function unenrollStudent(int $userId, int $courseId): array
    {
        $db = getDB();
        
        try {
            $stmt = $db->prepare("DELETE FROM student_course_enrollments WHERE user_id = ? AND course_id = ?");
            $stmt->execute([$userId, $courseId]);
            return ['success' => true, 'message' => 'Unenrolled successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Unenrollment failed'];
        }
    }

    /**
     * Get all enrolled students for a course
     * 
     * @param int $courseId The course ID
     * @return array List of enrolled students with user details
     */
    public static function getEnrolledStudents(int $courseId): array
    {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT u.id, u.name, u.email, u.student_id, u.batch, u.semester, sce.enrolled_at
            FROM student_course_enrollments sce
            JOIN users u ON sce.user_id = u.id
            WHERE sce.course_id = ? AND u.role = 'student'
            ORDER BY u.name
        ");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    /**
     * Check if student is enrolled in a course
     * 
     * @param int $userId The student's user ID
     * @param int $courseId The course ID
     * @return bool True if enrolled
     */
    public static function isStudentEnrolled(int $userId, int $courseId): bool
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM student_course_enrollments WHERE user_id = ? AND course_id = ?");
        $stmt->execute([$userId, $courseId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Get all courses a student is enrolled in
     * 
     * @param int $userId The student's user ID
     * @return array List of enrolled courses
     */
    public static function getStudentEnrolledCourses(int $userId): array
    {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT
                c.*,
                sce.batch,
                sce.semester,
                sce.enrolled_at,
                GROUP_CONCAT(
                    DISTINCT faculty.name
                    ORDER BY faculty.name
                    SEPARATOR ', '
                ) AS assigned_faculty
            FROM student_course_enrollments sce
            JOIN courses c ON sce.course_id = c.id
            LEFT JOIN teacher_course_assignments tca
                ON tca.course_id = c.id
                AND (tca.batch IS NULL OR tca.batch = '' OR tca.batch = sce.batch)
                AND (tca.semester IS NULL OR tca.semester = 0 OR tca.semester = sce.semester)
            LEFT JOIN users faculty
                ON faculty.id = tca.teacher_id
                AND faculty.role = 'faculty'
            WHERE sce.user_id = ?
            GROUP BY c.id, sce.batch, sce.semester, sce.enrolled_at
            ORDER BY c.year, c.semester, c.name
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}


