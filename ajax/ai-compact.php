<?php
// ajax/ai-compact.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

$body    = json_decode(file_get_contents('php://input'), true);
$question = trim($body['question'] ?? '');
$answers  = trim($body['answers'] ?? '');

if (!$question) {
    echo json_encode(['text' => 'No question provided.']);
    exit();
}

$prompt = "You are an exam preparation assistant for Metropolitan University Sylhet, Software Engineering department.

Question: $question

" . ($answers ? "Available answers from students:\n$answers\n\n" : "") . "

Write a COMPACT, exam-ready answer in maximum 10 lines.
- Include key definitions, steps, or formulas
- Use bullet points for clarity
- Focus only on what an examiner wants to see
- Do not repeat yourself
Start directly with the answer content.";

$result = callAI($prompt);
echo json_encode(['text' => $result['text']]);

