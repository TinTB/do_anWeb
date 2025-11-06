<?php
session_start();
require_once __DIR__ . '/config/database.php';

function write_log($msg) {
    $logdir = __DIR__ . '/logs';
    if (!is_dir($logdir)) mkdir($logdir, 0755, true);
    file_put_contents($logdir . '/register_debug.log', date('[Y-m-d H:i:s] ') . $msg . PHP_EOL, FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$email      = trim($_POST['email'] ?? '');
$username   = trim($_POST['username'] ?? $email); // nếu không có username, dùng email
$password   = $_POST['password'] ?? '';
$confirm    = $_POST['confirm_password'] ?? '';
$full_name  = trim($_POST['full_name'] ?? '');
$phone      = trim($_POST['phone'] ?? '');

// Basic validation
if ($email === '' || $password === '') {
    $_SESSION['flash_error'] = 'Vui lòng điền đầy đủ trường bắt buộc.';
    header('Location: register.php');
    exit;
}

if ($password !== $confirm) {
    $_SESSION['flash_error'] = 'Mật khẩu và xác nhận mật khẩu không khớp.';
    header('Location: register.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash_error'] = 'Email không hợp lệ.';
    header('Location: register.php');
    exit;
}

$dbClass = new Database();
$db = $dbClass->getConnection();
if (!$db) {
    write_log("DB connect failed in register.");
    $_SESSION['flash_error'] = 'Lỗi kết nối cơ sở dữ liệu.';
    header('Location: register.php');
    exit;
}

try {
    // check duplicate (username OR email)
    $checkStmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
    $checkStmt->execute([$username, $email]);
    if ($checkStmt->fetch()) {
        $_SESSION['flash_error'] = 'Username hoặc Email đã tồn tại. Vui lòng dùng thông tin khác.';
        header('Location: register.php');
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $insertSql = "INSERT INTO users (username, email, password, full_name, phone, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $insertStmt = $db->prepare($insertSql);
    $ok = $insertStmt->execute([$username, $email, $hash, $full_name, $phone]);

    write_log("Register attempt for {$username}/{$email} result: " . ($ok ? 'OK' : 'FAILED') . " | lastError: " . json_encode($db->errorInfo()));

    if ($ok) {
        // KHÔNG tự động login — yêu cầu user đăng nhập thủ công
        $_SESSION['flash_success'] = 'Đăng ký thành công. Vui lòng đăng nhập bằng tài khoản vừa tạo.';
        header('Location: login.php');
        exit;
    } else {
        $_SESSION['flash_error'] = 'Đăng ký thất bại. Vui lòng thử lại.';
        header('Location: register.php');
        exit;
    }
} catch (Exception $e) {
    write_log("Exception in register: " . $e->getMessage());
    $_SESSION['flash_error'] = 'Lỗi server. Kiểm tra log.';
    header('Location: register.php');
    exit;
}
?>