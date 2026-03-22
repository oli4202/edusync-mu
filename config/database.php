<?php
// ============================================================
//  EduSync — Database Configuration
//  config/database.php
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'edusync_mu');
define('DB_USER', 'root'); // Change to your MySQL username
define('DB_PASS', ''); // Change to your MySQL password
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'EduSync MU');
define('SITE_URL', 'http://localhost:8000');
// AI API Keys - Wrapped in defined() to avoid warnings if included multiple times
include __DIR__ . '/api-keys.php';
if (!defined('CLAUDE_API_KEY'))
    define('CLAUDE_API_KEY', $api_keys['CLAUDE_API_KEY']);
if (!defined('GEMINI_API_KEY'))
    define('GEMINI_API_KEY', $api_keys['GEMINI_API_KEY']);
if (!defined('HF_API_KEY'))
    define('HF_API_KEY', $api_keys['HF_API_KEY']);

function getDB()
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        }
        catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}
