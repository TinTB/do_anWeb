<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$product_id = $input['product_id'];
$quantity = $input['quantity'] ?? 1;

$database = new Database();
$db = $database->getConnection();

// Check if product exists
$query = "SELECT * FROM products WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$product_id]);

if ($stmt->rowCount() == 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// Check if product already in cart
$query = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id'], $product_id]);

if ($stmt->rowCount() > 0) {
    // Update quantity
    $query = "UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$quantity, $_SESSION['user_id'], $product_id]);
} else {
    // Add new item
    $query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
}

// Get updated cart count
$query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

$_SESSION['cart_count'] = $result['total'] ?? 0;

echo json_encode(['success' => true, 'cart_count' => $_SESSION['cart_count']]);
?>