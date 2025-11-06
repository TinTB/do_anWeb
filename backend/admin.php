<?php
ob_start();
session_start();

// Load shared frontend config + Database
require_once __DIR__ . '/../frontend/config/config.php';
require_once __DIR__ . '/../frontend/config/database.php';

// --- Initialize DB and current user/session safely ---
$database = new Database();
$db = $database->getConnection();

// Determine login state and load user
$isLoggedIn = false;
$user = null;
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $isLoggedIn = (bool)$user;
    } catch (Exception $e) {
        // DB error — treat as not logged in
        $isLoggedIn = false;
        $user = null;
    }
}

// Enforce admin-only access
if (!$isLoggedIn || ($user['role'] ?? '') !== 'admin') {
    // redirect to frontend login (use absolute path to avoid relative issues)
    header("Location: /Do_an/frontend/login.php");
    exit;
}

// If here, user is admin — include frontend header so nav uses session/user
include __DIR__ . '/../frontend/includes/header.php';

// Thêm nút quay lại trang chủ frontend (hiện rõ, nhưng style nhẹ để không phá layout)
echo '<div style="padding:12px 20px; background:transparent;">';
echo '<a href="/Do_an/frontend/index.php" class="btn btn-outline" style="display:inline-block; padding:8px 14px; border:1px solid #ccc; border-radius:6px; text-decoration:none; color:#333;">← Quay lại trang chủ</a>';
echo '</div>';

// --- ensure frontend CSS/JS load correctly when this page is served from /backend/ ---
echo '<!-- extra asset links to ensure admin UI loads correctly from backend -->' . PHP_EOL;
echo '<link rel="stylesheet" href="/Do_an/frontend/css/styles.css">' . PHP_EOL;
echo '<link rel="stylesheet" href="/Do_an/frontend/css/admin.css">' . PHP_EOL;
// expose base paths for frontend scripts so admin JS can build correct URLs
echo '<script>window.APP_BASE = "/Do_an/frontend/"; window.BACKEND_BASE = "/Do_an/backend/";</script>' . PHP_EOL;
// load the admin front-end script explicitly (absolute path)
echo '<script src="/Do_an/frontend/js/admin.js" defer></script>' . PHP_EOL;

$pageTitle = "Admin Panel - Soudemy";

