<?php

namespace App\Models;

use function getDB;

/**
 * Grade Model
 */
class Grade
{
    private static function letterGradeFromPercent(float $percent): array
    {
        if ($percent >= 80) return ['grade' => 'A+', 'gp' => 4.00];
        if ($percent >= 75) return ['grade' => 'A', 'gp' => 3.75];
        if ($percent >= 70) return ['grade' => 'A-', 'gp' => 3.50];
        if ($percent >= 65) return ['grade' => 'B+', 'gp' => 3.25];
        if ($percent >= 60) return ['grade' => 'B', 'gp' => 3.00];
        if ($percent >= 55) return ['grade' => 'B-', 'gp' => 2.75];
        if ($percent >= 50) return ['grade' => 'C+', 'gp' => 2.50];
        if ($percent >= 45) return ['grade' => 'C', 'gp' => 2.25];
        if ($percent >= 40) return ['grade' => 'D', 'gp' => 2.00];
        return ['grade' => 'F', 'gp' => 0.00];
    }

    public static function findById(int $id): ?array
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM grades WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByUser(int $userId): array
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM grades WHERE user_id = ? ORDER BY exam_date DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function findAllForStudentsView(): array
    {
        $db = getDB();
        $stmt = $db->query("
            SELECT
                g.id,
                g.user_id,
                g.subject_id,
                g.title AS exam_name,
                g.score AS marks_obtained,
                g.max_score AS total_marks,
                g.exam_date,
                g.notes,
                g.created_at,
                u.name AS student_name,
                u.student_id,
                s.name AS subject_name,
                s.code AS subject_code
            FROM grades g
            INNER JOIN users u ON u.id = g.user_id
            LEFT JOIN subjects s ON s.id = g.subject_id
            WHERE u.role = 'student'
            ORDER BY g.exam_date DESC, u.name ASC
        ");
        return $stmt->fetchAll();
    }

    public static function create(int $userId, array $data): array
    {
        try {
            $db = getDB();
            $stmt = $db->prepare(
                "INSERT INTO grades (user_id, subject_id, title, score, max_score, exam_date) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $userId,
                $data['subject_id'],
                $data['title'],
                $data['score'],
                $data['max_score'],
                $data['exam_date'] ?? date('Y-m-d')
            ]);
            return ['success' => true, 'id' => $db->lastInsertId()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function getAverageScoresBySubject(int $userId): array
    {
        $db = getDB();
        $stmt = $db->prepare(
            "SELECT g.subject_id, s.name as subject_name, AVG(g.score / g.max_score * 100) as avg_score 
             FROM grades g
             JOIN subjects s ON g.subject_id = s.id
             WHERE g.user_id=? 
             GROUP BY g.subject_id, s.name"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function getBatchSemesterResultSheet(string $batch, int $semester): array
    {
        $batch = trim($batch);
        if ($batch === '' || $semester <= 0) {
            return ['courses' => [], 'students' => []];
        }

        $courses = Course::findByBatchAndSemester($batch, $semester);
        $courseMap = [];
        foreach ($courses as $course) {
            $code = (string)($course['code'] ?? '');
            if ($code !== '') {
                $courseMap[$code] = [
                    'code' => $code,
                    'name' => (string)($course['name'] ?? $code),
                ];
            }
        }

        $students = User::getStudentsForAttendance($batch, $semester, 0);
        $db = getDB();
        $aggregateStmt = $db->prepare("
            SELECT
                s.code AS course_code,
                s.name AS course_name,
                SUM(g.score) AS sum_score,
                SUM(g.max_score) AS sum_max
            FROM grades g
            INNER JOIN subjects s ON s.id = g.subject_id
            WHERE g.user_id = ? AND s.semester = ?
            GROUP BY s.code, s.name
        ");

        $rows = [];
        foreach ($students as $student) {
            $studentId = (int)$student['id'];
            $aggregateStmt->execute([$studentId, $semester]);
            $subjectRows = $aggregateStmt->fetchAll();

            $subjectMap = [];
            foreach ($subjectRows as $sr) {
                $code = (string)($sr['course_code'] ?? '');
                if ($code === '') {
                    continue;
                }
                $sumScore = (float)($sr['sum_score'] ?? 0);
                $sumMax = (float)($sr['sum_max'] ?? 0);
                $marks100 = $sumMax > 0 ? round(($sumScore / $sumMax) * 100, 1) : 0.0;
                $gradeInfo = self::letterGradeFromPercent($marks100);
                $subjectMap[$code] = [
                    'code' => $code,
                    'name' => (string)($sr['course_name'] ?? $code),
                    'marks_100' => $marks100,
                    'grade' => $gradeInfo['grade'],
                    'gp' => $gradeInfo['gp'],
                ];
            }

            foreach ($courseMap as $code => $courseInfo) {
                if (!isset($subjectMap[$code])) {
                    $subjectMap[$code] = [
                        'code' => $code,
                        'name' => $courseInfo['name'],
                        'marks_100' => 0.0,
                        'grade' => 'F',
                        'gp' => 0.0,
                    ];
                }
            }

            ksort($subjectMap, SORT_NATURAL);
            $subjectList = array_values($subjectMap);
            $courseCount = max(1, count($subjectList));
            $overall = round(array_sum(array_column($subjectList, 'marks_100')) / $courseCount, 2);
            $overallGrade = self::letterGradeFromPercent($overall);

            $rows[] = [
                'student' => [
                    'id' => $studentId,
                    'name' => (string)($student['name'] ?? ''),
                    'student_id' => (string)($student['student_id'] ?? ''),
                    'avatar' => (string)($student['avatar'] ?? ''),
                ],
                'subjects' => $subjectList,
                'overall_marks' => $overall,
                'overall_grade' => $overallGrade['grade'],
                'overall_gp' => $overallGrade['gp'],
            ];
        }

        usort($rows, static function (array $a, array $b): int {
            return strcmp((string)$a['student']['student_id'], (string)$b['student']['student_id']);
        });

        return [
            'courses' => array_values($courseMap),
            'students' => $rows,
        ];
    }
}
