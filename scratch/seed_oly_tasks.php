<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers.php';

$db = getDB();

$userStmt = $db->prepare("SELECT id, name FROM users WHERE name LIKE ? LIMIT 1");
$userStmt->execute(['%Olyur Rob Oly%']);
$user = $userStmt->fetch();

if (!$user) {
    echo "User 'Olyur Rob Oly' not found.\n";
    exit(1);
}

$subjectsStmt = $db->prepare("
    SELECT id, name, code
    FROM subjects
    WHERE user_id = ?
    ORDER BY id ASC
    LIMIT 6
");
$subjectsStmt->execute([(int)$user['id']]);
$subjects = $subjectsStmt->fetchAll();

if (empty($subjects)) {
    echo "No subjects found for user {$user['name']} (ID {$user['id']}).\n";
    exit(1);
}

$taskTemplates = [
    ['Assignment 1 draft', 'Prepare first draft and notes', '+2 days', 'pending'],
    ['Revision checklist', 'Revise key topics and short notes', '+4 days', 'pending'],
    ['Practice problems', 'Solve at least 10 practice problems', '+1 days', 'todo'],
    ['Lab report submission', 'Finalize and submit lab report', '-1 days', 'done'],
    ['Class test preparation', 'Prepare probable questions', '+3 days', 'in_progress'],
    ['Previous question solve', 'Solve last year question set', '-2 days', 'done'],
];

$insert = $db->prepare("
    INSERT INTO tasks (user_id, subject_id, title, description, due_date, priority, status)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$added = 0;
foreach ($subjects as $index => $subject) {
    $tpl = $taskTemplates[$index % count($taskTemplates)];
    $title = ($subject['code'] ? $subject['code'] . ' - ' : '') . $tpl[0];
    $desc = $tpl[1] . " ({$subject['name']})";
    $dueDate = date('Y-m-d', strtotime($tpl[2]));
    $status = $tpl[3];

    $insert->execute([
        (int)$user['id'],
        (int)$subject['id'],
        $title,
        $desc,
        $dueDate,
        'normal',
        $status,
    ]);
    $added++;
}

echo "Added {$added} tasks for {$user['name']} (ID {$user['id']}).\n";
