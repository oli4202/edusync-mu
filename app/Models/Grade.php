<?php

namespace App\Models;

use function getDB;

/**
 * Grade Model
 */
class Grade
{
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
}
