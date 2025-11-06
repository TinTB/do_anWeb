<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        $db->beginTransaction();
        
        // Get cart items
        $query = "SELECT p.*, c.quantity, c.id as cart_id 
                  FROM cart c 
                  JOIN products p ON c.product_id = p.id 
                  WHERE c.user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$_SESSION['user_id']]);
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($cart_items)) {
            throw new Exception('Cart is empty');
        }
        
        // Calculate total
        $total = 0;
        foreach ($cart_items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        // Apply discount
        $discount = 0;
        if (isset($_SESSION['applied_coupon'])) {
            $discount = $total * ($_SESSION['applied_coupon']['discount'] / 100);
            $coupon_id = $_SESSION['applied_coupon']['id'];
        }
        $final_total = $total - $discount;
        
        // Create order
        $query = "INSERT INTO orders (user_id, total_amount, shipping_address, payment_method, status) 
                  VALUES (?, ?, ?, ?, 'pending')";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $_SESSION['user_id'],
            $final_total,
            $_POST['shipping_address'],
            $_POST['payment_method']
        ]);
        
        $order_id = $db->lastInsertId();
        
        // Add order items
        $query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        
        foreach ($cart_items as $item) {
            $stmt->execute([
                $order_id,
                $item['id'],
                $item['quantity'],
                $item['price']
            ]);
        }
        
        // Record coupon usage
        if (isset($coupon_id)) {
            $query = "INSERT INTO coupon_usage (coupon_id, user_id, order_id) VALUES (?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([$coupon_id, $_SESSION['user_id'], $order_id]);
            
            // Update coupon usage count
            $query = "UPDATE coupons SET used_count = used_count + 1 WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$coupon_id]);
        }
        
        // Clear cart
        $query = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$_SESSION['user_id']]);
        
        // Clear session cart count and coupon
        unset($_SESSION['cart_count']);
        unset($_SESSION['applied_coupon']);
        
        $db->commit();
        
        header("Location: order_success.php?order_id=" . $order_id);
        
    } catch (Exception $e) {
        $db->rollBack();
        header("Location: checkout.php?error=order_failed");
    }
}
?>