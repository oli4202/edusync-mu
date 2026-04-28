<?php

namespace App\Models;

use function getDB;

/**
 * Answer Model
 */
class Answer
{
    public static function findApprovedByQuestionId(int $questionId): array
    {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT a.*, u.name AS author_name, u.avatar AS author_avatar
            FROM answers a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE a.question_id = ? AND a.is_approved = 1
            ORDER BY a.upvotes DESC, a.created_at ASC
        ");
        $stmt->execute([$questionId]);
        return $stmt->fetchAll();
    }

    public static function create(int $questionId, int $userId, string $answerText, string $solutionSteps = ''): bool
    {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO answers (question_id, user_id, answer_text, solution_steps)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$questionId, $userId, clean($answerText), clean($solutionSteps)]);
    }

    public static function incrementUpvotes(int $answerId): ?int
    {
        $db = getDB();
        $db->prepare("UPDATE answers SET upvotes = upvotes + 1 WHERE id = ?")->execute([$answerId]);

        $stmt = $db->prepare("SELECT upvotes FROM answers WHERE id = ?");
        $stmt->execute([$answerId]);
        $upvotes = $stmt->fetchColumn();

        return $upvotes === false ? null : (int) $upvotes;
    }

    public static function findPending(): array
    {
        $db = getDB();
        $stmt = $db->query("
            SELECT a.*, q.question_text, u.name AS author 
            FROM answers a 
            JOIN questions q ON a.question_id = q.id 
            LEFT JOIN users u ON a.user_id = u.id 
            WHERE a.is_approved = 0 
            ORDER BY a.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public static function approve(int $id, int $approvedBy): bool
    {
        $db = getDB();
        $stmt = $db->prepare("UPDATE answers SET is_approved = 1, approved_by = ? WHERE id = ?");
        return $stmt->execute([$approvedBy, $id]);
    }

    public static function delete(int $id): bool
    {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM answers WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getCount(bool $approvedOnly = true): int
    {
        $db = getDB();
        $sql = "SELECT COUNT(*) FROM answers";
        if ($approvedOnly) {
            $sql .= " WHERE is_approved = 1";
        }
        return (int)$db->query($sql)->fetchColumn();
    }
}
