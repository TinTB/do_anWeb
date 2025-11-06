<?php
$pageTitle = "Product Details - Soudemy";
include 'includes/header.php';

// Kết nối database
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

// Lấy ID sản phẩm từ URL
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Lấy thông tin sản phẩm từ database
$product = null;
if ($db && $productId > 0) {
    try {
        $query = "SELECT * FROM products WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $product = null;
    }
}

// Nếu không tìm thấy sản phẩm từ database, chuyển hướng về shop
if (!$product) {
    header('Location: shop.php');
    exit;
}

// XỬ LÝ ADD TO CART TỪ PRODUCT PAGE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = intval($_POST['quantity']);
    
    // Khởi tạo giỏ hàng nếu chưa có
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Thêm sản phẩm vào giỏ hàng
    if (isset($_SESSION['cart'][$productId])) {
        // Nếu sản phẩm đã có trong giỏ, cập nhật số lượng
        $_SESSION['cart'][$productId]['quantity'] += $quantity;
    } else {
        // Thêm sản phẩm mới vào giỏ
        $_SESSION['cart'][$productId] = [
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'image' => $product['image'],
            'category' => $product['category']
        ];
    }
    
    // Cập nhật số lượng giỏ hàng
    $_SESSION['cart_count'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
    
    // Hiển thị thông báo thành công
    $success_message = "Đã thêm {$quantity} {$product['name']} vào giỏ hàng!";
}

// Lấy danh sách sản phẩm liên quan (cùng category) từ database
$relatedProducts = [];
if ($db) {
    try {
        $query = "SELECT * FROM products WHERE category = ? AND id != ? LIMIT 4";
        $stmt = $db->prepare($query);
        $stmt->execute([$product['category'], $productId]);
        $relatedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $relatedProducts = [];
    }
}
?>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <div class="container">
        <div class="breadcrumb-wrapper">
            <ul class="breadcrumb-list">
                <li><a href="index.php">Home</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="shop.php?category=<?php echo $product['category']; ?>"><?php echo ucfirst($product['category']); ?></a></li>
                <li><?php echo $product['name']; ?></li>
            </ul>
            <a href="javascript:history.back()" class="back-button">
                <i class="fa fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>
</div>

<!-- Product Detail -->
<section class="product-detail">
    <div class="container">
        <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
            <?php echo $success_message; ?>
            <a href="cart.php" class="view-cart-link">Xem giỏ hàng</a>
        </div>
        <?php endif; ?>
        
        <div class="product-layout">
            <div class="product-gallery">
                <div class="gallery-thumbnails">
                    <div class="thumbnail active" data-image="<?php echo $product['image']; ?>">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    </div>
                </div>
                <div class="main-image">
                    <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" id="mainProductImage">
                </div>
            </div>
            
            <div class="product-info">
                <h1><?php echo $product['name']; ?></h1>
                <div class="product-rating">
                    <div class="stars">
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star"></i>
                        <i class="fa fa-star-half-alt"></i>
                    </div>
                    <span class="rating-text">(<?php echo $product['rating'] ?? '4.5'; ?>/5 - <?php echo rand(80, 200); ?> reviews)</span>
                </div>
                
                <div class="product-price">
                    <span class="current-price">$<?php echo number_format($product['price'], 2); ?></span>
                </div>
                
                <div class="product-description">
                    <p><?php echo $product['description'] ?? 'Premium quality furniture item'; ?></p>
                </div>
                
                <div class="product-actions">
                    <form method="POST" action="product.php?id=<?php echo $productId; ?>" class="add-to-cart-form">
                        <div class="quantity-selector">
                            <button type="button" class="quantity-btn minus"><i class="fa fa-minus"></i></button>
                            <input type="number" class="quantity-input" name="quantity" value="1" min="1" id="productQuantity">
                            <button type="button" class="quantity-btn plus"><i class="fa fa-plus"></i></button>
                        </div>
                        
                        <button type="submit" name="add_to_cart" class="btn btn-primary add-to-cart-btn">
                            Add to Cart
                        </button>
                    </form>
                </div>
                
                <div class="product-wishlist">
                    <button class="wishlist-btn"><i class="fa fa-heart"></i> Add to wishlist</button>
                </div>
                
                <div class="product-meta">
                    <div class="meta-item">
                        <span>SKU:</span> PROD<?php echo str_pad($productId, 3, '0', STR_PAD_LEFT); ?>
                    </div>
                    <div class="meta-item">
                        <span>Category:</span> <?php echo ucfirst($product['category']); ?>
                    </div>
                    <div class="meta-item">
                        <span>Tag:</span> <a href="shop.php?category=<?php echo $product['category']; ?>"><?php echo $product['category']; ?></a>, <a href="shop.php">furniture</a>
                    </div>
                </div>
                
                <div class="product-tabs">
                    <div class="tabs-nav">
                        <button class="tab-btn active" data-tab="description">Description</button>
                        <button class="tab-btn" data-tab="additional">Additional information</button>
                        <button class="tab-btn" data-tab="reviews">Reviews</button>
                    </div>
                    
                    <div class="tab-content active" id="description">
                        <p><?php echo $product['description'] ?? 'Premium quality furniture item'; ?></p>
                        <h4>Features:</h4>
                        <ul>
                            <li>Premium quality materials</li>
                            <li>Easy to assemble</li>
                            <li>1-year warranty</li>
                            <li>Free shipping</li>
                        </ul>
                    </div>
                    
                    <div class="tab-content" id="additional">
                        <table class="specs-table">
                            <tr>
                                <td><strong>Dimensions</strong></td>
                                <td>80" W x 35" D x 30" H</td>
                            </tr>
                            <tr>
                                <td><strong>Material</strong></td>
                                <td>Premium Materials, Solid Construction</td>
                            </tr>
                            <tr>
                                <td><strong>Weight</strong></td>
                                <td>150 lbs</td>
                            </tr>
                            <tr>
                                <td><strong>Warranty</strong></td>
                                <td>2 Years</td>
                            </tr>
                            <tr>
                                <td><strong>Assembly</strong></td>
                                <td>Required (tools included)</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="tab-content" id="reviews">
                        <div class="review-summary">
                            <div class="average-rating">
                                <h3>4.5</h3>
                                <div class="stars">
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star-half-alt"></i>
                                </div>
                                <p>Based on 128 reviews</p>
                            </div>
                        </div>
                        <div class="reviews-list">
                            <div class="review-item">
                                <div class="reviewer">John D.</div>
                                <div class="stars">
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                </div>
                                <p>"Excellent quality and fast delivery. Highly recommended!"</p>
                            </div>
                            <div class="review-item">
                                <div class="reviewer">Sarah M.</div>
                                <div class="stars">
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star-half-alt"></i>
                                </div>
                                <p>"Beautiful product, exactly as described. Very happy with my purchase."</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Products -->
