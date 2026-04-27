<?php
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();
$lines = file(__DIR__ . '/students_data.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$currentBatch = null;
$insertedCount = 0;

$pendingId = null;

foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line)) continue;

    // Detect Batch
    if (preg_match('/SWE\s*-?\s*0?(\d+)/i', $line, $matches)) {
        $currentBatch = $matches[1];
        echo "Detected Batch: $currentBatch\n";
        $pendingId = null;
        continue;
    }

    if (strtolower($line) === 'id' || strtolower($line) === 'name' || strtolower($line) === 'student id') {
        continue;
    }

    $studentId = null;
    $name = null;

    // Check if line contains both ID and Name
    if (preg_match('/(?:^\d+\s+)?(\d{3}-\d{3}-\d{2,3})\s+(.+)$/i', $line, $matches)) {
        $studentId = trim($matches[1]);
        $name = trim($matches[2]);
        $pendingId = null; // reset
    } 
    // Check if line is just an ID
    elseif (preg_match('/^(?:\d+\s+)?(\d{3}-\d{3}-\d{2,3})$/i', $line, $matches)) {
        $pendingId = trim($matches[1]);
        continue;
    }
    // Check if we have a pending ID, then this line is probably the name
    elseif ($pendingId !== null) {
        $studentId = $pendingId;
        $name = $line;
        $pendingId = null;
    } else {
        continue; // Unrecognized format
    }

    if (!$currentBatch || !$studentId || !$name) {
        continue;
    }

    // Prepare email and password
    $email = strtolower($studentId) . '@edusync.mu';
    $password = password_hash('password123', PASSWORD_DEFAULT);

    try {
        // Insert or update
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, student_id, batch, role) 
            VALUES (?, ?, ?, ?, ?, 'student')
            ON DUPLICATE KEY UPDATE name = VALUES(name), batch = VALUES(batch)
        ");
        $stmt->execute([$name, $email, $password, $studentId, $currentBatch]);
        
        $insertedCount++;
    } catch (Exception $e) {
        echo "Error inserting $studentId: " . $e->getMessage() . "\n";
    }
}

echo "Done! Inserted/Updated $insertedCount students.\n";
