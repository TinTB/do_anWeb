<?php
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'update' && $productId > 0) {
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
        
        if (isset($_SESSION['cart'][$productId]) && $quantity > 0) {
            $_SESSION['cart'][$productId]['quantity'] = $quantity;
            $_SESSION['cart_count'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Product not found in cart']);
        }
        
    } elseif ($action === 'remove' && $productId > 0) {
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
            $_SESSION['cart_count'] = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Product not found in cart']);
        }
        
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>