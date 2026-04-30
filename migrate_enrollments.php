<?php
/**
 * Migration: Create student_course_enrollments table
 */

// Load environment
require_once __DIR__ . '/config/database.php';

// Get database connection via helper
require_once __DIR__ . '/app/helpers.php';

try {
    $db = getDB();

    $sql = "
    CREATE TABLE IF NOT EXISTS student_course_enrollments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        course_id INT NOT NULL,
        batch VARCHAR(20) NOT NULL,
        semester INT NOT NULL,
        enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_enrollment (user_id, course_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    );
    ";

    $db->exec($sql);
    echo "✓ Table 'student_course_enrollments' created successfully!\n";
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
