<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/helpers.php';

use function App\getDB;

$studentId = '213-134-021';
$db = getDB();

$stmt = $db->prepare("SELECT id, name, student_id, password FROM users WHERE student_id = ?");
$stmt->execute([$studentId]);
$user = $stmt->fetch();

if ($user) {
    echo "User found in database!\n";
    echo "ID: " . $user['id'] . "\n";
    echo "Name: " . $user['name'] . "\n";
    echo "Student ID: " . $user['student_id'] . "\n";
    echo "Hashed Password: " . $user['password'] . "\n";
    
    $password = '213-134-021';
    if (password_verify($password, $user['password'])) {
        echo "Password verification successful!\n";
    } else {
        echo "Password verification failed.\n";
        // Let's try to re-hash and update for testing if it's wrong
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$newHash, $user['id']]);
        echo "Password reset to student ID for testing.\n";
    }
} else {
    echo "User NOT found in database.\n";
}
