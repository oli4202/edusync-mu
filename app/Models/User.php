<?php

namespace App\Models;

use function App\getDB;

/**
 * User Model — Handles user-related database operations
 */
class User
{
    /**
     * Find user by email
     */
    public static function findByEmail(string $email): ?array
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Find user by ID
     */
    public static function findById(int $id): ?array
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Register a new user
     */
    public static function register(
        string $name,
        string $email,
        string $password,
        string $studentId = '',
        string $batch = '',
        int $semester = 1
    ): array {
        $db = getDB();

        // Check if email already exists
        if (self::findByEmail($email)) {
            return ['success' => false, 'message' => 'Email already registered.'];
        }

        // Hash password and insert
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $db->prepare(
                "INSERT INTO users (name, email, password, student_id, batch, semester) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$name, $email, $hashedPassword, $studentId, $batch, $semester]);

            return [
                'success' => true,
                'id' => $db->lastInsertId(),
                'user' => self::findById($db->lastInsertId())
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }

    /**
     * Authenticate user with email and password
     */
    public static function authenticate(string $email, string $password): array
    {
        $user = self::findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        // Update streak and last_active
        self::updateStreak($user['id'], $user['last_active'], $user['streak']);

        return ['success' => true, 'user' => $user];
    }

    /**
     * Update user's learning streak
     */
    private static function updateStreak(int $userId, ?string $lastActive, int $currentStreak): void
    {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $streak = $currentStreak;
        if ($lastActive === $yesterday) {
            $streak++;
        } elseif ($lastActive !== $today) {
            $streak = 1;
        }

        $db = getDB();
        $db->prepare("UPDATE users SET streak = ?, last_active = ? WHERE id = ?")
            ->execute([$streak, $today, $userId]);
    }

    /**
     * Update user profile
     */
    public static function update(int $userId, array $data): array
    {
        $db = getDB();
        $allowedFields = ['name', 'bio', 'avatar', 'semester'];
        $updates = [];
        $values = [];

        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $updates[] = "$field = ?";
                $values[] = $value;
            }
        }

        if (empty($updates)) {
            return ['success' => false, 'message' => 'No valid fields to update.'];
        }

        $values[] = $userId;
        try {
            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            $db->prepare($sql)->execute($values);
            return ['success' => true, 'user' => self::findById($userId)];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Update failed: ' . $e->getMessage()];
        }
    }

    /**
     * Get all users (admin)
     */
    public static function getAll(): array
    {
        $db = getDB();
        $stmt = $db->query("SELECT id, name, email, student_id, semester, role, created_at FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    /**
     * Delete user (admin)
     */
    public static function delete(int $userId): array
    {
        try {
            $db = getDB();
            $db->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Delete failed: ' . $e->getMessage()];
        }
    }
}
