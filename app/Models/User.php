<?php

namespace App\Models;

use App\Support\StudentRoster;
use function getDB;

/**
 * User Model — Handles user-related database operations
 */
class User
{
    private static bool $rosterSynced = false;

    private static function normalizeGeneratedEmail(string $studentId): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '.', $studentId) ?? $studentId);
        $slug = trim($slug, '.');

        return $slug . '@students.edusync.mu';
    }

    private static function ensureRosterSchema(): void
    {
        $db = getDB();
        $db->exec("
            CREATE TABLE IF NOT EXISTS student_batch_memberships (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                student_id VARCHAR(30) NOT NULL,
                batch VARCHAR(20) NOT NULL,
                semester INT NOT NULL DEFAULT 1,
                label VARCHAR(50) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_student_batch_membership (student_id, batch, semester),
                KEY idx_membership_user (user_id),
                CONSTRAINT fk_membership_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
    }

    public static function ensureRosterSynced(): void
    {
        if (self::$rosterSynced) {
            return;
        }

        self::ensureRosterSchema();
        $db = getDB();
        $primaryAssignments = StudentRoster::primaryAssignments();

        foreach ($primaryAssignments as $studentId => $profile) {
            if (!$profile) {
                continue;
            }

            $existing = self::findByStudentId($studentId, false);
            $generatedEmail = self::normalizeGeneratedEmail($studentId);
            $name = $profile['name'];
            $batch = $profile['batch'];
            $semester = (int) $profile['semester'];

            if ($existing) {
                $stmt = $db->prepare("
                    UPDATE users
                    SET name = ?, batch = ?, semester = ?, department = COALESCE(NULLIF(department, ''), 'Software Engineering')
                    WHERE id = ?
                ");
                $stmt->execute([$name, $batch, $semester, $existing['id']]);
                $userId = (int) $existing['id'];
            } else {
                $stmt = $db->prepare("
                    INSERT INTO users (name, email, password, role, student_id, batch, semester, department)
                    VALUES (?, ?, ?, 'student', ?, ?, ?, 'Software Engineering')
                ");
                $stmt->execute([
                    $name,
                    $generatedEmail,
                    password_hash($studentId, PASSWORD_DEFAULT),
                    $studentId,
                    $batch,
                    $semester,
                ]);
                $userId = (int) $db->lastInsertId();
            }

            foreach (StudentRoster::findMemberships($studentId) as $membership) {
                $stmt = $db->prepare("
                    INSERT INTO student_batch_memberships (user_id, student_id, batch, semester, label)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        user_id = VALUES(user_id),
                        label = VALUES(label)
                ");
                $stmt->execute([
                    $userId,
                    $studentId,
                    $membership['batch'],
                    (int) $membership['semester'],
                    $membership['label'],
                ]);
            }
        }

        self::$rosterSynced = true;
    }

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

    public static function findByStudentId(string $studentId, bool $syncRoster = true): ?array
    {
        if ($syncRoster) {
            self::ensureRosterSynced();
        }

        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE student_id = ? LIMIT 1");
        $stmt->execute([$studentId]);

        return $stmt->fetch() ?: null;
    }

    public static function findByLoginIdentifier(string $identifier): ?array
    {
        self::ensureRosterSynced();
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? OR student_id = ? LIMIT 1");
        $stmt->execute([$identifier, $identifier]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Find user by ID
     */
    public static function findById(int $id): ?array
    {
        self::ensureRosterSynced();
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
        string $role = 'student',
        string $studentId = '',
        string $batch = '',
        int $semester = 1
    ): array {
        self::ensureRosterSynced();
        $db = getDB();

        // Check if email already exists
        if (self::findByEmail($email)) {
            return ['success' => false, 'message' => 'Email already registered.'];
        }

        if ($role === 'student' && $studentId !== '') {
            $rosterProfile = StudentRoster::findPrimary($studentId);
            if (!$rosterProfile) {
                return ['success' => false, 'message' => 'Student ID was not found in the official batch roster.'];
            }

            $batch = $rosterProfile['batch'];
            $semester = (int) $rosterProfile['semester'];

            $existingStudent = self::findByStudentId($studentId, false);
            if ($existingStudent) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                try {
                    $stmt = $db->prepare("
                        UPDATE users
                        SET name = ?, email = ?, password = ?, role = ?, batch = ?, semester = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $email, $hashedPassword, $role, $batch, $semester, $existingStudent['id']]);

                    self::syncMembershipsForUser((int) $existingStudent['id'], $studentId);

                    return [
                        'success' => true,
                        'id' => $existingStudent['id'],
                        'user' => self::findById((int) $existingStudent['id']),
                    ];
                } catch (\Exception $e) {
                    return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
                }
            }
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $db->prepare(
                "INSERT INTO users (name, email, password, role, student_id, batch, semester) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$name, $email, $hashedPassword, $role, $studentId, $batch, $semester]);

            if ($studentId !== '') {
                self::syncMembershipsForUser((int) $db->lastInsertId(), $studentId);
            }

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
        $user = self::findByLoginIdentifier($email);
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
        self::ensureRosterSynced();
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

    public static function getAllStudentsWithStats(): array
    {
        self::ensureRosterSynced();
        $db = getDB();
        $stmt = $db->query("
            SELECT u.id, u.name, u.student_id, u.batch, u.semester, u.avatar,
                   (SELECT COUNT(*) FROM attendance WHERE user_id = u.id) as total_classes,
                   (SELECT ROUND((SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0)) * 100, 1) FROM attendance WHERE user_id = u.id) as attendance_rate
            FROM users u
            WHERE u.role = 'student'
            ORDER BY u.batch ASC, u.student_id ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get all users (admin)
     */
    public static function getAll(): array
    {
        self::ensureRosterSynced();
        $db = getDB();
        $stmt = $db->query("SELECT id, name, email, student_id, batch, semester, role, created_at FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    /**
     * Delete user (admin)
     */
    public static function delete(int $userId): array
    {
        try {
            self::ensureRosterSynced();
            $db = getDB();
            $db->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Delete failed: ' . $e->getMessage()];
        }
    }

    /**
     * Get classmates for a user
     */
    public static function getClassmates(int $userId, int $semester): array
    {
        self::ensureRosterSynced();
        $db = getDB();
        $stmt = $db->prepare("SELECT id, name, email, semester FROM users WHERE id != ? AND role='student' ORDER BY ABS(semester - ?), name ASC LIMIT 20");
        $stmt->execute([$userId, $semester]);
        return $stmt->fetchAll();
    }

    /**
     * Find potential study partners
     */
    public static function findPartners(int $userId, string $search = ''): array
    {
        self::ensureRosterSynced();
        $db = getDB();
        $query = "
            SELECT u.id, u.name, u.email, u.student_id, u.batch, u.semester, u.department, u.streak, u.bio, u.avatar,
                (SELECT COUNT(*) FROM follows WHERE follower_id=? AND following_id=u.id) AS is_following,
                (SELECT COUNT(*) FROM follows WHERE follower_id=u.id) AS following_count,
                (SELECT COUNT(*) FROM follows WHERE following_id=u.id) AS follower_count,
                (SELECT COUNT(*) FROM group_members WHERE user_id=u.id) AS group_count
            FROM users u
            WHERE u.id != ? AND u.role = 'student'
        ";
        $params = [$userId, $userId];
        if ($search) {
            $query .= " AND (u.name LIKE ? OR u.student_id LIKE ? OR u.batch LIKE ?)";
            $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
        }
        $query .= " ORDER BY is_following DESC, u.name ASC LIMIT 50";
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function follow(int $followerId, int $followingId): bool
    {
        if ($followerId === $followingId) return false;
        $db = getDB();
        $stmt = $db->prepare("INSERT IGNORE INTO follows (follower_id, following_id) VALUES (?,?)");
        return $stmt->execute([$followerId, $followingId]);
    }

    public static function unfollow(int $followerId, int $followingId): bool
    {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM follows WHERE follower_id=? AND following_id=?");
        return $stmt->execute([$followerId, $followingId]);
    }

    public static function getConnectionCounts(int $userId): array
    {
        self::ensureRosterSynced();
        $db = getDB();
        $following = $db->prepare("SELECT COUNT(*) FROM follows WHERE follower_id=?");
        $following->execute([$userId]);
        $followers = $db->prepare("SELECT COUNT(*) FROM follows WHERE following_id=?");
        $followers->execute([$userId]);
        return [
            'following' => (int)$following->fetchColumn(),
            'followers' => (int)$followers->fetchColumn()
        ];
    }

    public static function getStudentCount(): int
    {
        self::ensureRosterSynced();
        $db = getDB();
        return (int)$db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
    }

    public static function getBatchOptions(): array
    {
        return StudentRoster::batchOptions();
    }

    public static function getStudentsForAttendance(string $batch = '', int $semester = 0, int $courseId = 0): array
    {
        self::ensureRosterSynced();
        $db = getDB();
        $conditions = ["u.role = 'student'"];
        $params = [];

        if ($courseId > 0) {
            $course = Course::findById($courseId);
            if ($course && !empty($course['batch'])) {
                $courseBatches = array_filter(array_map('trim', explode(',', (string) $course['batch'])));
                if ($courseBatches !== []) {
                    $placeholders = implode(',', array_fill(0, count($courseBatches), '?'));
                    $conditions[] = "m.batch IN ($placeholders)";
                    foreach ($courseBatches as $courseBatch) {
                        $params[] = $courseBatch;
                    }
                }
            }
        }

        if ($batch !== '') {
            $conditions[] = "m.batch = ?";
            $params[] = $batch;
        }

        if ($semester > 0) {
            $conditions[] = "m.semester = ?";
            $params[] = $semester;
        }

        $sql = "
            SELECT DISTINCT u.id, u.name, u.email, u.student_id, u.batch, u.semester, u.avatar,
                GROUP_CONCAT(DISTINCT CONCAT('Batch ', m.batch, ' / Sem ', m.semester) ORDER BY m.semester DESC SEPARATOR ', ') AS memberships
            FROM users u
            INNER JOIN student_batch_memberships m ON m.user_id = u.id
            WHERE " . implode(' AND ', $conditions) . "
            GROUP BY u.id, u.name, u.email, u.student_id, u.batch, u.semester, u.avatar
            ORDER BY u.name ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function getStudentOverviewByStudentId(string $studentId): ?array
    {
        self::ensureRosterSynced();
        $studentId = trim($studentId);
        if ($studentId === '') {
            return null;
        }

        $user = self::findByStudentId($studentId, false);
        if (!$user) {
            return null;
        }

        $db = getDB();
        $membershipsStmt = $db->prepare("
            SELECT batch, semester, label
            FROM student_batch_memberships
            WHERE user_id = ?
            ORDER BY semester DESC, batch ASC
        ");
        $membershipsStmt->execute([$user['id']]);
        $memberships = $membershipsStmt->fetchAll();

        $subjectsStmt = $db->prepare("
            SELECT name, code, semester, year
            FROM subjects
            WHERE user_id = ?
            ORDER BY semester ASC, name ASC
        ");
        $subjectsStmt->execute([$user['id']]);
        $subjects = $subjectsStmt->fetchAll();

        $attendanceSummaryStmt = $db->prepare("
            SELECT
                COUNT(*) AS total_classes,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS present_count,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) AS absent_count,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) AS late_count,
                ROUND((SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0)) * 100, 1) AS present_percentage
            FROM attendance
            WHERE user_id = ?
        ");
        $attendanceSummaryStmt->execute([$user['id']]);
        $attendanceSummary = $attendanceSummaryStmt->fetch() ?: [];

        $attendanceStmt = $db->prepare("
            SELECT a.class_date, a.status, a.notes, c.code, c.name AS course_name
            FROM attendance a
            INNER JOIN courses c ON c.id = a.course_id
            WHERE a.user_id = ?
            ORDER BY a.class_date DESC
            LIMIT 10
        ");
        $attendanceStmt->execute([$user['id']]);
        $recentAttendance = $attendanceStmt->fetchAll();

        return [
            'user' => $user,
            'memberships' => $memberships,
            'subjects' => $subjects,
            'attendance_summary' => $attendanceSummary,
            'recent_attendance' => $recentAttendance,
        ];
    }

    public static function syncMembershipsForUser(int $userId, string $studentId): void
    {
        self::ensureRosterSchema();
        $db = getDB();

        foreach (StudentRoster::findMemberships($studentId) as $membership) {
            $stmt = $db->prepare("
                INSERT INTO student_batch_memberships (user_id, student_id, batch, semester, label)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    user_id = VALUES(user_id),
                    label = VALUES(label)
            ");
            $stmt->execute([
                $userId,
                $studentId,
                $membership['batch'],
                (int) $membership['semester'],
                $membership['label'],
            ]);
        }
    }
}
