<?php

namespace App\Models;

use function getDB;

/**
 * Subject Model
 */
class Subject
{
    public static function findById(int $id): ?array
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM subjects WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByUser(int $userId): array
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM subjects WHERE user_id = ? ORDER BY name");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function findByUserWithStats(int $userId): array
    {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT s.*,
                (SELECT COUNT(*) FROM tasks WHERE subject_id=s.id AND status!='done') AS pending_tasks,
                (SELECT COUNT(*) FROM tasks WHERE subject_id=s.id AND status='done') AS done_tasks,
                (SELECT COALESCE(SUM(hours),0) FROM study_logs WHERE subject_id=s.id AND WEEK(logged_date)=WEEK(NOW())) AS week_hours
            FROM subjects s WHERE s.user_id=? ORDER BY s.name
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function create(int $userId, array $data): array
    {
        try {
            $db = getDB();
            $stmt = $db->prepare(
                "INSERT INTO subjects (user_id, name, code, color, year, semester, target_hours_per_week) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $userId,
                $data['name'],
                $data['code'] ?? null,
                $data['color'] ?? '#4f46e5',
                $data['year'] ?? null,
                $data['semester'] ?? 1,
                $data['target_hours_per_week'] ?? 5.0
            ]);
            return ['success' => true, 'id' => $db->lastInsertId()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function update(int $id, array $data): array
    {
        try {
            $db = getDB();
            $updates = [];
            $values = [];
            foreach (['name', 'code', 'color', 'year', 'semester', 'target_hours_per_week'] as $field) {
                if (isset($data[$field])) {
                    $updates[] = "$field = ?";
                    $values[] = $data[$field];
                }
            }
            $values[] = $id;
            $stmt = $db->prepare("UPDATE subjects SET " . implode(', ', $updates) . " WHERE id = ?");
            $stmt->execute($values);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function delete(int $id): void
    {
        $db = getDB();
        $db->prepare("DELETE FROM subjects WHERE id = ?")->execute([$id]);
    }

    public static function syncForUserBatchSemester(int $userId, string $batch, int $semester): void
    {
        if ($batch === '' || $semester <= 0) {
            return;
        }

        $db = getDB();
        $courses = Course::findByBatchAndSemester($batch, $semester);

        foreach ($courses as $course) {
            $existsStmt = $db->prepare("SELECT id FROM subjects WHERE user_id = ? AND code = ? LIMIT 1");
            $existsStmt->execute([$userId, $course['code']]);
            if ($existsStmt->fetch()) {
                continue;
            }

            $stmt = $db->prepare("
                INSERT INTO subjects (user_id, name, code, color, year, semester, target_hours_per_week)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $course['name'],
                $course['code'],
                '#22d3ee',
                (int) ($course['year'] ?? date('Y')),
                (int) ($course['semester'] ?? $semester),
                5.0,
            ]);
        }
    }
}
