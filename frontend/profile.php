<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pageTitle = "Hồ sơ - Soudemy";
include 'includes/header.php';

// Kết nối database và lấy thông tin user
$database = new Database();
$db = $database->getConnection();

// Lấy thông tin user từ database
$user = null;
if ($db) {
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$user) {
    echo "Không tìm thấy thông tin người dùng!";
    exit;
}

// Get user orders
$query = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get available coupons
$query = "SELECT * FROM coupons WHERE expires_at >= CURDATE() AND (usage_limit = 0 OR used_count < usage_limit)";
$stmt = $db->prepare($query);
$stmt->execute();
$coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

$active_tab = $_GET['tab'] ?? 'profile';
?>

<section class="profile-section">
    <div class="container">
        <h1 class="page-title">Hồ sơ của tôi</h1>
        
        <div class="profile-layout">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="user-card">
                    <div class="user-avatar">
                        <?php 
                        // Kiểm tra và hiển thị avatar đúng cách
                        if (!empty($user['avatar']) && file_exists('../' . $user['avatar'])): 
                        ?>
                       <?php if (!empty($user['avatar']) && file_exists('../' . $user['avatar'])): ?>
    <img src="../<?php echo htmlspecialchars($user['avatar']); ?>" 
         alt="<?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?>"
         onerror="this.parentElement.querySelector('.avatar-placeholder').style.display='flex'; this.remove();">
<?php endif; ?>
<div class="avatar-placeholder" <?php if (!empty($user['avatar']) && file_exists('../' . $user['avatar'])) echo 'style="display:none;"'; ?>>
    <i class="fa fa-user"></i>
