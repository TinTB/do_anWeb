<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra xem có thông tin đơn hàng không
if (!isset($_SESSION['last_order'])) {
    header('Location: index.php');
    exit();
}

$pageTitle = "Đặt hàng thành công - Soudemy";

include 'includes/header.php';

// Lấy thông tin đơn hàng từ session
$order = $_SESSION['last_order'];

// Lấy thông tin với giá trị mặc định để tránh lỗi
$order_number = $order['order_number'] ?? 'Đang cập nhật';
$order_id = $order['order_id'] ?? '';
$total = $order['total'] ?? 0;
$payment_method = $order['payment_method'] ?? 'cod';
$name = $order['name'] ?? '';
$email = $order['email'] ?? '';
$phone = $order['phone'] ?? '';
$address = $order['address'] ?? '';

// Xóa thông tin đơn hàng khỏi session sau khi hiển thị
// unset($_SESSION['last_order']);
?>

<section class="order-success-section">
    <div class="container">
        <!-- Thông báo thanh toán thành công -->
        <div class="success-alert">
            <div class="alert-icon">✓</div>
            <h1>THANH TOÁN THÀNH CÔNG!</h1>
            <p class="alert-message">Đơn hàng của bạn đã được tiếp nhận và đang được xử lý.</p>
        </div>

        <div class="success-message">
            <div class="success-icon">
                <i class="fa fa-check-circle"></i>
            </div>
            <h2>Đặt hàng thành công!</h2>
            <p>Cảm ơn bạn đã đặt hàng. Chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất.</p>
        </div>

        <div class="order-details">
            <h3>Thông tin đơn hàng</h3>
            
            <?php if (isset($order['order_number'])): ?>
            <div class="detail-item">
                <strong>Mã đơn hàng:</strong>
                <span><?php echo htmlspecialchars($order_number); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (isset($order['order_id'])): ?>
            <div class="detail-item">
                <strong>ID đơn hàng:</strong>
                <span>#<?php echo htmlspecialchars($order_id); ?></span>
            </div>
            <?php endif; ?>

            <div class="detail-item">
                <strong>Tổng tiền:</strong>
                <span class="price-highlight">$<?php echo number_format($total, 2); ?></span>
            </div>

            <?php if (isset($order['discount_amount']) && $order['discount_amount'] > 0): ?>
            <div class="detail-item">
                <strong>Giảm giá:</strong>
                <span class="discount-highlight">-$<?php echo number_format($order['discount_amount'], 2); ?></span>
            </div>
            <?php endif; ?>

            <div class="detail-item">
                <strong>Phương thức thanh toán:</strong>
                <span class="payment-method">
                    <?php 
                    switch($payment_method) {
                        case 'cod': echo '💰 Thanh toán khi nhận hàng (COD)'; break;
                        case 'banking': echo '🏦 Chuyển khoản ngân hàng'; break;
                        case 'momo': echo '📱 Ví MoMo'; break;
                        default: echo htmlspecialchars($payment_method);
                    }
                    ?>
                </span>
            </div>

            <div class="detail-item">
                <strong>Trạng thái:</strong>
                <span class="status-success">✓ Hoàn tất</span>
            </div>

            <div class="customer-info">
                <h4>Thông tin khách hàng</h4>
                <div class="detail-item">
                    <strong>Họ tên:</strong>
                    <span><?php echo htmlspecialchars($name); ?></span>
                </div>
                
                <div class="detail-item">
                    <strong>Email:</strong>
                    <span><?php echo htmlspecialchars($email); ?></span>
                </div>
                
                <div class="detail-item">
                    <strong>Số điện thoại:</strong>
                    <span><?php echo htmlspecialchars($phone); ?></span>
                </div>
                
                <div class="detail-item">
                    <strong>Địa chỉ giao hàng:</strong>
                    <span><?php echo nl2br(htmlspecialchars($address)); ?></span>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="shop.php" class="btn btn-primary">Tiếp tục mua sắm</a>
            <a href="profile.php?tab=orders" class="btn btn-info">Xem đơn hàng</a>
            <a href="index.php" class="btn btn-secondary">Về trang chủ</a>
        </div>
    </div>
</section>

<style>
.order-success-section {
    padding: 40px 0;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
}

/* Thông báo thanh toán thành công */
.success-alert {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 30px;
    border-radius: 12px;
    text-align: center;
    margin-bottom: 30px;
    box-shadow: 0 5px 20px rgba(40, 167, 69, 0.3);
    animation: slideDown 0.5s ease-out;
}

.alert-icon {
    font-size: 50px;
    font-weight: bold;
    margin-bottom: 15px;
    animation: checkmark 0.6s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes checkmark {
    0% {
        transform: scale(0) rotate(-45deg);
        opacity: 0;
    }
    50% {
        transform: scale(1.2) rotate(10deg);
    }
    100% {
        transform: scale(1) rotate(0deg);
        opacity: 1;
    }
}

.success-alert h1 {
    font-size: 28px;
    margin: 10px 0;
    font-weight: bold;
    letter-spacing: 1px;
}

.alert-message {
    font-size: 16px;
    opacity: 0.95;
    margin: 10px 0 0 0;
}

.success-message {
    background: white;
    padding: 40px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.success-icon {
    font-size: 60px;
    color: #28a745;
    margin-bottom: 20px;
}

.success-icon i {
    animation: bounce 0.6s;
}

@keyframes bounce {
    0%, 20%, 60%, 100% {transform: translateY(0);}
    40% {transform: translateY(-10px);}
    80% {transform: translateY(-5px);}
}

.success-message h2 {
    color: #28a745;
    margin-bottom: 15px;
    text-align: center;
}

.success-message p {
    font-size: 16px;
    color: #666;
    text-align: center;
}

.order-details {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    text-align: left;
}

.order-details h3 {
    margin-bottom: 20px;
    text-align: center;
    color: #333;
    font-size: 20px;
    font-weight: bold;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #eee;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-item strong {
    color: #333;
    font-weight: 600;
    min-width: 150px;
}

.price-highlight {
    color: #28a745;
    font-weight: bold;
    font-size: 16px;
}

.discount-highlight {
    color: #dc3545;
    font-weight: bold;
}

.payment-method {
    color: #007bff;
    font-weight: 600;
}

.status-success {
    color: #28a745;
    font-weight: bold;
    background: #d4edda;
    padding: 5px 10px;
    border-radius: 4px;
}

.customer-info {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid #ddd;
}

.customer-info h4 {
    margin-bottom: 15px;
    color: #333;
    font-weight: bold;
}

.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 30px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    transition: all 0.3s;
    display: inline-block;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
}

.btn-info {
    background: #17a2b8;
    color: white;
}

.btn-info:hover {
    background: #138496;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(23, 162, 184, 0.3);
}

@media (max-width: 768px) {
    .success-alert {
        padding: 20px;
    }

    .success-alert h1 {
        font-size: 22px;
    }

    .order-details {
        padding: 20px;
    }

    .action-buttons {
        flex-direction: column;
        gap: 10px;
    }

    .btn {
        width: 100%;
        text-align: center;
    }
    
    .detail-item {
        flex-direction: column;
        gap: 5px;
    }

    .detail-item strong {
        min-width: auto;
    }
}
</style>

<?php 
include 'includes/footer.php';
ob_end_flush();
?>