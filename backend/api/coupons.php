<?php
require_once __DIR__ . '/../../frontend/config/config.php';
require_once __DIR__ . '/../../frontend/config/database.php';
header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        createCoupon($db);
        break;
    case 'get':
        getCoupon($db);
        break;
    case 'update':
        updateCoupon($db);
        break;
    case 'delete':
        deleteCoupon($db);
        break;
    case 'validate':
        validateCoupon($db);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function createCoupon($db) {
    $data = $_POST;
    
    // Validation
    if (empty($data['code']) || empty($data['discount_value']) || empty($data['start_date']) || empty($data['end_date'])) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }
    
    // Check if coupon code already exists
    $check_stmt = $db->prepare("SELECT id FROM coupons WHERE code = ?");
    $check_stmt->execute([$data['code']]);
    if ($check_stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Coupon code already exists']);
        return;
    }
    
    $sql = "INSERT INTO coupons (code, discount_type, discount_value, min_order_amount, max_discount_amount, usage_limit, start_date, end_date, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([
        $data['code'],
        $data['discount_type'],
        $data['discount_value'],
        $data['min_order_amount'] ?: 0,
        $data['max_discount_amount'] ?: null,
        $data['usage_limit'] ?: null,
        $data['start_date'],
        $data['end_date'],
        isset($data['is_active']) ? 1 : 0
    ]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Coupon created successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create coupon']);
    }
}

function getCoupon($db) {
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Coupon ID is required']);
        return;
    }
    
    $sql = "SELECT * FROM coupons WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);
    $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($coupon) {
        echo json_encode(['success' => true, 'coupon' => $coupon]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Coupon not found']);
    }
}

function updateCoupon($db) {
    $data = $_POST;
    $id = $data['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Coupon ID is required']);
        return;
    }
    
    $sql = "UPDATE coupons SET 
            code = ?, discount_type = ?, discount_value = ?, min_order_amount = ?, 
            max_discount_amount = ?, usage_limit = ?, start_date = ?, end_date = ?, is_active = ?
            WHERE id = ?";
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([
        $data['code'],
        $data['discount_type'],
        $data['discount_value'],
        $data['min_order_amount'] ?: 0,
        $data['max_discount_amount'] ?: null,
        $data['usage_limit'] ?: null,
        $data['start_date'],
        $data['end_date'],
        isset($data['is_active']) ? 1 : 0,
        $id
    ]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Coupon updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update coupon']);
    }
}

function deleteCoupon($db) {
    $id = $_POST['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Coupon ID is required']);
        return;
    }
    
    $sql = "DELETE FROM coupons WHERE id = ?";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([$id]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Coupon deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete coupon']);
    }
}

function validateCoupon($db) {
    $code = $_POST['code'] ?? '';
    $order_amount = $_POST['order_amount'] ?? 0;
    
    if (empty($code)) {
        echo json_encode(['valid' => false, 'message' => 'Coupon code is required']);
        return;
    }
    
    $sql = "SELECT * FROM coupons WHERE code = ? AND is_active = 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([$code]);
    $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$coupon) {
        echo json_encode(['valid' => false, 'message' => 'Invalid coupon code']);
        return;
    }
    
    // Check validity period
    $now = date('Y-m-d H:i:s');
    if ($now < $coupon['start_date'] || $now > $coupon['end_date']) {
        echo json_encode(['valid' => false, 'message' => 'Coupon has expired']);
        return;
    }
    
    // Check usage limit
    if ($coupon['usage_limit'] && $coupon['used_count'] >= $coupon['usage_limit']) {
        echo json_encode(['valid' => false, 'message' => 'Coupon usage limit reached']);
        return;
    }
    
    // Check minimum order amount
    if ($order_amount < $coupon['min_order_amount']) {
        echo json_encode(['valid' => false, 'message' => 'Minimum order amount not met']);
        return;
    }
    
    // Calculate discount
    $discount = 0;
    if ($coupon['discount_type'] == 'percentage') {
        $discount = $order_amount * ($coupon['discount_value'] / 100);
        if ($coupon['max_discount_amount'] && $discount > $coupon['max_discount_amount']) {
            $discount = $coupon['max_discount_amount'];
        }
    } else {
        $discount = $coupon['discount_value'];
    }
    
    echo json_encode([
        'valid' => true,
        'discount' => $discount,
        'discount_type' => $coupon['discount_type'],
        'discount_value' => $coupon['discount_value']
    ]);
}
?>

