<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập và giỏ hàng
if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    echo '<script>window.location.href = "cart.php";</script>';
    exit();
}

$pageTitle = "Thanh toán - Soudemy";

// Include file config và Database class
require_once 'config/config.php';

// Kết nối database
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Lỗi kết nối database: " . $e->getMessage());
}
// Lấy thông tin user từ database
$user = null;
if ($db) {
    // Lấy thông tin user
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

$total = isset($_GET['total']) ? floatval($_GET['total']) : 0;

// Nếu total là 0, tính từ cart
if ($total <= 0 && isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
}

// XỬ LÝ FORM THANH TOÁN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $payment_method = $_POST['payment_method'] ?? 'cod';
    $selected_card_id = $_POST['selected_card'] ?? null;
    $discount_amount = floatval($_POST['discount_amount'] ?? 0);
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($phone) || empty($address)) {
        die('Vui lòng điền đầy đủ thông tin thanh toán');
    }
    
    try {
        // Tính toán tổng tiền sau discount
        $final_total = $total - $discount_amount;
        
        // Tạo số đơn hàng
        $order_number = 'ORD-' . date('Ymd') . '-' . uniqid();
        
        // Lưu đơn hàng vào database
        $query = "INSERT INTO orders (user_id, total_amount, shipping_address, payment_method, status) 
                  VALUES (?, ?, ?, ?, 'completed')";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $_SESSION['user_id'], 
            $final_total, 
            "Tên: $name\nEmail: $email\nĐiện thoại: $phone\nĐịa chỉ: $address", 
            $payment_method
        ]);
        
        $order_id = $db->lastInsertId();
        
        // Lưu chi tiết đơn hàng
        foreach ($_SESSION['cart'] as $productId => $item) {
            $query = "INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity) 
                      VALUES (?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([
                $order_id, 
                $productId, 
                $item['name'], 
                $item['price'], 
                $item['quantity']
            ]);
        }
        
        // Lưu thông tin đơn hàng vào session
        $_SESSION['last_order'] = [
            'order_number' => $order_number,
            'order_id' => $order_id,
            'total' => $final_total,
            'original_total' => $total,
            'discount_amount' => $discount_amount,
            'payment_method' => $payment_method,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'status' => 'success'
        ];
        
        // Xóa giỏ hàng sau khi đặt hàng thành công
        unset($_SESSION['cart']);
        
        // Chuyển hướng tới trang shop
        header('Location: shop.php');
        exit();
        
    } catch(PDOException $e) {
        $_SESSION['error_message'] = 'Lỗi xử lý đơn hàng: ' . $e->getMessage();
        exit();
    }
}


include 'includes/header.php';
?>

