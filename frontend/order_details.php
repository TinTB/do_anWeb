<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pageTitle = "Chi tiết đơn hàng - Soudemy";

require_once 'config/config.php';

// Kết nối database
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Lỗi kết nối database: " . $e->getMessage());
}

// Lấy order_id từ URL
$order_id = $_GET['order_id'] ?? 0;

// Lấy thông tin đơn hàng
$order = null;
$order_items = [];

if ($order_id) {
    // Lấy thông tin đơn hàng
    $query = "SELECT o.*, u.full_name, u.email, u.phone 
              FROM orders o 
              LEFT JOIN users u ON o.user_id = u.id 
              WHERE o.id = ? AND o.user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        // Lấy chi tiết đơn hàng
        $items_query = "SELECT * FROM order_items WHERE order_id = ?";
        $items_stmt = $db->prepare($items_query);
        $items_stmt->execute([$order_id]);
        $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Nếu không tìm thấy đơn hàng
if (!$order) {
    echo '<script>alert("Đơn hàng không tồn tại!"); window.location.href = "profile.php";</script>';
    exit();
}

include 'includes/header.php';
?>

<section class="order-details-section">
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Chi tiết đơn hàng</h1>
            <a href="profile.php" class="back-link">← Quay lại hồ sơ</a>
        </div>

        <div class="order-details-layout">
            <div class="order-info">
                <div class="info-card">
                    <h3>Thông tin đơn hàng</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Mã đơn hàng:</strong>
                            <span>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Ngày đặt:</strong>
                            <span><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Tổng tiền:</strong>
                            <span class="price">$<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Phương thức thanh toán:</strong>
                            <span>
                                <?php 
                                switch($order['payment_method']) {
                                    case 'cod': echo 'Thanh toán khi nhận hàng'; break;
                                    case 'banking': echo 'Chuyển khoản ngân hàng'; break;
                                    case 'momo': echo 'Ví MoMo'; break;
                                    default: echo htmlspecialchars($order['payment_method']);
                                }
                                ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <strong>Trạng thái:</strong>
                            <span class="status status-<?php echo $order['status']; ?>">
                                <?php 
                                switch($order['status']) {
                                    case 'pending': echo 'Đang chờ xử lý'; break;
                                    case 'processing': echo 'Đang xử lý'; break;
                                    case 'shipped': echo 'Đang giao hàng'; break;
                                    case 'delivered': echo 'Đã giao hàng'; break;
                                    case 'cancelled': echo 'Đã hủy'; break;
                                    default: echo htmlspecialchars($order['status']);
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <h3>Thông tin khách hàng</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Họ tên:</strong>
                            <span><?php echo htmlspecialchars($order['full_name'] ?? 'Nguyễn Thiên An'); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Email:</strong>
                            <span><?php echo htmlspecialchars($order['email'] ?? 'an.21714802010559@gmail.com'); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Số điện thoại:</strong>
                            <span><?php echo htmlspecialchars($order['phone'] ?? '0775829532'); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Địa chỉ giao hàng:</strong>
                            <span><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="order-items">
                <div class="items-card">
                    <h3>Sản phẩm đã đặt</h3>
                    <div class="items-list">
                        <?php foreach ($order_items as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <img src="images/products/<?php echo $item['product_id']; ?>.jpg" 
                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                     onerror="this.src='images/placeholder.jpg'">
                            </div>
                            <div class="item-details">
                                <h4 class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                <div class="item-price">$<?php echo number_format($item['product_price'], 2); ?></div>
                                <div class="item-quantity">Số lượng: <?php echo $item['quantity']; ?></div>
                            </div>
                            <div class="item-total">
                                $<?php echo number_format($item['product_price'] * $item['quantity'], 2); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($order_items)): ?>
                        <div class="no-items">
                            <p>Không có sản phẩm nào trong đơn hàng này.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="order-summary">
                        <div class="summary-item">
                            <strong>Tổng cộng:</strong>
                            <span class="total-amount">$<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.order-details-section {
    padding: 40px 0;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.back-link {
    color: #007bff;
    text-decoration: none;
}

.back-link:hover {
    text-decoration: underline;
}

.order-details-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.info-card, .items-card {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.info-card h3, .items-card h3 {
    margin-bottom: 20px;
    color: #333;
    border-bottom: 2px solid #007bff;
    padding-bottom: 10px;
}

.info-grid {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.info-item:last-child {
    border-bottom: none;
}

.price {
    color: #e74c3c;
    font-weight: bold;
    font-size: 18px;
}

.status {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-processing {
    background: #cce7ff;
    color: #004085;
}

.status-shipped {
    background: #d4edda;
    color: #155724;
}

.status-delivered {
    background: #d1ecf1;
    color: #0c5460;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.order-item {
    display: flex;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
    align-items: center;
}

.order-item:last-child {
    border-bottom: none;
}

.item-image {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-details {
    flex: 1;
}

.item-name {
    margin: 0 0 5px 0;
    font-size: 16px;
}

.item-price, .item-quantity {
    color: #666;
    font-size: 14px;
}

.item-total {
    font-weight: bold;
    color: #e74c3c;
}

.order-summary {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid #333;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 18px;
}

.total-amount {
    color: #e74c3c;
    font-weight: bold;
    font-size: 24px;
}

.no-items {
    text-align: center;
    padding: 40px;
    color: #666;
}

@media (max-width: 768px) {
    .order-details-layout {
        grid-template-columns: 1fr;
    }
    
    .page-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .order-item {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<?php 
include 'includes/footer.php';
ob_end_flush();
?>