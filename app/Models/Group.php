<?php

namespace App\Models;

use function App\getDB;
use function App\clean;
use PDO;

/**
 * Group Model
 */
class Group
{
    public static function findMyGroups(int $userId): array
    {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT g.*, gm.role as my_role,
                (SELECT COUNT(*) FROM group_members WHERE group_id=g.id) AS member_count,
                (SELECT name FROM users WHERE id=g.creator_id) AS creator_name,
                (SELECT GROUP_CONCAT(u.name SEPARATOR ', ')
                    FROM group_members gm2
                    JOIN users u ON gm2.user_id=u.id
                    WHERE gm2.group_id=g.id
                    ORDER BY u.name ASC) AS member_names,
                s.name AS subject_name
            FROM study_groups g
            JOIN group_members gm ON gm.group_id=g.id AND gm.user_id=?
            LEFT JOIN subjects s ON g.subject_id=s.id
            ORDER BY g.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function findDiscoverGroups(int $userId): array
    {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT g.*,
                (SELECT COUNT(*) FROM group_members WHERE group_id=g.id) AS member_count,
                (SELECT name FROM users WHERE id=g.creator_id) AS creator_name,
                s.name AS subject_name
            FROM study_groups g
            LEFT JOIN subjects s ON g.subject_id=s.id
            WHERE g.is_public=1 AND g.id NOT IN (SELECT group_id FROM group_members WHERE user_id=?)
            ORDER BY member_count DESC LIMIT 20
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function create(int $userId, array $data): int
    {
        $db = getDB();
        $maxMembers = intval($data['max_members'] ?? 20);
        $stmt = $db->prepare("INSERT INTO study_groups (creator_id, subject_id, name, description, max_members) VALUES (?,?,?,?,?)");
        $stmt->execute([
            $userId,
            $data['subject_id'] ?: null,
            clean($data['name']),
            clean($data['description'] ?? ''),
            $maxMembers
        ]);
        $gid = (int) $db->lastInsertId();

        // Add creator as admin
        $db->prepare("INSERT INTO group_members (group_id, user_id, role) VALUES (?,?,'admin')")->execute([$gid, $userId]);

        // Add members if provided
        if (!empty($data['member_ids'])) {
            $memberIds = array_slice(array_unique(array_map('intval', $data['member_ids'])), 0, max(0, $maxMembers - 1));
            if (!empty($memberIds)) {
                $placeholders = implode(',', array_fill(0, count($memberIds), '?'));
                $validMembers = $db->prepare("SELECT id FROM users WHERE id IN ($placeholders) AND id != ?");
                $validMembers->execute(array_merge($memberIds, [$userId]));
                foreach ($validMembers->fetchAll(PDO::FETCH_COLUMN) as $memberId) {
                    $db->prepare("INSERT IGNORE INTO group_members (group_id, user_id) VALUES (?,?)")->execute([$gid, $memberId]);
                }
            }
        }
        return $gid;
    }

    public static function join(int $groupId, int $userId): bool
    {
        $db = getDB();
        $check = $db->prepare("SELECT COUNT(*) FROM group_members WHERE group_id=? AND user_id=?");
        $check->execute([$groupId, $userId]);
        if (!$check->fetchColumn()) {
            return $db->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?,?)")->execute([$groupId, $userId]);
        }
        return true;
    }

    public static function leave(int $groupId, int $userId): bool
    {
        $db = getDB();
        return $db->prepare("DELETE FROM group_members WHERE group_id=? AND user_id=?")->execute([$groupId, $userId]);
    }

    public static function getCount(): int
    {
        $db = getDB();
        return (int)$db->query("SELECT COUNT(*) FROM study_groups")->fetchColumn();
    }
}