<!-- PHẦN HTML GIỮ NGUYÊN NHƯ TRƯỚC -->
<section class="checkout-section">
    <div class="container">
        <h1 class="page-title">Thanh toán</h1>
        <div class="checkout-layout">
            <div class="checkout-form">
                <h3>Thông tin thanh toán</h3>
                <form method="POST" id="paymentForm">
                    <div class="form-group">
                        <label>Họ và tên *</label>
                        <input type="text" name="name" required value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại *</label>
                        <input type="tel" name="phone" required value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Địa chỉ giao hàng *</label>
                        <textarea name="address" required placeholder="Nhập địa chỉ đầy đủ..."><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Phương thức thanh toán *</label>
                        <select name="payment_method" id="paymentMethod" required>
                            <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                            <option value="card">Thanh toán thẻ</option>
                        </select>
                    </div>

                    <!-- Thông báo khi chọn thẻ -->
                    <div id="cardWarning" style="display: none;">
                        <div class="warning-message">
                            <i class="fa fa-exclamation-circle"></i>
                            <p>Không thể áp dụng trả thẻ</p>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" id="submitBtn">
                        Hoàn tất đơn hàng
                    </button>
                </form>
            </div>
            
            <div class="order-summary">
                <div class="order-header">
                    <h3><i class="fa fa-shopping-bag"></i> Tóm tắt đơn hàng</h3>
                </div>
                
                <div class="order-items-container">
                    <?php 
                    $itemCount = 0;
                    foreach ($_SESSION['cart'] as $productId => $item): 
                        $itemCount++;
                    ?>
                    <div class="order-item-card">
                        <div class="item-info">
                            <div class="item-header">
                                <h4 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h4>
                                <span class="item-qty-badge"><?php echo $item['quantity']; ?>x</span>
                            </div>
                            <div class="item-details">
                                <span class="item-unit-price">$<?php echo number_format($item['price'], 2); ?>/cái</span>
                                <span class="item-total-price">= $<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-divider"></div>

                <!-- Mã giảm giá -->
                <div class="coupon-promo-section">
                    <h4><i class="fa fa-ticket"></i> Mã khuyến mãi</h4>
                    <div class="coupon-input-group">
                        <input type="text" id="couponCode" placeholder="Nhập mã khuyến mãi của bạn..." class="coupon-input-field">
                        <button type="button" id="applyCouponBtn" class="coupon-apply-btn">
                            <i class="fa fa-check"></i> Áp dụng
                        </button>
                    </div>
                    <div id="couponMessage" class="coupon-message"></div>
                </div>

                <div class="order-divider"></div>

                <!-- Tính toán chi phí -->
                <div class="order-calculation">
                    <div class="calc-row">
                        <span class="calc-label">Tổng sản phẩm:</span>
                        <span class="calc-value">$<span id="subtotalAmount"><?php echo number_format($total, 2); ?></span></span>
                    </div>
                    <div class="calc-row" id="discountRow" style="display: none;">
                        <span class="calc-label discount-label">
                            <i class="fa fa-percent"></i> Khuyến mãi:
                        </span>
                        <span class="calc-value discount-value" id="discountAmount">-$0.00</span>
                    </div>
                    <div class="calc-row shipping-row">
                        <span class="calc-label">Phí vận chuyển:</span>
                        <span class="calc-value free-shipping"><i class="fa fa-truck"></i> Miễn phí</span>
                    </div>
                </div>

                <div class="order-divider-bold"></div>

                <!-- Tổng tiền -->
                <div class="order-total">
                    <span class="total-label">Thành tiền:</span>
                    <span class="total-amount">$<span id="finalTotal"><?php echo number_format($total, 2); ?></span></span>
                </div>

                <!-- Hidden fields để lưu thông tin coupon -->
                <input type="hidden" name="applied_coupon" id="appliedCoupon">
                <input type="hidden" name="discount_amount" id="discountAmountField" value="0">
            </div>
        </div>
    </div>
</section>

<!-- PHẦN CSS VÀ JAVASCRIPT -->
<style>
.checkout-section {
    padding: 40px 0;
}

.checkout-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
}

.checkout-form, .order-summary {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-group input, 
.form-group textarea, 
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}

.form-group textarea {
    height: 80px;
    resize: vertical;
}

.cards-selection {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
}

.card-option {
    display: flex;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #eee;
}

.card-option:last-child {
    border-bottom: none;
}

.card-option label {
    margin-left: 10px;
    cursor: pointer;
    flex: 1;
}

.card-option label span {
    display: block;
    font-size: 12px;
    color: #666;
}

.add-card-link, .no-cards a {
    color: #007bff;
    text-decoration: none;
    font-size: 14px;
}

.add-card-link:hover, .no-cards a:hover {
    text-decoration: underline;
}

.no-cards {
    text-align: center;
    color: #666;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 4px;
}

