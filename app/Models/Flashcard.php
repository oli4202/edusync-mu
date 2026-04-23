<?php

namespace App\Models;

use function App\getDB;
use function App\clean;
use function App\callAI;

/**
 * Flashcard Model
 */
class Flashcard
{
    public static function findByUser(int $userId, ?string $deck = null): array
    {
        $db = getDB();
        if ($deck) {
            $stmt = $db->prepare("SELECT f.*, s.name AS subject_name FROM flashcards f LEFT JOIN subjects s ON f.subject_id=s.id WHERE f.user_id=? AND f.deck_name=? ORDER BY f.created_at DESC");
            $stmt->execute([$userId, $deck]);
        } else {
            $stmt = $db->prepare("SELECT f.*, s.name AS subject_name FROM flashcards f LEFT JOIN subjects s ON f.subject_id=s.id WHERE f.user_id=? ORDER BY f.created_at DESC LIMIT 50");
            $stmt->execute([$userId]);
        }
        return $stmt->fetchAll();
    }

    public static function getDecks(int $userId): array
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT deck_name, COUNT(*) as cnt FROM flashcards WHERE user_id=? GROUP BY deck_name ORDER BY deck_name");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function getCount(int $userId): int
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM flashcards WHERE user_id=?");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public static function create(int $userId, array $data): bool
    {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO flashcards (user_id, subject_id, deck_name, question, answer) VALUES (?,?,?,?,?)");
        return $stmt->execute([
            $userId,
            $data['subject_id'] ?: null,
            clean($data['deck_name'] ?: 'General'),
            clean($data['question']),
            clean($data['answer'])
        ]);
    }

    public static function delete(int $id, int $userId): bool
    {
        $db = getDB();
        return $db->prepare("DELETE FROM flashcards WHERE id=? AND user_id=?")->execute([$id, $userId]);
    }

    public static function generateAI(int $userId, string $topic, int $count, ?int $subjectId = null, ?string $deckName = null): bool
    {
        $result = callAI(
            "Generate exactly $count flashcard question-answer pairs for the topic: \"$topic\" for a Software Engineering university student. Return ONLY a JSON array with objects having \"question\" and \"answer\" keys. No extra text.",
            "You are a flashcard generator. Return only valid JSON array."
        );

        if (!$result['success']) return false;

        $json = $result['text'];
        if (preg_match('/\[.*\]/s', $json, $matches)) {
            $cards = json_decode($matches[0], true);
            if ($cards) {
                $db = getDB();
                $stmt = $db->prepare("INSERT INTO flashcards (user_id, subject_id, deck_name, question, answer, ai_generated) VALUES (?,?,?,?,?,1)");
                foreach ($cards as $card) {
                    if (isset($card['question']) && isset($card['answer'])) {
                        $stmt->execute([
                            $userId,
                            $subjectId ?: null,
                            clean($deckName ?: $topic),
                            $card['question'],
                            $card['answer']
                        ]);
                    }
                }
                return true;
            }
        }
        return false;
    }
}
