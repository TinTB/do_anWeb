<?php
session_start();

// Use frontend config/database from backend
require_once __DIR__ . '/../frontend/config/config.php';
require_once __DIR__ . '/../frontend/config/database.php';

header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Check user role
$query = "SELECT role FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Check if it's a delete request (id provided and is a delete action)
        if (isset($_POST['action']) && $_POST['action'] === 'delete') {
            deleteProduct($db);
        } elseif (isset($_POST['id']) && !empty($_POST['id'])) {
            updateProduct($db);
        } else {
            addProduct($db);
        }
        break;
    case 'DELETE':
        deleteProduct($db);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

function addProduct($db) {
    // Validate required fields
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    
    // Validation
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Tên sản phẩm bắt buộc']);
        exit;
    }
    if (empty($category)) {
        echo json_encode(['success' => false, 'message' => 'Danh mục bắt buộc']);
        exit;
    }
    if ($price <= 0) {
        echo json_encode(['success' => false, 'message' => 'Giá phải lớn hơn 0']);
        exit;
    }
    if ($stock < 0) {
        echo json_encode(['success' => false, 'message' => 'Tồn kho không được âm']);
        exit;
    }
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Hình ảnh bắt buộc']);
        exit;
    }
    
    // Handle image upload
    $file_tmp = $_FILES['image']['tmp_name'];
    $file_name = $_FILES['image']['name'];
    $file_size = $_FILES['image']['size'];
    $file_type = mime_content_type($file_tmp);
    
    // Validate image
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file_type, $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Định dạng không hỗ trợ. Chỉ JPG, PNG, GIF, WebP']);
        exit;
    }
    
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file_size > $max_size) {
        echo json_encode(['success' => false, 'message' => 'Hình ảnh không được vượt quá 5MB']);
        exit;
    }
    
    // Create upload directory if it doesn't exist
    $upload_dir = __DIR__ . '/../frontend/images/' . $category;
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
    $filename = uniqid('product_') . '.' . $file_extension;
    $target_path = $upload_dir . '/' . $filename;
    
    if (!move_uploaded_file($file_tmp, $target_path)) {
        echo json_encode(['success' => false, 'message' => 'Lỗi tải hình ảnh']);
        exit;
    }
    
    $image_path = 'images/' . $category . '/' . $filename;
    
    try {
        $query = "INSERT INTO products (name, category, price, stock, description, image, rating) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        
        $rating = 5; // Default rating for new products
        
        if ($stmt->execute([$name, $category, $price, $stock, $description, $image_path, $rating])) {
            $product_id = $db->lastInsertId();
            echo json_encode([
                'success' => true, 
                'message' => 'Sản phẩm đã được thêm thành công!',
                'product_id' => $product_id
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Thêm sản phẩm thất bại']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi database: ' . $e->getMessage()]);
    }
}

function updateProduct($db) {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    
    // Validation
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ']);
        exit;
    }
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Tên sản phẩm bắt buộc']);
        exit;
    }
    
    try {
        $query = "UPDATE products SET name = ?, category = ?, price = ?, stock = ?, description = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$name, $category, $price, $stock, $description, $id])) {
            echo json_encode(['success' => true, 'message' => 'Sản phẩm đã được cập nhật!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi database: ' . $e->getMessage()]);
    }
}

function deleteProduct($db) {
    $id = intval($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ']);
        exit;
    }
    
    try {
        // Get product image before deleting
        $query = "SELECT image FROM products WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete product
        $query = "DELETE FROM products WHERE id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$id])) {
            echo json_encode(['success' => true, 'message' => 'Sản phẩm đã được xóa!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Xóa thất bại']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi database: ' . $e->getMessage()]);
    }
}
?>