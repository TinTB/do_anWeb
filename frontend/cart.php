<?php
// BẬT LỖI
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Giỏ hàng - Soudemy";
include 'includes/header.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo '<div class="container">
            <div class="alert alert-warning">
                <p>Vui lòng <a href="login.php" class="alert-link">đăng nhập</a> để xem giỏ hàng của bạn.</p>
            </div>
            <div class="text-center">
                <a href="shop.php" class="btn btn-primary">Tiếp tục mua sắm</a>
            </div>
          </div>';
    include 'includes/footer.php';
    exit();
}

// Lấy giỏ hàng từ SESSION
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total = 0;

foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<section class="cart-section">
    <div class="container">
        <h1 class="page-title">Giỏ hàng của bạn</h1>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <i class="fa fa-shopping-cart"></i>
                <p>Giỏ hàng của bạn đang trống</p>
                <a href="shop.php" class="btn btn-primary">Tiếp tục mua sắm</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <div class="cart-items">
                    <?php foreach ($cart_items as $productId => $item): ?>
                    <div class="cart-item" data-product-id="<?php echo $productId; ?>">
                        <div class="item-image">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 80px; height: 80px; object-fit: cover;">
                        </div>
                        <div class="item-details">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <div class="item-category"><?php echo htmlspecialchars($item['category']); ?></div>
                            <div class="item-price">$<?php echo number_format($item['price'], 2); ?></div>
                        </div>
                        <div class="item-quantity">
                            <button class="quantity-btn minus">-</button>
                            <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1" data-product-id="<?php echo $productId; ?>">
                            <button class="quantity-btn plus">+</button>
                        </div>
                        <div class="item-total">
                            $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                        <button class="remove-item" data-product-id="<?php echo $productId; ?>">
                            <i class="fa fa-trash"></i> Xóa
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <div class="summary-card">
                        <h3>Tổng đơn hàng</h3>
                        <div class="summary-row">
                            <span>Tạm tính:</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Phí vận chuyển:</span>
                            <span>$0.00</span>
                        </div>
                <div class="summary-row discount-row">
                    <div class="coupon-section">
                        <input type="text" id="couponCode" placeholder="Nhập mã giảm giá">
                        <button id="applyCoupon" class="btn btn-outline">Áp dụng</button>
                    </div>
                    <span id="discountAmount">-$0.00</span>
                </div>
                        <div class="summary-row total-row">
                            <span>Tổng cộng:</span>
                            <span id="finalTotal">$<?php echo number_format($total, 2); ?></span>
                        </div>
                        <button id="checkoutBtn" class="btn btn-primary btn-block">Thanh toán</button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- THÊM CSS TRỰC TIẾP -->
<style>
.cart-section {
    padding: 40px 0;
    min-height: 60vh;
}

.page-title {
    text-align: center;
    margin-bottom: 30px;
    color: #333;
}

.cart-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    align-items: start;
}

