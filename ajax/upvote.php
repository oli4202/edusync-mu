<?php
// ajax/upvote.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

$body     = json_decode(file_get_contents('php://input'), true);
$answerId = (int)($body['answer_id'] ?? 0);
$db       = getDB();

if (!$answerId) { echo json_encode(['error' => 'Invalid']); exit(); }

$db->prepare("UPDATE answers SET upvotes = upvotes + 1 WHERE id=?")->execute([$answerId]);
$stmt = $db->prepare("SELECT upvotes FROM answers WHERE id=?");
$stmt->execute([$answerId]);
$row = $stmt->fetch();
echo json_encode(['upvotes' => $row['upvotes']]);
