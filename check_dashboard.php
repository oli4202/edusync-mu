<?php
require 'edusync/app/bootstrap.php';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SESSION['user_id'] = 1;
try {
    $controller = new App\Controllers\DashboardController();
    ob_start();
    $controller->index();
    $output = ob_get_clean();
    echo "SUCCESS\n";
} catch (\Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() . "\n";
}
