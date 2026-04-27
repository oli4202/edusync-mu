<?php

namespace App\Models;

use function getDB;

/**
 * Task Model
 */
class Task
{
    public static function findById(int $id): ?array
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByUser(int $userId): array
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT t.*, s.name as subject_name FROM tasks t LEFT JOIN subjects s ON t.subject_id = s.id WHERE t.user_id = ? ORDER BY due_date");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function create(int $userId, array $data): array
    {
        try {
            $db = getDB();
            $stmt = $db->prepare(
                "INSERT INTO tasks (user_id, subject_id, title, description, due_date, priority, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $userId,
                $data['subject_id'] ?? null,
                $data['title'],
                $data['description'] ?? '',
                $data['due_date'] ?? null,
                $data['priority'] ?? 'normal',
                'pending'
            ]);
            return ['success' => true, 'id' => $db->lastInsertId()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function updateStatus(int $id, string $status): void
    {
        $db = getDB();
        $db->prepare("UPDATE tasks SET status = ? WHERE id = ?")->execute([$status, $id]);
    }

    public static function delete(int $id): void
    {
        $db = getDB();
        $db->prepare("DELETE FROM tasks WHERE id = ?")->execute([$id]);
    }

    public static function getTasksDueSoon(int $userId, int $days = 3): int
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM tasks WHERE user_id=? AND status!='done' AND due_date <= DATE_ADD(NOW(), INTERVAL ? DAY)");
        $stmt->execute([$userId, $days]);
        return (int)$stmt->fetchColumn();
    }

    public static function getDoneCount(int $userId): int
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM tasks WHERE user_id=? AND status='done'");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public static function getUpcomingTasks(int $userId, int $limit = 5): array
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT t.*, s.name AS subject_name, s.color FROM tasks t LEFT JOIN subjects s ON t.subject_id=s.id WHERE t.user_id=? AND t.status!='done' ORDER BY t.due_date ASC LIMIT ?");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
}
