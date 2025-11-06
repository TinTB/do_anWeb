<?php
// Load site config nếu có
$configFile = __DIR__ . '/config.php';
if (file_exists($configFile)) {
    require_once $configFile;
}

// Central DB config used bởi frontend và backend
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'Soudemy_Demo'); // ensure matches soudemy_demo.sql
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

// Simple Database class
if (!class_exists('Database')) {
    class Database {
        public function getConnection() {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                return $pdo;
            } catch (PDOException $e) {
                error_log("Database connection error: " . $e->getMessage());
                return null;
            }
        }
    }
}
?>