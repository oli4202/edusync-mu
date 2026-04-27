<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

$db = App\getDB();

echo "--- Courses Distribution ---\n";
$courses = $db->query("SELECT id, name, batch, semester FROM courses ORDER BY batch, semester")->fetchAll();
foreach ($courses as $c) {
    echo "ID: {$c['id']} | Name: {$c['name']} | Batch: {$c['batch']} | Sem: {$c['semester']}\n";
}

echo "\n--- Students Distribution ---\n";
$dist = $db->query("SELECT batch, semester, COUNT(*) as count FROM users WHERE role='student' GROUP BY batch, semester")->fetchAll();
foreach ($dist as $d) {
    echo "Batch: {$d['batch']} | Sem: {$d['semester']} | Students: {$d['count']}\n";
}
