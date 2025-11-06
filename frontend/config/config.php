<?php
// Site configuration
define('SITE_NAME', 'Soudemy_demo');
// Set SITE_URL to your local test URL (adjust port if bạn dùng khác)
define('SITE_URL', 'http://localhost:8080/Do_an/frontend');
define('UPLOAD_PATH', __DIR__ . '/../../uploads/');

// Database configuration
define('DB_HOST', 'localhost');
// Must match the database name in soudemy_demo.sql
define('DB_NAME', 'Soudemy_Demo');
define('DB_USER', 'root');
define('DB_PASS', '');

// Email configuration (leave as before)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');

// Other settings
define('ITEMS_PER_PAGE', 12);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
?>