/* Order Summary Styles */
.order-summary {
    background: linear-gradient(135deg, #f5f7fa 0%, #ffffff 100%);
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid #e8ecf1;
}

.order-header {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #000;
}

.order-header h3 {
    color: #000;
    font-size: 18px;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.order-header i {
    color: #000;
    font-size: 20px;
}

/* Order Items Container */
.order-items-container {
    margin-bottom: 20px;
    max-height: 300px;
    overflow-y: auto;
    padding: 0 5px;
}

.order-items-container::-webkit-scrollbar {
    width: 6px;
}

.order-items-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.order-items-container::-webkit-scrollbar-thumb {
    background: #007bff;
    border-radius: 10px;
}

.order-items-container::-webkit-scrollbar-thumb:hover {
    background: #0056b3;
}

/* Order Item Card */
.order-item-card {
    background: white;
    padding: 12px;
    margin-bottom: 10px;
    border-radius: 6px;
    border: 1px solid #e8ecf1;
    transition: all 0.3s ease;
}

.order-item-card:hover {
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
    border-color: #007bff;
    transform: translateX(5px);
}

.item-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.item-name {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    color: #000;
    flex: 1;
    word-wrap: break-word;
}

.item-qty-badge {
    background: #007bff;
    color: white;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    white-space: nowrap;
}

.item-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
}

.item-unit-price {
    color: #000;
}

.item-total-price {
    font-weight: 600;
    color: #000;
}

/* Divider */
.order-divider {
    height: 1px;
    background: #e8ecf1;
    margin: 15px 0;
}

.order-divider-bold {
    height: 2px;
    background: #333;
    margin: 15px 0;
}

/* Coupon Section */
.coupon-promo-section {
    margin-bottom: 15px;
}

.coupon-promo-section h4 {
    font-size: 14px;
    font-weight: 600;
    color: #000;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.coupon-promo-section i {
    color: #ff9800;
    font-size: 16px;
}

.coupon-input-group {
    display: flex;
    gap: 8px;
}

.coupon-input-field {
    flex: 1;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 13px;
    transition: border-color 0.3s ease;
}

.coupon-input-field:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.coupon-apply-btn {
    padding: 10px 15px;
    background: #ff9800;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
}

.coupon-apply-btn:hover {
    background: #f08000;
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(255, 152, 0, 0.3);
}

.coupon-apply-btn:active {
    transform: translateY(0);
}

.coupon-message {
    font-size: 13px;
    margin-top: 8px;
    font-weight: 500;
}

/* Order Calculation */
.order-calculation {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 6px;
    margin: 15px 0;
}

.calc-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    font-size: 14px;
}

.calc-row:not(:last-child) {
    border-bottom: 1px solid #e8ecf1;
}

.calc-label {
    color: #000;
    font-weight: 500;
}

.calc-value {
    color: #000;
    font-weight: 600;
}

.discount-label {
    color: #28a745;
}

.discount-value {
    color: #28a745;
    font-weight: 700;
}

.shipping-row {
    border: none;
}

.free-shipping {
    color: #28a745;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* Order Total */
.order-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    background: linear-gradient(135deg, rgba(0, 123, 255, 0.05), rgba(255, 152, 0, 0.05));
    border-radius: 6px;
    padding: 15px;
}

.total-label {
    font-size: 16px;
    font-weight: 700;
    color: #000;
}

.total-amount {
    font-size: 22px;
    font-weight: 800;
    color: #000;
}

