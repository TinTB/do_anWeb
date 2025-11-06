<?php
// Configure session cookie so it's available site-wide and allow credentials
// Must be set BEFORE session_start()
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',               // ensure cookie available across the app
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => false,           // set true if using HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
// allow browser to accept Set-Cookie via CORS (if cross-origin requests exist)
header('Access-Control-Allow-Credentials: true');

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ!']);
    exit;
}

// Load central DB config
require_once __DIR__ . '/../config/database.php';

// Read JSON body first, fallback to $_POST
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() === JSON_ERROR_NONE && !empty($input)) {
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
} else {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
}

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin!']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    if (!$db) {
        echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database!']);
        exit;
    }

    $query = "SELECT * FROM users WHERE email = ? LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'] ?? $user['email'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'] ?? '';
        
        // cart count
        $cartQuery = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
        $cartStmt = $db->prepare($cartQuery);
        $cartStmt->execute([$user['id']]);
        $cartData = $cartStmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['cart_count'] = $cartData['total'] ?? 0;
        
        // return SITE_URL to caller (useful for AJAX redirect)
        $homeUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') . '/index.php' : '/Do_an/frontend/index.php';
        echo json_encode(['success' => true, 'message' => 'Đăng nhập thành công', 'redirect' => $homeUrl]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Email hoặc mật khẩu không chính xác!']);
    }
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi hệ thống!']);
}
?>