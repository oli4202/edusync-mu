<?php
// admin/ajax/ai-suggest.php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$prompt = $input['prompt'] ?? '';

if (!$prompt) {
    echo json_encode(['error' => 'No prompt provided', 'text' => 'Empty prompt sent.']);
    exit;
}

$result = callAI($prompt, 'You are a helpful academic assistant for Metropolitan University Sylhet, Software Engineering department students.');
echo json_encode(['text' => $result['text']]);