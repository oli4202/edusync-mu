<?php

namespace App\Models;

use function App\getDB;

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
        $stmt = $db->prepare("SELECT * FROM attendance WHERE user_id = ? ORDER BY class_date DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
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
        $stmt = $db->prepare("SELECT a.*, u.name AS student_name, u.student_id AS sid 
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
}
