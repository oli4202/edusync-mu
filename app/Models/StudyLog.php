<?php

namespace App\Models;

use function App\getDB;

/**
 * StudyLog Model
 */
class StudyLog
{
    public static function findByUser(int $userId): array
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM study_logs WHERE user_id = ? ORDER BY logged_date DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function getWeeklyHours(int $userId): float
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT COALESCE(SUM(hours),0) FROM study_logs WHERE user_id=? AND WEEK(logged_date)=WEEK(NOW())");
        $stmt->execute([$userId]);
        return (float)$stmt->fetchColumn();
    }

    public static function getRecentLogs(int $userId, int $days = 30): array
    {
        $db = getDB();
        $stmt = $db->prepare(
            "SELECT DATE(logged_date) as date, SUM(hours) as total
             FROM study_logs
             WHERE user_id=? AND logged_date >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY DATE(logged_date)
             ORDER BY date DESC"
        );
        $stmt->execute([$userId, $days]);
        return $stmt->fetchAll();
    }

    public static function create(int $userId, array $data): array
    {
        try {
            $db = getDB();
            $stmt = $db->prepare(
                "INSERT INTO study_logs (user_id, subject_id, hours, logged_date, notes)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $userId,
                $data['subject_id'] ?? null,
                $data['hours'],
                $data['logged_date'] ?? date('Y-m-d'),
                $data['notes'] ?? ''
            ]);
            return ['success' => true, 'id' => $db->lastInsertId()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}