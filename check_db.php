<?php
require 'config/database.php';
require 'app/helpers.php';
$db = getDB();
try {
    $res = $db->query('SELECT id, is_approved, submitted_by FROM questions LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
    print_r($res);
} catch (Exception $e) {
    echo $e->getMessage();
}
