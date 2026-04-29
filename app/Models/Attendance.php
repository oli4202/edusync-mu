<?php

namespace App\Models;

use function getDB;

/**
 * Attendance Model
 */
class Attendance
{
    public static function findById(int $id): ?array
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM attendance WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByUser(int $userId): array
    {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT a.*, c.code AS course_code, c.name AS course_name
            FROM attendance a
            INNER JOIN courses c ON c.id = a.course_id
            WHERE a.user_id = ?
            ORDER BY a.class_date DESC, c.name ASC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function findByBatch(string $batch): array
    {
        $db = getDB();
        // If batch is empty, we still want to show something relevant, or nothing if strict
        if ($batch === '') return [];

        $stmt = $db->prepare("
            SELECT a.*, c.code AS course_code, c.name AS course_name, u.name AS student_name, u.student_id AS sid, u.avatar AS student_avatar
            FROM attendance a
            INNER JOIN courses c ON c.id = a.course_id
            INNER JOIN users u ON u.id = a.user_id
            WHERE u.batch = ?
            ORDER BY a.class_date DESC, c.name ASC, u.name ASC
        ");
        $stmt->execute([$batch]);
        return $stmt->fetchAll();
    }

    public static function getBatchStats(string $batch): array
    {
        $db = getDB();
        if ($batch === '') return ['total_records' => 0, 'present_count' => 0];

        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total_records,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count
            FROM attendance a
            INNER JOIN users u ON u.id = a.user_id
            WHERE u.batch = ?
        ");
        $stmt->execute([$batch]);
        return $stmt->fetch();
    }

    public static function record(int $userId, int $courseId, string $date, string $status, string $notes = ''): array
    {
        try {
            $db = getDB();
            $stmt = $db->prepare(
                "INSERT INTO attendance (user_id, course_id, class_date, status, notes) 
                 VALUES (?, ?, ?, ?, ?) 
                 ON DUPLICATE KEY UPDATE status = VALUES(status), notes = VALUES(notes)"
            );
            $stmt->execute([$userId, $courseId, $date, $status, $notes]);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function getExistingForCourseAndDate(int $courseId, string $date): array
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT user_id, status, notes FROM attendance WHERE course_id=? AND class_date=?");
        $stmt->execute([$courseId, $date]);
        $existing = [];
        foreach ($stmt->fetchAll() as $row) {
            $existing[$row['user_id']] = $row;
        }
        return $existing;
    }

    public static function getRecentHistoryForCourse(int $courseId, int $limit = 100): array
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT a.*, u.name AS student_name, u.student_id AS sid, u.avatar AS student_avatar
            FROM attendance a JOIN users u ON a.user_id=u.id 
            WHERE a.course_id=? ORDER BY a.class_date DESC, u.name ASC LIMIT ?");
        $stmt->execute([$courseId, $limit]);
        return $stmt->fetchAll();
    }

    public static function getTotalCount(): int
    {
        $db = getDB();
        return (int)$db->query("SELECT COUNT(*) FROM attendance")->fetchColumn();
    }

    public static function getTodayCount(): int
    {
        $db = getDB();
        $stmt = getDB()->prepare("SELECT COUNT(*) FROM attendance WHERE class_date=?");
        $stmt->execute([date('Y-m-d')]);
        return (int)$stmt->fetchColumn();
    }

    public static function getGridReport(int $courseId, string $batch): array
    {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT a.user_id, a.class_date, a.status 
            FROM attendance a
            INNER JOIN users u ON u.id = a.user_id
            WHERE a.course_id = ? AND u.batch = ?
            ORDER BY a.class_date ASC
        ");
        $stmt->execute([$courseId, $batch]);
        
        $data = [];
        foreach ($stmt->fetchAll() as $row) {
            $data[$row['user_id']][$row['class_date']] = $row['status'];
        }
        return $data;
    }

    public static function getUniqueDatesForCourse(int $courseId, string $batch): array
    {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT DISTINCT class_date 
            FROM attendance a
            INNER JOIN users u ON u.id = a.user_id
            WHERE a.course_id = ? AND u.batch = ?
            ORDER BY class_date ASC
        ");
        $stmt->execute([$courseId, $batch]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}
