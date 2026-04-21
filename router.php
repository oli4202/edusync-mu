<?php
/**
 * router.php — Built-in PHP web server router
 * Use: php -S localhost:8000 router.php
 */

$file = __DIR__ . $_SERVER['REQUEST_URI'];

if (is_file($file) && in_array(pathinfo($file, PATHINFO_EXTENSION), ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'ico', 'woff', 'woff2'])) {
    return false;
}

// Everything else goes to index.php
require_once __DIR__ . '/index.php';
