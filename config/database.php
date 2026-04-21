<?php
// ============================================================
//  EduSync — Database Configuration (MVC version)
//  config/database.php
// ============================================================

/**
 * Get a PDO database connection using .env values.
 * Uses a static variable so only one connection is created per request.
 */
function getDB(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $host    = getenv('DB_HOST') ?: '127.0.0.1';
            $dbname  = getenv('DB_NAME') ?: 'edusync_mu';
            $user    = getenv('DB_USER') ?: 'root';
            $pass    = getenv('DB_PASS') ?: '';
            $charset = getenv('DB_CHARSET') ?: 'utf8mb4';

            $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}
