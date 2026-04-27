<?php
require __DIR__ . '/vendor/autoload.php';

try {
    $db = \App\getDB();
    $db->exec("ALTER TABLE users MODIFY COLUMN role ENUM('student','faculty','admin') DEFAULT 'student'");
    echo "Successfully updated users table role ENUM.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