.payment-method-display {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.payment-badge {
    background: #28a745;
    color: white;
    padding: 10px 15px;
    border-radius: 4px;
    font-weight: 600;
    text-align: center;
    font-size: 14px;
}

.payment-badge i {
    margin-right: 8px;
}

/* Warning Message Styles */
.warning-message {
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 4px;
    padding: 15px;
    margin-top: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: slideDown 0.3s ease-out;
}

.warning-message i {
    color: #ffc107;
    font-size: 24px;
}

.warning-message p {
    color: #856404;
    margin: 0;
    font-weight: 600;
}

/* Success Notification Styles */
.success-notification {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.success-content {
    background: white;
    padding: 50px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.5s ease-out;
    max-width: 400px;
    width: 90%;
}

@keyframes slideUp {
    from {
        transform: translateY(50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.success-icon {
    font-size: 60px;
    color: #28a745;
    font-weight: bold;
    margin-bottom: 15px;
    animation: zoomIn 0.6s ease-out;
}

@keyframes zoomIn {
    from {
        transform: scale(0);
    }
    to {
        transform: scale(1);
    }
}

.success-content h2 {
    color: #28a745;
    font-size: 24px;
    margin: 15px 0;
    font-weight: bold;
}

.success-content p {
    color: #666;
    font-size: 16px;
    margin-bottom: 20px;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #28a745;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 20px auto 0;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('paymentForm');
    const paymentMethod = document.getElementById('paymentMethod');
    const cardWarning = document.getElementById('cardWarning');
    
    // Xử lý thay đổi phương thức thanh toán
    paymentMethod.addEventListener('change', function() {
        if (this.value === 'card') {
            // Hiển thị thông báo cảnh báo
            cardWarning.style.display = 'block';
            
            // Sau 2 giây tự động quay lại COD
            setTimeout(() => {
                this.value = 'cod';
                cardWarning.style.display = 'none';
            }, 2000);
        } else {
            cardWarning.style.display = 'none';
        }
    });
    
    // Xử lý submit form
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const name = document.querySelector('input[name="name"]').value.trim();
        const email = document.querySelector('input[name="email"]').value.trim();
        const phone = document.querySelector('input[name="phone"]').value.trim();
        const address = document.querySelector('textarea[name="address"]').value.trim();
        
        // Validate fields
        if (!name || !email || !phone || !address) {
            alert('Vui lòng điền đầy đủ thông tin thanh toán');
            return;
        }
        
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang xử lý...';
        submitBtn.disabled = true;
        
        // Hiển thị thông báo thành công
        setTimeout(() => {
            // Tạo thông báo
            const successMessage = document.createElement('div');
            successMessage.className = 'success-notification';
            successMessage.innerHTML = `
                <div class="success-content">
                    <div class="success-icon">✓</div>
                    <h2>Bạn đã thanh toán thành công!</h2>
                    <p>Đơn hàng của bạn đang được xử lý...</p>
                    <div class="spinner"></div>
                </div>
            `;
            document.body.appendChild(successMessage);
            
            // Submit form sau 2 giây, rồi chuyển hướng đến shop
            setTimeout(() => {
                // Gửi form để lưu dữ liệu vào database
                fetch(form.action || '', {
                    method: 'POST',
                    body: new FormData(form)
                }).then(() => {
                    // Chuyển hướng đến trang shop
                    window.location.href = 'shop.php';
                }).catch(() => {
                    // Nếu có lỗi, vẫn chuyển hướng đến shop
                    window.location.href = 'shop.php';
                });
            }, 2000);
        }, 1500);
    });
});

// Coupon functionality
document.getElementById('applyCouponBtn')?.addEventListener('click', function() {
    const couponCode = document.getElementById('couponCode').value;
    const orderAmount = <?php echo $total; ?>;
    
    if (!couponCode) {
        document.getElementById('couponMessage').innerHTML = 
            '<span style="color: red;">Vui lòng nhập mã giảm giá</span>';
        return;
    }
    
    fetch('api/coupons.php?action=validate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `code=${encodeURIComponent(couponCode)}&order_amount=${orderAmount}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.valid) {
            applyDiscount(data.discount, couponCode);
            document.getElementById('couponMessage').innerHTML = 
                '<span style="color: green;">Áp dụng mã giảm giá thành công! Giảm: $' + data.discount.toFixed(2) + '</span>';
        } else {
            document.getElementById('couponMessage').innerHTML = 
                '<span style="color: red;">' + data.message + '</span>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('couponMessage').innerHTML = 
            '<span style="color: red;">Lỗi khi áp dụng mã giảm giá</span>';
    });
});

function applyDiscount(discount, couponCode) {
    const currentTotal = <?php echo $total; ?>;
    const newTotal = currentTotal - discount;
    
    // Update display
    document.getElementById('finalTotal').textContent = newTotal.toFixed(2);
    document.getElementById('discountAmount').textContent = '-$' + discount.toFixed(2);
    document.getElementById('discountRow').style.display = 'flex';
    
    // Save to hidden fields
    document.getElementById('appliedCoupon').value = couponCode;
    document.getElementById('discountAmountField').value = discount;
    
    // Update total for form submission
    document.querySelector('input[name="total"]').value = newTotal;
}
</script>

<?php 
include 'includes/footer.php';
ob_end_flush();
?>