<?php
// ajax/run-python.php — Local Python execution for Code Playground
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$code = $body['code'] ?? '';
$stdin = $body['input'] ?? '';

if (!$code) {
    echo json_encode(['success' => false, 'error' => 'No code provided.']);
    exit();
}

// Create a temporary file for the code
$tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'edusync_playground';
if (!is_dir($tmpDir)) mkdir($tmpDir, 0777, true);

$fileId = uniqid('py_', true);
$pyFile = $tmpDir . DIRECTORY_SEPARATOR . $fileId . '.py';
$inputFile = $tmpDir . DIRECTORY_SEPARATOR . $fileId . '.txt';

file_put_contents($pyFile, $code);
file_put_contents($inputFile, $stdin);

// Run the python code
$command = 'python ' . escapeshellarg($pyFile) . ' < ' . escapeshellarg($inputFile) . ' 2>&1';
$output = [];
$returnCode = 0;

exec($command, $output, $returnCode);

// Cleanup
@unlink($pyFile);
@unlink($inputFile);

echo json_encode([
    'success' => true,
    'output' => implode("\n", $output),
    'returnCode' => $returnCode
]);
