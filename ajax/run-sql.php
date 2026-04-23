<?php
// LEGACY FILE - REDIRECT TO MVC API
header('Location: /api/playground/run-sql');
exit;

require_once __DIR__ . '/../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$code = trim($body['code'] ?? '');

if (!$code) {
    echo json_encode(['success' => false, 'error' => 'No SQL code provided.']);
    exit();
}

$db = getDB();
$results = [];

// Divide by semicolon but try to be smart about it (basic version)
// Note: In a production environment, you'd want a more robust SQL parser.
$statements = array_filter(array_map('trim', explode(';', $code)));

try {
    foreach ($statements as $stmtText) {
        if (empty($stmtText)) continue;
        
        $stmt = $db->prepare($stmtText);
        $stmt->execute();
        
        $isQuery = stripos($stmtText, 'SELECT') === 0 || stripos($stmtText, 'SHOW') === 0 || stripos($stmtText, 'DESCRIBE') === 0 || stripos($stmtText, 'EXPLAIN') === 0;
        
        if ($isQuery) {
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $columns = [];
            if (!empty($rows)) {
                $columns = array_keys($rows[0]);
            }
            $results[] = [
                'type' => 'table',
                'stmt' => substr($stmtText, 0, 100),
                'columns' => $columns,
                'rows' => $rows,
                'rowCount' => count($rows)
            ];
        } else {
            $results[] = [
                'type' => 'status',
                'stmt' => substr($stmtText, 0, 100),
                'affectedRows' => $stmt->rowCount(),
                'message' => 'Success'
            ];
        }
    }
    echo json_encode(['success' => true, 'results' => $results]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'error' => 'SQL Error: ' . $e->getMessage(),
        'stmt' => $stmtText ?? 'unknown'
    ]);
}