// Check if user is admin
if (!$isLoggedIn || $user['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
$database = new Database();
$db = $database->getConnection();

// Get statistics
$users_count = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$products_count = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$orders_count = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_revenue = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'completed'")->fetchColumn();

// Get recent orders
$query = "SELECT o.*, u.full_name, u.email 
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          ORDER BY o.created_at DESC 
          LIMIT 5";
$recent_orders = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="admin-section">
    <div class="container">
        <h1 class="page-title">Admin Dashboard</h1>
        
        <!-- Statistics -->
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $users_count; ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa fa-cube"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $products_count; ?></h3>
                    <p>Total Products</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $orders_count; ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa fa-dollar-sign"></i>
                </div>
                <div class="stat-info">
                    <h3>$<?php echo number_format($total_revenue, 2); ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
        </div>
        
        <!-- Admin Tabs -->
        <div class="admin-tabs">
            <div class="tab-navigation">
                <button class="tab-btn active" data-tab="dashboard">Dashboard</button>
                <button class="tab-btn" data-tab="users">Users</button>
                <button class="tab-btn" data-tab="products">Products</button>
                <button class="tab-btn" data-tab="orders">Orders</button>
                <button class="tab-btn" data-tab="coupons">Coupons</button>
                <button class="tab-btn" data-tab="banners">Banners</button>
            </div>
            
            <div class="tab-content active" id="dashboard">
                <div class="dashboard-content">
                    <div class="recent-orders">
                        <h3>Recent Orders</h3>
                        <div class="orders-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                        <td>
                                            <div><?php echo $order['full_name']; ?></div>
                                            <small><?php echo $order['email']; ?></small>
                                        </td>
                                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <button class="btn-action view-order" data-order-id="<?php echo $order['id']; ?>">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            <button class="btn-action edit-order" data-order-id="<?php echo $order['id']; ?>">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tab-content" id="users">
                <div class="tab-header">
                    <h3>User Management</h3>
                    <button class="btn btn-primary" id="addUserBtn">Add User</button>
                </div>
                <div class="users-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($users as $user_item):
                            ?>
                            <tr>
                                <td><?php echo $user_item['id']; ?></td>
                                <td>
                                    <div class="user-info">
                                        <?php if (!empty($user_item['avatar']) && file_exists($user_item['avatar'])): ?>
                                        <img src="<?php echo $user_item['avatar']; ?>" alt="<?php echo $user_item['full_name']; ?>" class="user-avatar">
                                        <?php else: ?>
                                        <div class="user-avatar default-avatar">
                                            <i class="fa fa-user"></i>
                                        </div>
                                        <?php endif; ?>
                                        <span><?php echo $user_item['full_name']; ?></span>
                                    </div>
                                </td>
                                <td><?php echo $user_item['email']; ?></td>
                                <td><?php echo $user_item['phone'] ?: 'N/A'; ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $user_item['role']; ?>">
                                        <?php echo ucfirst($user_item['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user_item['created_at'])); ?></td>
                                <td>
                                    <button class="btn-action edit-user" data-user-id="<?php echo $user_item['id']; ?>">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn-action delete-user" data-user-id="<?php echo $user_item['id']; ?>">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>        
                    </table>
                </div>
            </div>
            
            <div class="tab-content" id="products">
                <div class="tab-header">
                    <h3>Product Management</h3>
                    <button class="btn btn-primary" id="addProductBtn">Add Product</button>
                </div>
                <div class="products-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Rating</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $products = $db->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($products as $product):
                            ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td>
                                    <img src="../frontend/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="product-thumb">
                                </td>
                                <td><?php echo $product['name']; ?></td>
                                <td>
                                    <span class="category-badge"><?php echo ucfirst($product['category']); ?></span>
                                </td>
                                <td>$<?php echo number_format($product['price'], 2); ?></td>
                                <td><?php echo $product['stock']; ?></td>
                                <td>
                                    <div class="product-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fa <?php echo $i <= $product['rating'] ? 'fa-star' : 'fa-star-o'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </td>
                                <td>
                                    <button class="btn-action edit-product" data-product-id="<?php echo $product['id']; ?>">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn-action delete-product" data-product-id="<?php echo $product['id']; ?>">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="tab-content" id="banners">
                <div class="tab-header">
                    <h3>Banner Management</h3>
                    <button class="btn btn-primary" id="addBannerBtn">Add Banner</button>
                </div>
                <div class="banners-grid">
                    <?php
                    $banners = $db->query("SELECT * FROM banners ORDER BY position, created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($banners as $banner):
                    ?>
                    <div class="banner-card">
                        <img src="../<?php echo $banner['image']; ?>" alt="<?php echo $banner['title']; ?>">
                        <div class="banner-info">
                            <h4><?php echo $banner['title']; ?></h4>
                            <p><?php echo $banner['description']; ?></p>
                            <div class="banner-meta">
                                <span>Position: <?php echo $banner['position']; ?></span>
                                <span class="status <?php echo $banner['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $banner['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </div>
                            <div class="banner-actions">
                                <button class="btn-action edit-banner" data-banner-id="<?php echo $banner['id']; ?>">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button class="btn-action delete-banner" data-banner-id="<?php echo $banner['id']; ?>">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="tab-content" id="coupons">
                <div class="tab-header">
                    <h3>Coupon Management</h3>
                    <button class="btn btn-primary" id="addCouponBtn">Add Coupon</button>
                </div>
                <div class="coupons-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Coupon Code</th>
                                <th>Discount</th>
                                <th>Min Order</th>
                                <th>Usage</th>
                                <th>Validity</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $coupons = $db->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
                            if (empty($coupons)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 20px;">
                                        No coupons found. <a href="javascript:void(0)" onclick="openModal('couponModal')">Create your first coupon</a>
                                    </td>
                                </tr>
                            <?php else:
                            foreach ($coupons as $coupon):
                            ?>
                            <tr>
                                <td><?php echo $coupon['id'] ?? ''; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($coupon['code'] ?? ''); ?></strong>
                                </td>
                                <td>
                                    <?php 
                                    $discountType = $coupon['discount_type'] ?? '';
                                    $discountValue = $coupon['discount_value'] ?? 0;
                                    if ($discountType == 'percentage') {
                                        echo $discountValue . '%';
                                    } else {
                                        echo '$' . number_format($discountValue, 2);
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $minOrder = $coupon['min_order_amount'] ?? 0;
                                    echo $minOrder > 0 ? '$' . number_format($minOrder, 2) : 'No minimum'; 
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $usedCount = $coupon['used_count'] ?? 0;
                                    $usageLimit = $coupon['usage_limit'] ?? null;
                                    $usage_text = $usedCount . ' used';
                                    if ($usageLimit) {
                                        $usage_text .= ' / ' . $usageLimit . ' limit';
                                    } else {
                                        $usage_text .= ' (no limit)';
                                    }
                                    echo $usage_text;
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $startDate = $coupon['start_date'] ?? '';
                                    $endDate = $coupon['end_date'] ?? '';
                                    
                                    if (!empty($startDate) && !empty($endDate)) {
                                        $now = time();
                                        $start = strtotime($startDate);
                                        $end = strtotime($endDate);
                                        
                                        if ($now < $start) {
                                            echo '<span class="status-badge status-pending">Starts ' . date('M d, Y', $start) . '</span>';
                                        } elseif ($now > $end) {
                                            echo '<span class="status-badge status-expired">Expired</span>';
                                        } else {
                                            echo '<span class="status-badge status-active">Valid until ' . date('M d, Y', $end) . '</span>';
                                        }
                                    } else {
                                        echo '<span class="status-badge status-inactive">No dates set</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $isActive = $coupon['is_active'] ?? 0;
                                    $statusClass = $isActive ? 'active' : 'inactive';
                                    $statusText = $isActive ? 'Active' : 'Inactive';
                                    ?>
                                    <span class="status-badge status-<?php echo $statusClass; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-action edit-coupon" data-coupon-id="<?php echo $coupon['id'] ?? ''; ?>">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn-action delete-coupon" data-coupon-id="<?php echo $coupon['id'] ?? ''; ?>">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; 
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Product Modal -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3 id="productModalTitle">Thêm sản phẩm mới</h3>
        <form id="productForm" enctype="multipart/form-data">
            <div class="form-group">
                <label>Tên sản phẩm *</label>
                <input type="text" name="name" placeholder="Nhập tên sản phẩm" required>
            </div>
            <div class="form-group">
                <label>Danh mục *</label>
                <select name="category" required>
                    <option value="">-- Chọn danh mục --</option>
                    <option value="sofa">Sofa</option>
                    <option value="table">Bàn</option>
                    <option value="lamp">Đèn</option>
                    <option value="bed">Giường</option>
                    <option value="bookshelf">Kệ sách</option>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Giá (USD) *</label>
                    <input type="number" name="price" step="0.01" min="0.01" placeholder="0.00" required>
                </div>
                <div class="form-group">
                    <label>Tồn kho *</label>
                    <input type="number" name="stock" min="0" placeholder="0" required>
                </div>
            </div>
            <div class="form-group">
                <label>Mô tả</label>
                <textarea name="description" rows="4" placeholder="Nhập mô tả sản phẩm"></textarea>
            </div>
            <div class="form-group">
                <label>Hình ảnh sản phẩm *</label>
                <input type="file" name="image" id="productImage" accept="image/jpeg,image/png,image/gif,image/webp" required>
                <small>Định dạng: JPG, PNG, GIF, WebP. Dung lượng tối đa: 5MB</small>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Lưu sản phẩm</button>
            </div>
        </form>
    </div>
</div>

<!-- User Modal -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3 id="userModalTitle">Add User</h3>
        <form id="userForm">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone">
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save User</button>
            </div>
        </form>
    </div>
</div>

<!-- Coupon Modal -->
<div id="couponModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3 id="couponModalTitle">Add Coupon</h3>
        <form id="couponForm">
            <div class="form-group">
                <label>Coupon Code *</label>
                <input type="text" name="code" required placeholder="e.g., SALE20">
            </div>
            
            <div class="form-group">
                <label>Discount Type *</label>
                <select name="discount_type" required>
                    <option value="percentage">Percentage (%)</option>
                    <option value="fixed">Fixed Amount ($)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Discount Value *</label>
                <input type="number" name="discount_value" step="0.01" required placeholder="e.g., 20 for 20% or $20">
            </div>
            
            <div class="form-group">
                <label>Minimum Order Amount</label>
                <input type="number" name="min_order_amount" step="0.01" placeholder="0 for no minimum">
            </div>
            
            <div class="form-group">
                <label>Maximum Discount Amount (for percentage only)</label>
                <input type="number" name="max_discount_amount" step="0.01" placeholder="Leave empty for no limit">
            </div>
            
            <div class="form-group">
                <label>Usage Limit</label>
                <input type="number" name="usage_limit" placeholder="Leave empty for unlimited">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Start Date *</label>
                    <input type="datetime-local" name="start_date" required>
                </div>
                
                <div class="form-group">
                    <label>End Date *</label>
                    <input type="datetime-local" name="end_date" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" checked> Active Coupon
                </label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Coupon</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 30px;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.modal .close {
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    color: #aaa;
    line-height: 1;
}

.modal .close:hover {
    color: #000;
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
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.form-actions {
    text-align: center;
    margin-top: 30px;
}

.btn-primary {
    background: #007bff;
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
}

.btn-primary:hover {
    background: #0056b3;
}

/* Tab Styles */
.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.tab-btn.active {
    background: #007bff;
    color: white;
}
.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.default-avatar {
    background: #000;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.default-avatar .fa-user {
    margin: 0;
}

.role-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.role-admin {
    background: #dc3545;
    color: white;
}

.role-user {
    background: #28a745;
    color: white;
}

.btn-action {
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px;
    margin: 0 2px;
    color: #666;
}

.btn-action:hover {
    color: #000;
}
</style>

<script>
// Global modal management
function openModal(modalId) {
    console.log('Opening modal:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
    } else {
        console.error('Modal not found:', modalId);
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing admin panel...');
    
    // Tab navigation
    document.querySelectorAll('.tab-btn').forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.dataset.tab;
            
            // Remove active class from all tabs and contents
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            // Add active class to current tab and content
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Modal close buttons
    document.querySelectorAll('.modal .close').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            e.target.style.display = 'none';
        }
    });
    
    // Add Product button
    document.getElementById('addProductBtn')?.addEventListener('click', function() {
        console.log('Add product button clicked');
        openModal('productModal');
    });
    
    // Add User button
    document.getElementById('addUserBtn')?.addEventListener('click', function() {
        console.log('Add user button clicked');
        openModal('userModal');
    });
    
    // Add Coupon button
    document.getElementById('addCouponBtn')?.addEventListener('click', function() {
        console.log('Add coupon button clicked');
        openModal('couponModal');
    });
    
    // Add Banner button
    document.getElementById('addBannerBtn')?.addEventListener('click', function() {
        console.log('Add banner button clicked');
        alert('Add banner functionality will be implemented soon');
    });
    
    // Product form submission
    document.getElementById('productForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Product form submitted');
        alert('Product added successfully! (This is a demo)');
        closeModal('productModal');
    });
    
    // User form submission
    document.getElementById('userForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('User form submitted');
        alert('User added successfully! (This is a demo)');
        closeModal('userModal');
    });
    
    // Coupon form submission
    document.getElementById('couponForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Coupon form submitted');
        
        const formData = new FormData(this);
        
        // Simple validation
        const code = formData.get('code');
        const discountValue = formData.get('discount_value');
        const startDate = formData.get('start_date');
        const endDate = formData.get('end_date');
        
        if (!code || !discountValue || !startDate || !endDate) {
            alert('Please fill in all required fields');
            return;
        }
        
        // Show loading
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = 'Saving...';
        submitBtn.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
            alert('Coupon created successfully! (This is a demo)');
            closeModal('couponModal');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 1000);
    });
    
    // Edit and Delete buttons - basic functionality
    document.querySelectorAll('.edit-product, .edit-user, .edit-coupon, .edit-banner').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.productId || this.dataset.userId || this.dataset.couponId || this.dataset.bannerId;
            alert('Edit functionality for ID: ' + id + ' will be implemented soon');
        });
    });
    
    document.querySelectorAll('.delete-product, .delete-user, .delete-coupon, .delete-banner').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.productId || this.dataset.userId || this.dataset.couponId || this.dataset.bannerId;
            if (confirm('Are you sure you want to delete this item?')) {
                alert('Delete functionality for ID: ' + id + ' will be implemented soon');
            }
        });
    });
    
    console.log('Admin panel initialized successfully');
});
</script>

<?php
// sửa include footer để dùng footer chung bên frontend
include __DIR__ . '/../frontend/includes/footer.php';