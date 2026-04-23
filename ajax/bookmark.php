<?php
// LEGACY FILE - REDIRECT TO MVC API
header('Location: /api/question-bank/bookmark');
exit;

require_once __DIR__ . '/../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

$body       = json_decode(file_get_contents('php://input'), true);
$questionId = (int)($body['question_id'] ?? 0);
$db         = getDB();

if (!$questionId) { echo json_encode(['error' => 'Invalid']); exit(); }

$stmt = $db->prepare("SELECT id FROM question_bookmarks WHERE user_id=? AND question_id=?");
$stmt->execute([$_SESSION['user_id'], $questionId]);
$existing = $stmt->fetch();

if ($existing) {
    $db->prepare("DELETE FROM question_bookmarks WHERE user_id=? AND question_id=?")->execute([$_SESSION['user_id'], $questionId]);
    echo json_encode(['bookmarked' => false]);
} else {
    $db->prepare("INSERT INTO question_bookmarks (user_id, question_id) VALUES (?,?)")->execute([$_SESSION['user_id'], $questionId]);
    echo json_encode(['bookmarked' => true]);
}