<section class="related-products">
    <div class="container">
        <h2 class="section-title">Related Products</h2>
        
        <div class="product-grid">
            <?php if (!empty($relatedProducts)): ?>
                <?php foreach ($relatedProducts as $relatedProduct): ?>
                <div class="product-card">
                    <div class="product-image">
                        <a href="product.php?id=<?php echo $relatedProduct['id']; ?>">
                            <img src="<?php echo $relatedProduct['image']; ?>" alt="<?php echo $relatedProduct['name']; ?>">
                        </a>
                    </div>
                    <h3><?php echo $relatedProduct['name']; ?></h3>
                    <div class="product-price">$<?php echo number_format($relatedProduct['price'], 2); ?></div>
                    <div class="product-actions">
                        <a href="product.php?id=<?php echo $relatedProduct['id']; ?>" class="btn btn-outline">View Details</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No related products found.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
// JavaScript cho product page
document.addEventListener('DOMContentLoaded', function() {
    // Thumbnail click
    const thumbnails = document.querySelectorAll('.thumbnail');
    const mainImage = document.getElementById('mainProductImage');
    
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            // Remove active class from all thumbnails
            thumbnails.forEach(t => t.classList.remove('active'));
            // Add active class to clicked thumbnail
            this.classList.add('active');
            // Change main image
            const newImage = this.getAttribute('data-image');
            mainImage.src = newImage;
        });
    });
    
    // Quantity selector
    const minusBtn = document.querySelector('.quantity-btn.minus');
    const plusBtn = document.querySelector('.quantity-btn.plus');
    const quantityInput = document.getElementById('productQuantity');
    
    if (minusBtn && plusBtn && quantityInput) {
        minusBtn.addEventListener('click', function() {
            let quantity = parseInt(quantityInput.value);
            if (quantity > 1) {
                quantityInput.value = quantity - 1;
            }
        });
        
        plusBtn.addEventListener('click', function() {
            let quantity = parseInt(quantityInput.value);
            quantityInput.value = quantity + 1;
        });
    }
    
    // Tab switching
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Remove active class from all buttons and contents
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Wishlist
    const wishlistBtn = document.querySelector('.wishlist-btn');
    if (wishlistBtn) {
        wishlistBtn.addEventListener('click', function() {
            this.classList.toggle('active');
            if (this.classList.contains('active')) {
                this.innerHTML = '<i class="fa fa-heart"></i> Added to wishlist';
            } else {
                this.innerHTML = '<i class="fa fa-heart"></i> Add to wishlist';
            }
        });
    }
});
</script>

<style>
.alert {
    padding: 12px 16px;
    margin-bottom: 20px;
    border-radius: 4px;
    border: 1px solid transparent;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.view-cart-link {
    margin-left: 10px;
    color: #155724;
    text-decoration: underline;
    font-weight: bold;
}

.add-to-cart-form {
    display: flex;
    align-items: center;
    gap: 10px;
}

.quantity-selector {
    display: flex;
    align-items: center;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}

.quantity-btn {
    background: #f8f9fa;
    border: none;
    padding: 8px 12px;
    cursor: pointer;
    font-size: 14px;
}

.quantity-input {
    width: 50px;
    border: none;
    text-align: center;
    padding: 8px;
    font-size: 14px;
}

.add-to-cart-btn {
    white-space: nowrap;
    flex: 1;
}
</style>

<?php include 'includes/footer.php'; ?>