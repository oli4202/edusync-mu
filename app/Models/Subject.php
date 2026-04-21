<?php

namespace App\Models;

use function App\getDB;

/**
 * Subject Model
 */
class Subject
{
    public static function findById(int $id): ?array
    {
        $db = getDB();
        return $db->prepare("SELECT * FROM subjects WHERE id = ?")->execute([$id])->fetch() ?: null;
    }

    public static function findByUser(int $userId): array
    {
        $db = getDB();
        return $db->prepare("SELECT * FROM subjects WHERE user_id = ? ORDER BY name")->execute([$userId])->fetchAll();
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
}

/**
 * Task Model
 */
class Task
{
    public static function findById(int $id): ?array
    {
        $db = getDB();
        return $db->prepare("SELECT * FROM tasks WHERE id = ?")->execute([$id])->fetch() ?: null;
    }

    public static function findByUser(int $userId): array
    {
        $db = getDB();
        return $db->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY due_date")->execute([$userId])->fetchAll();
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
}

/**
 * Grade Model
 */
class Grade
{
    public static function findById(int $id): ?array
    {
        $db = getDB();
        return $db->prepare("SELECT * FROM grades WHERE id = ?")->execute([$id])->fetch() ?: null;
    }

    public static function findByUser(int $userId): array
    {
        $db = getDB();
        return $db->prepare("SELECT * FROM grades WHERE user_id = ? ORDER BY exam_date DESC")->execute([$userId])->fetchAll();
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
}

/**
 * Attendance Model
 */
class Attendance
{
    public static function findById(int $id): ?array
    {
        $db = getDB();
        return $db->prepare("SELECT * FROM attendance WHERE id = ?")->execute([$id])->fetch() ?: null;
    }

    public static function findByUser(int $userId): array
    {
        $db = getDB();
        return $db->prepare("SELECT * FROM attendance WHERE user_id = ? ORDER BY class_date DESC")->execute([$userId])->fetchAll();
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
}