.cart-items {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.cart-item {
    display: grid;
    grid-template-columns: 80px 1fr auto auto auto;
    gap: 15px;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.cart-item:last-child {
    border-bottom: none;
}

.item-details h3 {
    margin: 0 0 5px 0;
    color: #333;
}

.item-category {
    color: #666;
    font-size: 14px;
}

.item-price {
    font-weight: bold;
    color: #000000;
}

.item-quantity {
    display: flex;
    align-items: center;
    gap: 5px;
}

.quantity-btn {
    background: #f8f9fa;
    border: 1px solid #ddd;
    padding: 5px 10px;
    cursor: pointer;
    border-radius: 4px;
}

.quantity-btn:hover {
    background: #e9ecef;
}

.quantity-input {
    width: 50px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 5px;
}

.item-total {
    font-weight: bold;
    color: #333;
}

.remove-item {
    background: #dc3545;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.remove-item:hover {
    background: #c82333;
}

.cart-summary {
    position: sticky;
    top: 20px;
}

.summary-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.summary-card h3 {
    margin-top: 0;
    color: #333;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.total-row {
    border-top: 2px solid #333;
    font-weight: bold;
    font-size: 18px;
}

.coupon-section {
    display: flex;
    gap: 10px;
}

#couponCode {
    flex: 1;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

.btn-outline {
    background: transparent;
    border: 1px solid #007bff;
    color: #007bff;
}

.btn-outline:hover {
    background: #007bff;
    color: white;
}

.btn-block {
    width: 100%;
    margin-top: 15px;
}

.empty-cart {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.empty-cart i {
    font-size: 48px;
    margin-bottom: 20px;
    color: #ddd;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.alert-warning {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
}

.alert-link {
    color: #856404;
    text-decoration: underline;
}

.text-center {
    text-align: center;
}
/* Thêm vào cuối phần CSS */
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.fa-spinner {
    margin-right: 5px;
}

.coupon-section {
    display: flex;
    gap: 10px;
    align-items: center;
}

#couponCode {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

#couponCode:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.discount-applied {
    background-color: #d4edda;
    border-color: #c3e6cb;
}

/* Hiệu ứng cho nút thanh toán */
#checkoutBtn {
    background: linear-gradient(135deg, #28a745, #20c997);
    border: none;
    padding: 12px;
    font-size: 16px;
    font-weight: bold;
    transition: all 0.3s ease;
}

#checkoutBtn:hover:not(:disabled) {
    background: linear-gradient(135deg, #218838, #1e7e34);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

#checkoutBtn:active {
    transform: translateY(0);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let discount = 0;
    const discountAmount = document.getElementById('discountAmount');
    const finalTotal = document.getElementById('finalTotal');
    const originalTotal = <?php echo $total; ?>;
    
    // Xử lý mã giảm giá
    document.getElementById('applyCoupon').addEventListener('click', applyCoupon);
    document.getElementById('couponCode').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyCoupon();
        }
    });

    // Xóa sản phẩm
    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            if (confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
                fetch('update_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'product_id=' + productId + '&action=remove'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            }
        });
    });

    // Cập nhật số lượng
    document.querySelectorAll('.quantity-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const item = this.closest('.cart-item');
            const productId = item.getAttribute('data-product-id');
            const input = item.querySelector('.quantity-input');
            let quantity = parseInt(input.value);
            
            if (this.classList.contains('minus') && quantity > 1) {
                quantity--;
            } else if (this.classList.contains('plus')) {
                quantity++;
            }
            
            input.value = quantity;
            
            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId + '&quantity=' + quantity + '&action=update'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        });
    });

    // Xử lý thanh toán
    document.getElementById('checkoutBtn').addEventListener('click', function() {
        checkout();
    });

    function applyCoupon() {
        const couponCode = document.getElementById('couponCode').value.trim();
        const couponSection = document.querySelector('.coupon-section');
        
        if (!couponCode) {
            alert('Vui lòng nhập mã giảm giá');
            return;
        }

        // Hiển thị loading
        const applyBtn = document.getElementById('applyCoupon');
        applyBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang áp dụng...';
        applyBtn.disabled = true;

        // Danh sách mã giảm giá mẫu
        const validCoupons = {
            'WELCOME10': 0.1,    // Giảm 10%
            'SAVE20': 0.2,       // Giảm 20%
            'FREESHIP': 0,       // Miễn phí ship (giảm $0 nhưng có thể xử lý riêng)
            'SALE50': 0.5        // Giảm 50%
        };

        // Giả lập API call
        setTimeout(() => {
            if (validCoupons.hasOwnProperty(couponCode.toUpperCase())) {
                const discountRate = validCoupons[couponCode.toUpperCase()];
                discount = originalTotal * discountRate;
                
                // Cập nhật hiển thị
                discountAmount.textContent = '-$' + discount.toFixed(2);
                discountAmount.style.color = '#28a745';
                discountAmount.style.fontWeight = 'bold';
                
                // Cập nhật tổng tiền
                updateFinalTotal();
                
                // Hiển thị thông báo thành công
                showMessage('Áp dụng mã giảm giá thành công!', 'success');
                
                // Thay đổi giao diện phần mã giảm giá
                couponSection.innerHTML = `
                    <span style="color: #28a745; font-weight: bold;">${couponCode.toUpperCase()}</span>
                    <button id="removeCoupon" class="btn btn-outline" style="margin-left: 10px;">Xóa</button>
                `;
                
                // Thêm sự kiện xóa mã
                document.getElementById('removeCoupon').addEventListener('click', removeCoupon);
                
            } else {
                showMessage('Mã giảm giá không hợp lệ hoặc đã hết hạn!', 'error');
                applyBtn.innerHTML = 'Áp dụng';
                applyBtn.disabled = false;
            }
        }, 1000);
    }

    function removeCoupon() {
        discount = 0;
        discountAmount.textContent = '-$0.00';
        discountAmount.style.color = '';
        discountAmount.style.fontWeight = '';
        
        updateFinalTotal();
        
        // Khôi phục lại ô nhập mã
        const couponSection = document.querySelector('.coupon-section');
        couponSection.innerHTML = `
            <input type="text" id="couponCode" placeholder="Nhập mã giảm giá">
            <button id="applyCoupon" class="btn btn-outline">Áp dụng</button>
        `;
        
        // Thêm lại sự kiện
        document.getElementById('applyCoupon').addEventListener('click', applyCoupon);
        document.getElementById('couponCode').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyCoupon();
            }
        });
        
        showMessage('Đã xóa mã giảm giá', 'info');
    }

    function updateFinalTotal() {
        const finalAmount = originalTotal - discount;
        finalTotal.textContent = '$' + finalAmount.toFixed(2);
        
        // Highlight tổng tiền mới
        finalTotal.style.color = '#e44d26';
        finalTotal.style.fontWeight = 'bold';
        finalTotal.style.fontSize = '1.2em';
    }

    function checkout() {
        if (<?php echo count($cart_items); ?> === 0) {
            alert('Giỏ hàng của bạn đang trống!');
            return;
        }

        const checkoutBtn = document.getElementById('checkoutBtn');
        checkoutBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang xử lý...';
        checkoutBtn.disabled = true;

        // Dữ liệu thanh toán
        const checkoutData = {
            total: originalTotal,
            discount: discount,
            final_total: originalTotal - discount,
            items: <?php echo json_encode($cart_items); ?>,
            coupon: document.getElementById('couponCode').value || null
        };

        // Giả lập xử lý thanh toán
        setTimeout(() => {
            // Chuyển hướng đến trang thanh toán
            window.location.href = 'checkout.php?total=' + (originalTotal - discount);
        }, 1500);
    }

    function showMessage(message, type) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `alert alert-${type} fixed-message`;
        messageDiv.textContent = message;
        messageDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            padding: 12px 20px;
            border-radius: 4px;
            color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#004085'};
            background-color: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#cce5ff'};
            border: 1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#b8daff'};
        `;
        
        document.body.appendChild(messageDiv);
        
        setTimeout(() => {
            messageDiv.remove();
        }, 3000);
    }
});
</script>
<style>
<style>
/* Đảm bảo giá màu đen */
.item-price,
.cart-item .item-price,
.cart-items .item-price,
[class*="price"],
.cart-section .item-price {
    color: #000000 !important;
    font-weight: 600 !important;
}

/* Nút xóa - nền đen chữ xám */
.remove-item {
    background: #000000 !important;
    color: #999999 !important;
    border: none;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.3s ease;
    border: 1px solid #000000 !important;
}

.remove-item:hover {
    background: #333333 !important;
    color: #ffffff !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.remove-item i {
    margin-right: 5px;
    color: inherit !important;
}
</style>
</style>
<?php include 'includes/footer.php'; ?>