</div>

                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <i class="fa fa-user"></i>
                            </div>
                        <?php endif; ?>
                        
                        <form id="avatarForm" enctype="multipart/form-data" style="display: none;">
                            <input type="file" name="avatar" id="avatarInput" accept="image/*">
                        </form>
                        <button class="change-avatar-btn" onclick="document.getElementById('avatarInput').click()">Đổi avatar</button>
                    </div>
                    <h3><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></h3>
                    
                </div>
                
                <nav class="profile-nav">
                    <a href="?tab=profile" class="<?php echo $active_tab == 'profile' ? 'active' : ''; ?>">
                        <i class="fa fa-user"></i> Thông tin cá nhân
                    </a>
                    <a href="?tab=orders" class="<?php echo $active_tab == 'orders' ? 'active' : ''; ?>">
                        <i class="fa fa-shopping-bag"></i> Đơn hàng của tôi
                    </a>
                    <a href="?tab=address" class="<?php echo $active_tab == 'address' ? 'active' : ''; ?>">
                        <i class="fa fa-map-marker"></i> Địa chỉ giao hàng
                    </a>
                    <a href="?tab=payment" class="<?php echo $active_tab == 'payment' ? 'active' : ''; ?>">
                        <i class="fa fa-credit-card"></i> Phương thức thanh toán
                    </a>
                    <a href="?tab=coupons" class="<?php echo $active_tab == 'coupons' ? 'active' : ''; ?>">
                        <i class="fa fa-tag"></i> Mã giảm giá
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="profile-content">
                <!-- Tab Thông tin cá nhân -->
                <?php if ($active_tab == 'profile'): ?>
                    <div class="tab-pane active">
                        <h3>Thông tin cá nhân</h3>
                        
                        <!-- Hiển thị thông báo -->
                        <div id="profileMessage" style="display: none; margin-bottom: 20px; padding: 10px; border-radius: 4px;"></div>
                        
                        <form id="profileForm" class="profile-form" method="POST">
                            <div class="form-group">
                                <label>Họ và tên</label>
                                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                <small>Email không thể thay đổi</small>
                            </div>
                            <div class="form-group">
                                <label>Số điện thoại</label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Địa chỉ</label>
                                <textarea name="address" rows="3" placeholder="Nhập địa chỉ của bạn"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Cập nhật thông tin</button>
                            </div>
                        </form>
                        
                        <div class="danger-zone">
                            <h4>Vùng nguy hiểm</h4>
                            <p>Xóa tài khoản của bạn vĩnh viễn</p>
                            <button class="btn btn-danger" id="deleteAccountBtn">XÓA TÀI KHOẢN</button>
                        </div>
                    </div>
                
                <!-- Tab Đơn hàng -->
                <?php elseif ($active_tab == 'orders'): ?>
                    <div class="tab-pane active">
                        <h3>Đơn hàng của tôi</h3>
                        <?php if (empty($orders)): ?>
                            <div class="empty-state">
                                <i class="fa fa-shopping-bag"></i>
                                <p>Bạn chưa có đơn hàng nào</p>
                                <a href="shop.php" class="btn btn-primary">Mua sắm ngay</a>
                            </div>
                        <?php else: ?>
                            <div class="orders-list">
                                <?php foreach ($orders as $order): ?>
                                <div class="order-card">
                                    <div class="order-header">
                                        <div class="order-info">
                                            <strong>Mã đơn hàng: #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                                            <span class="order-date"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                                        </div>
                                        <div class="order-status">
                                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                                <?php 
                                                $status_text = [
                                                    'pending' => 'Chờ xử lý',
                                                    'processing' => 'Đang xử lý',
                                                    'shipped' => 'Đang giao',
                                                    'completed' => 'Hoàn thành',
                                                    'cancelled' => 'Đã hủy'
                                                ];
                                                echo $status_text[$order['status']] ?? $order['status'];
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="order-footer">
                                        <div class="order-total">
                                            Tổng tiền: <strong>$<?php echo number_format($order['total_amount'], 2); ?></strong>
                                        </div>
                                        <a href="order_details.php?order_id=<?php echo $order['id']; ?>" class="btn btn-outline">Xem chi tiết</a>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                
                <!-- Tab Địa chỉ -->
                <?php elseif ($active_tab == 'address'): ?>
                    <div class="tab-pane active">
                        <div class="tab-header">
                            <h3>Địa chỉ giao hàng</h3>
                            <button class="btn btn-primary" id="addAddressBtn">Thêm địa chỉ mới</button>
                        </div>
                        
                        <!-- Hiển thị thông báo -->
                        <div id="addressMessage" style="display: none; margin-bottom: 20px; padding: 10px; border-radius: 4px;"></div>
                        
                        <?php if (empty($user['address'])): ?>
                            <div class="empty-state">
                                <i class="fa fa-map-marker"></i>
                                <p>Bạn chưa có địa chỉ nào</p>
                                <button class="btn btn-primary" id="addFirstAddressBtn">Thêm địa chỉ đầu tiên</button>
                            </div>
                        <?php else: ?>
                            <div class="addresses-list">
                                <div class="address-card default">
                                    <div class="address-content">
                                        <h4><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></h4>
                                        <p><?php echo htmlspecialchars($user['phone'] ?: 'Chưa có số điện thoại'); ?></p>
                                        <p><?php echo nl2br(htmlspecialchars($user['address'])); ?></p>
                                    </div>
                                    <div class="address-actions">
                                        <span class="default-badge">Địa chỉ mặc định</span>
                                        <button type="button" class="btn-edit-address" onclick="showAddressForm()">Sửa</button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Form thêm/sửa địa chỉ -->
                        <div id="addressForm" style="display: <?php echo empty($user['address']) ? 'block' : 'none'; ?>; margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                            <h4><?php echo empty($user['address']) ? 'Thêm địa chỉ mới' : 'Sửa địa chỉ'; ?></h4>
                            <form id="addressUpdateForm">
                                <div class="form-group">
                                    <label>Họ và tên</label>
                                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Số điện thoại</label>
                                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Địa chỉ</label>
                                    <textarea name="address" rows="3" placeholder="Nhập địa chỉ đầy đủ" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary"><?php echo empty($user['address']) ? 'Thêm địa chỉ' : 'Cập nhật địa chỉ'; ?></button>
                                    <?php if (!empty($user['address'])): ?>
                                        <button type="button" class="btn btn-outline" onclick="hideAddressForm()">Hủy</button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                
                <!-- Tab Phương thức thanh toán -->
                <?php elseif ($active_tab == 'payment'): ?>
                    <div class="tab-pane active">
                        <div class="tab-header">
                            <h3>Phương thức thanh toán</h3>
                            <button class="btn btn-primary" id="addPaymentBtn">Thêm phương thức thanh toán</button>
                        </div>
                        
                        <!-- Hiển thị thông báo -->
                        <div id="paymentMessage" style="display: none; margin-bottom: 20px; padding: 10px; border-radius: 4px;"></div>
                        
                        <?php if (empty($user['payment_method']) && empty($user['bank_card_info'])): ?>
                            <div class="empty-state">
                                <i class="fa fa-credit-card"></i>
                                <p>Bạn chưa có phương thức thanh toán nào</p>
                                <button class="btn btn-primary" id="addFirstPaymentBtn">Thêm phương thức thanh toán</button>
                            </div>
                        <?php else: ?>
                            <div class="payment-methods-list">
                                <div class="payment-card default">
                                    <div class="payment-icon">
                                        <i class="fa fa-credit-card"></i>
                                    </div>
                                    <div class="payment-info">
                                        <h4><?php echo htmlspecialchars($user['payment_method'] ?: 'Thanh toán khi nhận hàng'); ?></h4>
                                        <p>Phương thức thanh toán mặc định</p>
                                        <?php if (!empty($user['bank_card_info'])): ?>
                                            <p>Thông tin: <?php echo htmlspecialchars($user['bank_card_info']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="payment-actions">
                                        <span class="default-badge">Mặc định</span>
                                        <button type="button" class="btn-edit-payment" onclick="showPaymentForm()">Sửa</button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Form thêm/sửa phương thức thanh toán -->
                        <div id="paymentForm" style="display: <?php echo (empty($user['payment_method']) && empty($user['bank_card_info'])) ? 'block' : 'none'; ?>; margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                            <h4><?php echo (empty($user['payment_method']) && empty($user['bank_card_info'])) ? 'Thêm phương thức thanh toán' : 'Sửa phương thức thanh toán'; ?></h4>
                            <form id="paymentUpdateForm">
                                <div class="form-group">
                                    <label>Phương thức thanh toán</label>
                                    <select name="payment_method" required>
                                        <option value="">Chọn phương thức</option>
                                        <option value="credit_card" <?php echo ($user['payment_method'] == 'credit_card') ? 'selected' : ''; ?>>Thẻ tín dụng</option>
                                        <option value="debit_card" <?php echo ($user['payment_method'] == 'debit_card') ? 'selected' : ''; ?>>Thẻ ghi nợ</option>
                                        <option value="paypal" <?php echo ($user['payment_method'] == 'paypal') ? 'selected' : ''; ?>>PayPal</option>
                                        <option value="momo" <?php echo ($user['payment_method'] == 'momo') ? 'selected' : ''; ?>>MoMo</option>
                                        <option value="cash" <?php echo (empty($user['payment_method']) || $user['payment_method'] == 'cash') ? 'selected' : ''; ?>>Tiền mặt</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Thông tin thẻ/ví (nếu có)</label>
                                    <input type="text" name="bank_card_info" value="<?php echo htmlspecialchars($user['bank_card_info'] ?? ''); ?>" placeholder="Số thẻ, số ví hoặc thông tin thanh toán">
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary"><?php echo (empty($user['payment_method']) && empty($user['bank_card_info'])) ? 'Thêm phương thức' : 'Cập nhật phương thức'; ?></button>
                                    <?php if (!empty($user['payment_method']) || !empty($user['bank_card_info'])): ?>
                                        <button type="button" class="btn btn-outline" onclick="hidePaymentForm()">Hủy</button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                
                <!-- Tab Mã giảm giá -->
                <?php elseif ($active_tab == 'coupons'): ?>
                    <div class="tab-pane active">
                        <h3>Mã giảm giá của tôi</h3>
                        
                        <?php if (empty($coupons)): ?>
                            <div class="empty-state">
                                <i class="fa fa-tag"></i>
                                <p>Hiện không có mã giảm giá khả dụng</p>
                                <p class="small-text">Mã giảm giá mới sẽ được cập nhật thường xuyên</p>
                            </div>
                        <?php else: ?>
                            <div class="coupons-list">
                                <?php foreach ($coupons as $coupon): ?>
                                <div class="coupon-card">
                                    <div class="coupon-header">
                                        <h4 class="coupon-code"><?php echo htmlspecialchars($coupon['code']); ?></h4>
                                        <span class="coupon-discount">Giảm <?php echo $coupon['discount']; ?>%</span>
                                    </div>
                                    <div class="coupon-body">
                                        <p>Áp dụng cho đơn hàng tối thiểu $<?php echo number_format($coupon['min_order'], 2); ?></p>
                                        <p class="coupon-expiry">Hết hạn: <?php echo date('d/m/Y', strtotime($coupon['expires_at'])); ?></p>
                                        <?php if ($coupon['usage_limit'] > 0): ?>
                                            <p class="coupon-usage">Đã dùng: <?php echo $coupon['used_count']; ?>/<?php echo $coupon['usage_limit']; ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="coupon-footer">
                                        <button class="btn-copy-coupon" data-code="<?php echo htmlspecialchars($coupon['code']); ?>">Sao chép mã</button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
/* CSS styles giữ nguyên từ phiên bản trước */
.profile-section {
    padding: 40px 0;
    min-height: 80vh;
}

.page-title {
    text-align: center;
    margin-bottom: 30px;
    color: #333;
}

.profile-layout {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
}

.user-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    text-align: center;
    border: 1px solid #e0e0e0;
}

