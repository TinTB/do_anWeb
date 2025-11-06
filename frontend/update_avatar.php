<?php
session_start();
require_once 'config/config.php';

// Debug: Ghi log lỗi
error_log("Upload avatar started");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Kiểm tra có file upload không
if (!isset($_FILES['avatar'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Không có file được upload']);
    exit();
}

$file = $_FILES['avatar'];

// Kiểm tra lỗi upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    $error_messages = [
        UPLOAD_ERR_INI_SIZE => 'File vượt quá kích thước cho phép',
        UPLOAD_ERR_FORM_SIZE => 'File quá lớn',
        UPLOAD_ERR_PARTIAL => 'File chỉ upload được một phần',
        UPLOAD_ERR_NO_FILE => 'Không có file được upload',
        UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm',
        UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file',
        UPLOAD_ERR_EXTENSION => 'Upload bị dừng bởi extension'
    ];
    
    $message = $error_messages[$file['error']] ?? 'Lỗi upload không xác định';
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

// Kiểm tra loại file
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowed_types)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh JPEG, PNG, GIF, WebP']);
    exit();
}

// Kiểm tra kích thước (5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File quá lớn (tối đa 5MB)']);
    exit();
}

// Tạo thư mục upload
$upload_dir = '../uploads/avatars/';
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Không thể tạo thư mục upload']);
        exit();
    }
}

// Tạo tên file mới
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$new_filename = 'avatar_' . $user_id . '_' . time() . '.' . $file_extension;
$file_path = $upload_dir . $new_filename;

// Di chuyển file
if (move_uploaded_file($file['tmp_name'], $file_path)) {
    try {
        // Kết nối database
        $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Đường dẫn lưu trong database
        $db_path = 'uploads/avatars/' . $new_filename;
        
        // Cập nhật database
        $query = "UPDATE users SET avatar = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$db_path, $user_id]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Upload avatar thành công!',
            'avatar_path' => $db_path
        ]);
        
    } catch(PDOException $e) {
        // Xóa file nếu lỗi database
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi database: ' . $e->getMessage()]);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Không thể lưu file']);
}
?>