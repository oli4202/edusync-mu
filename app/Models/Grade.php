<?php

namespace App\Models;

use function App\getDB;

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
                "INSERT INTO grades (user_id, subject_id, exam_name, marks_obtained, total_marks, exam_date) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $userId,
                $data['subject_id'],
                $data['exam_name'],
                $data['marks_obtained'],
                $data['total_marks'],
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
            "SELECT subject_id, AVG(marks_obtained / total_marks * 100) as avg_score 
             FROM grades 
             WHERE user_id=? 
             GROUP BY subject_id"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