.user-avatar {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    width: 100%;
}

.user-avatar img, .avatar-placeholder {
    width: 200px !important; /* Tăng kích thước đáng kể */
    height: 200px !important; /* Tăng kích thước đáng kể */
    border-radius: 50%;
    object-fit: cover;
    margin: 0 auto 15px;
    border: 5px solid #f8f9fa;
    box-shadow: 0 4px 25px rgba(0,0,0,0.1);
    display: block;
}

.avatar-placeholder {
    background: linear-gradient(135deg, #6c757d, #495057);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 80px;
    color: #e9ecef;
}

.change-avatar-btn {
    background: #6c757d;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 20px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-block;
    margin-top: 10px;
}

.user-card {
    background: white;
    padding: 40px 30px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    text-align: center;
    border: 1px solid #e9ecef;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 400px;
}

.user-info {
    text-align: center;
    width: 100%;
    margin-top: 15px;
}

.user-card h3 {
    margin: 0 0 8px 0;
    color: #2c3e50;
    font-size: 22px;
    font-weight: 600;
    word-break: break-word;
    line-height: 1.3;
}

.user-card p {
    color: #7f8c8d;
    margin: 0;
    font-size: 15px;
    font-weight: 400;
    word-break: break-word;
    line-height: 1.4;
}

/* Đảm bảo avatar hiển thị đúng kích thước */
.user-avatar img {
    max-width: 100%;
    height: auto;
}

/* Responsive */
@media (max-width: 768px) {
    .user-avatar img, .avatar-placeholder {
        width: 160px !important;
        height: 160px !important;
        font-size: 60px;
    }
    
    .user-card {
        padding: 30px 20px;
        min-height: 350px;
    }
    
    .user-card h3 {
        font-size: 20px;
    }
}

@media (max-width: 480px) {
    .user-avatar img, .avatar-placeholder {
        width: 140px !important;
        height: 140px !important;
        font-size: 50px;
    }
    
    .user-card {
        padding: 25px 15px;
        min-height: 320px;
    }
    
    .user-card h3 {
        font-size: 18px;
    }
    
    .user-card p {
        font-size: 14px;
    }
}
.user-card {
    display: grid !important;
    grid-template-rows: auto 3fr !important;
    gap: 20px !important;
}

.user-avatar {
    grid-row: 2 !important;
}

.user-info {
    grid-row: 3 !important;
    margin-top: 1 !important;
    align-self: start !important;
}

</style>

<script>
// Hiển thị thông báo
function showMessage(elementId, message, type) {
    const element = document.getElementById(elementId);
    element.textContent = message;
    element.style.display = 'block';
    element.style.background = type === 'success' ? '#d4edda' : '#f8d7da';
    element.style.color = type === 'success' ? '#155724' : '#721c24';
    element.style.border = type === 'success' ? '1px solid #c3e6cb' : '1px solid #f5c6cb';
    
    setTimeout(() => {
        element.style.display = 'none';
    }, 5000);
}

// Xử lý upload avatar
document.getElementById('avatarInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        console.log('File selected:', file);
        
        // Kiểm tra loại file
        if (!file.type.startsWith('image/')) {
            showMessage('profileMessage', 'Vui lòng chọn file ảnh!', 'error');
            return;
        }
        
        // Kiểm tra kích thước
        if (file.size > 5 * 1024 * 1024) {
            showMessage('profileMessage', 'File quá lớn! Tối đa 5MB.', 'error');
            return;
        }
        
        // Hiển thị loading
        const changeBtn = document.querySelector('.change-avatar-btn');
        const originalText = changeBtn.textContent;
        changeBtn.textContent = 'Đang upload...';
        changeBtn.disabled = true;
        
        const formData = new FormData();
        formData.append('avatar', file);
        
        console.log('Sending request to update_avatar.php');
        
        fetch('update_avatar.php', {
            method: 'POST',
            body: formData,
            // Thêm headers để debug
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Response status:', response.status, response.statusText);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                showMessage('profileMessage', data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showMessage('profileMessage', 'Lỗi: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            showMessage('profileMessage', 'Lỗi: ' + error.message, 'error');
        })
        .finally(() => {
            changeBtn.textContent = originalText;
            changeBtn.disabled = false;
            document.getElementById('avatarInput').value = '';
        });
    }
});

