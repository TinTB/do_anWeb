<?php
session_start();
require_once 'config/config.php';

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit();
}

// Kết nối database
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Xử lý dữ liệu từ form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        // Xác định loại cập nhật
        if (isset($_POST['full_name'])) {
            // Cập nhật thông tin cá nhân
            $full_name = trim($_POST['full_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            
            $query = "UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$full_name, $phone, $address, $user_id]);
            
            $response['success'] = true;
            $response['message'] = 'Cập nhật thông tin thành công!';
            
        } elseif (isset($_POST['payment_method'])) {
            // Cập nhật phương thức thanh toán
            $payment_method = trim($_POST['payment_method'] ?? '');
            $bank_card_info = trim($_POST['bank_card_info'] ?? '');
            
            $query = "UPDATE users SET payment_method = ?, bank_card_info = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$payment_method, $bank_card_info, $user_id]);
            
            $response['success'] = true;
            $response['message'] = 'Cập nhật phương thức thanh toán thành công!';
        }
        
    } catch(PDOException $e) {
        $response['message'] = 'Lỗi database: ' . $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Nếu không phải POST request
header('HTTP/1.1 400 Bad Request');
echo json_encode(['success' => false, 'message' => 'Request không hợp lệ']);
?>