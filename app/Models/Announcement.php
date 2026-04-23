<?php

namespace App\Models;

use function App\getDB;
use function App\clean;

/**
 * Announcement Model
 */
class Announcement
{
    public static function findAllForUser(int $semester, bool $isAdmin = false): array
    {
        $db = getDB();
        $where = ["(a.expires_at IS NULL OR a.expires_at >= CURDATE())"];
        $params = [];
        
        if (!$isAdmin) {
            $where[] = "(a.target_semester=0 OR a.target_semester=?)";
            $params[] = $semester;
        }

        $stmt = $db->prepare("SELECT a.*, u.name AS posted_by FROM announcements a
            JOIN users u ON a.user_id=u.id
            WHERE ".implode(' AND ',$where)."
            ORDER BY a.is_pinned DESC, a.created_at DESC");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function create(int $userId, array $data): bool
    {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO announcements (user_id, title, content, type, target_semester, is_pinned, expires_at) VALUES (?,?,?,?,?,?,?)");
        return $stmt->execute([
            $userId,
            clean($data['title']),
            clean($data['content']),
            clean($data['type'] ?? 'general'),
            (int)($data['target_semester'] ?? 0),
            isset($data['is_pinned']) ? 1 : 0,
            $data['expires_at'] ? clean($data['expires_at']) : null
        ]);
    }

    public static function delete(int $id): bool
    {
        $db = getDB();
        return $db->prepare("DELETE FROM announcements WHERE id=?")->execute([$id]);
    }

    public static function togglePin(int $id): bool
    {
        $db = getDB();
        return $db->prepare("UPDATE announcements SET is_pinned=NOT is_pinned WHERE id=?")->execute([$id]);
    }
}