// Xử lý form thông tin cá nhân
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    // Hiển thị loading
    submitBtn.textContent = 'Đang cập nhật...';
    submitBtn.disabled = true;
    
    fetch('update_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('profileMessage', data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showMessage('profileMessage', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('profileMessage', 'Đã xảy ra lỗi kết nối', 'error');
    })
    .finally(() => {
        // Khôi phục nút
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
});

// Xử lý form địa chỉ
document.getElementById('addressUpdateForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.textContent = 'Đang cập nhật...';
    submitBtn.disabled = true;
    
    fetch('update_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('addressMessage', data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showMessage('addressMessage', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('addressMessage', 'Đã xảy ra lỗi kết nối', 'error');
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
});

// Xử lý form phương thức thanh toán
document.getElementById('paymentUpdateForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.textContent = 'Đang cập nhật...';
    submitBtn.disabled = true;
    
    fetch('update_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('paymentMessage', data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showMessage('paymentMessage', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('paymentMessage', 'Đã xảy ra lỗi kết nối', 'error');
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
});

// Xử lý sao chép mã giảm giá
document.querySelectorAll('.btn-copy-coupon').forEach(button => {
    button.addEventListener('click', function() {
        const code = this.getAttribute('data-code');
        navigator.clipboard.writeText(code).then(() => {
            const originalText = this.textContent;
            this.textContent = 'Đã sao chép!';
            this.style.background = '#28a745';
            
            setTimeout(() => {
                this.textContent = originalText;
                this.style.background = '';
            }, 2000);
        });
    });
});

// Xử lý xóa tài khoản
document.getElementById('deleteAccountBtn')?.addEventListener('click', function() {
    if (confirm('Bạn có chắc chắn muốn xóa tài khoản? Hành động này không thể hoàn tác!')) {
        alert('Tính năng xóa tài khoản sẽ được triển khai sau!');
    }
});
</script>

<?php include 'includes/footer.php'; ?>