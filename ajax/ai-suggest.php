<?php
// ajax/ai-suggest.php — General AI prompt handler
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
header('Content-Type: application/json');


$body = json_decode(file_get_contents('php://input'), true);
$prompt = trim($body['prompt'] ?? '');


if (!$prompt) {
    echo json_encode(['text' => 'No prompt provided.']);
    exit();
}


$result = callAI($prompt, 'You are a helpful academic assistant for Metropolitan University Sylhet, Software Engineering department students.');
echo json_encode(['text' => $result['text']]);