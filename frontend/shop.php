<?php
$pageTitle = "Shop - Soudemy";
include 'includes/header.php';

// Kết nối database
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

// Lấy sản phẩm từ database
$products = [];
if ($db) {
    try {
        $query = "SELECT * FROM products ORDER BY created_at DESC";
        $stmt = $db->query($query);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $products = [];
    }
} else {
    // Fallback data if database not available
    $products = [
        ['id' => 1, 'name' => 'Comfort Sofa', 'price' => 750.00, 'image' => 'images/Sofa/sofa1.png', 'category' => 'sofa'],
        ['id' => 2, 'name' => 'Wooden Table', 'price' => 299.00, 'image' => 'images/table/table1.png', 'category' => 'table'],
    ];
}

// XỬ LÝ ADD TO CART
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $productId = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    
    // Tìm sản phẩm trong database
    $productToAdd = null;
    foreach ($products as $product) {
        if ($product['id'] == $productId) {
            $productToAdd = $product;
            break;
        }
    }
    
    if ($productToAdd) {
        // Khởi tạo giỏ hàng nếu chưa có
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Thêm sản phẩm vào giỏ hàng
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = [
                'name' => $productToAdd['name'],
                'price' => $productToAdd['price'],
                'quantity' => $quantity,
                'image' => $productToAdd['image'],
                'category' => $productToAdd['category']
            ];
        }
        
        $_SESSION['cart_count'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
        $success_message = "Đã thêm {$productToAdd['name']} vào giỏ hàng!";
    }
}

// Xử lý các tham số filter và sort
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : 'all';
$selectedSort = isset($_GET['sort']) ? $_GET['sort'] : 'popularity';
$selectedPriceRange = isset($_GET['price_range']) ? $_GET['price_range'] : '';
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Lọc sản phẩm ban đầu
$filteredProducts = $products;

// Lọc theo category
if ($selectedCategory !== 'all') {
    $filteredProducts = array_filter($filteredProducts, function($product) use ($selectedCategory) {
        return $product['category'] === $selectedCategory;
    });
}

// Lọc theo price range
if ($selectedPriceRange) {
    list($minPrice, $maxPrice) = explode('-', $selectedPriceRange);
    $filteredProducts = array_filter($filteredProducts, function($product) use ($minPrice, $maxPrice) {
        return $product['price'] >= $minPrice && $product['price'] <= $maxPrice;
    });
}

// Tìm kiếm sản phẩm
if ($searchTerm) {
    $filteredProducts = array_filter($filteredProducts, function($product) use ($searchTerm) {
        return stripos($product['name'], $searchTerm) !== false;
    });
}

// Sắp xếp sản phẩm
switch ($selectedSort) {
    case 'price_low':
        usort($filteredProducts, function($a, $b) {
            return $a['price'] <=> $b['price'];
        });
        break;
    case 'price_high':
        usort($filteredProducts, function($a, $b) {
            return $b['price'] <=> $a['price'];
        });
        break;
    case 'latest':
        usort($filteredProducts, function($a, $b) {
            return $b['id'] <=> $a['id'];
        });
        break;
    case 'popularity':
    default:
        // Mặc định giữ nguyên thứ tự
        break;
}

// Xử lý phân trang
$itemsPerPage = 12;
$totalItems = count($filteredProducts);
$totalPages = ceil($totalItems / $itemsPerPage);

// Lấy trang hiện tại từ URL
$currentPage = isset($_GET['page']) ? max(1, min($totalPages, intval($_GET['page']))) : 1;

// Tính toán sản phẩm cho trang hiện tại
$startIndex = ($currentPage - 1) * $itemsPerPage;
$currentProducts = array_slice($filteredProducts, $startIndex, $itemsPerPage);

// Hàm tạo URL với các tham số hiện tại
function generateUrl($params = []) {
    $currentParams = $_GET;
    unset($currentParams['page']); // Xóa page khi thay đổi filter/sort
    
    $mergedParams = array_merge($currentParams, $params);
    return 'shop.php?' . http_build_query($mergedParams);
}

// Tính số lượng sản phẩm theo category và price range
function countProductsByCategory($products, $category) {
    if ($category === 'all') return count($products);
    return count(array_filter($products, function($product) use ($category) {
        return $product['category'] === $category;
    }));
}

function countProductsByPriceRange($products, $min, $max) {
    return count(array_filter($products, function($product) use ($min, $max) {
        return $product['price'] >= $min && $product['price'] <= $max;
    }));
}
?>

<!-- Shop Content -->
<section class="shop-content">
    <div class="container">
        <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
            <?php echo $success_message; ?>
            <a href="cart.php" class="view-cart-link">Xem giỏ hàng</a>
        </div>
        <?php endif; ?>
        
        <div class="shop-layout">
            <div class="shop-main">
                <div class="shop-header">
                    <div class="shop-results">Showing <?php echo $startIndex + 1; ?>-<?php echo min($startIndex + $itemsPerPage, $totalItems); ?> of <?php echo $totalItems; ?> results</div>
                    <div class="shop-sorting">
                        <select class="sort-select" id="sortSelect" onchange="window.location.href=this.value">
                            <option value="<?php echo generateUrl(['sort' => 'popularity']); ?>" <?php echo $selectedSort === 'popularity' ? 'selected' : ''; ?>>Sort by popularity</option>
                            <option value="<?php echo generateUrl(['sort' => 'price_low']); ?>" <?php echo $selectedSort === 'price_low' ? 'selected' : ''; ?>>Sort by price: low to high</option>
                            <option value="<?php echo generateUrl(['sort' => 'price_high']); ?>" <?php echo $selectedSort === 'price_high' ? 'selected' : ''; ?>>Sort by price: high to low</option>
                            <option value="<?php echo generateUrl(['sort' => 'latest']); ?>" <?php echo $selectedSort === 'latest' ? 'selected' : ''; ?>>Sort by latest</option>
                        </select>
                    </div>
                </div>
                
                <div class="product-grid shop-grid">
                    <?php if (count($currentProducts) > 0): ?>
                        <?php foreach ($currentProducts as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <a href="product.php?id=<?php echo $product['id']; ?>">
                                    <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                                </a>
                            </div>
                            <h3><?php echo $product['name']; ?></h3>
                            <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                            <div class="product-actions">
                                <form method="POST" action="<?php echo generateUrl(); ?>" class="add-to-cart-form">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" name="add_to_cart" class="btn btn-primary add-to-cart-btn">
                                        Add to Cart
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-products">
                            <p>No products found matching your criteria.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="<?php echo generateUrl(['page' => $currentPage - 1]); ?>" class="page-link">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="<?php echo generateUrl(['page' => $i]); ?>" class="page-link <?php echo $i == $currentPage ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="<?php echo generateUrl(['page' => $currentPage + 1]); ?>" class="page-link">Next</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="shop-sidebar">
                <div class="search-box">
                    <form method="GET" action="shop.php" id="searchForm">
                        <input type="text" name="search" id="searchInput" placeholder="Search products..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                        <button type="submit" id="searchButton"><i class="fa fa-search"></i></button>
                    </form>
                </div>
                
                <div class="filter-section">
                    <h4>Category</h4>
                    <ul class="filter-list">
                        <li><a href="<?php echo generateUrl(['category' => 'all', 'page' => 1]); ?>" class="<?php echo $selectedCategory === 'all' ? 'active' : ''; ?>">All Products (<?php echo countProductsByCategory($products, 'all'); ?>)</a></li>
                        <li><a href="<?php echo generateUrl(['category' => 'sofa', 'page' => 1]); ?>" class="<?php echo $selectedCategory === 'sofa' ? 'active' : ''; ?>">Sofas & Chairs (<?php echo countProductsByCategory($products, 'sofa'); ?>)</a></li>
                        <li><a href="<?php echo generateUrl(['category' => 'table', 'page' => 1]); ?>" class="<?php echo $selectedCategory === 'table' ? 'active' : ''; ?>">Tables (<?php echo countProductsByCategory($products, 'table'); ?>)</a></li>
                        <li><a href="<?php echo generateUrl(['category' => 'bed', 'page' => 1]); ?>" class="<?php echo $selectedCategory === 'bed' ? 'active' : ''; ?>">Beds (<?php echo countProductsByCategory($products, 'bed'); ?>)</a></li>
                        <li><a href="<?php echo generateUrl(['category' => 'lamp', 'page' => 1]); ?>" class="<?php echo $selectedCategory === 'lamp' ? 'active' : ''; ?>">Lighting (<?php echo countProductsByCategory($products, 'lamp'); ?>)</a></li>
                        <li><a href="<?php echo generateUrl(['category' => 'bookshelf', 'page' => 1]); ?>" class="<?php echo $selectedCategory === 'bookshelf' ? 'active' : ''; ?>">Storage (<?php echo countProductsByCategory($products, 'bookshelf'); ?>)</a></li>
                    </ul>
                </div>
                
                <div class="filter-section">
                    <h4>Price Range</h4>
                    <ul class="filter-list">
                        <li><a href="<?php echo generateUrl(['price_range' => '0-200', 'page' => 1]); ?>" class="<?php echo $selectedPriceRange === '0-200' ? 'active' : ''; ?>">Under $200 (<?php echo countProductsByPriceRange($products, 0, 200); ?>)</a></li>
                        <li><a href="<?php echo generateUrl(['price_range' => '200-500', 'page' => 1]); ?>" class="<?php echo $selectedPriceRange === '200-500' ? 'active' : ''; ?>">$200 - $500 (<?php echo countProductsByPriceRange($products, 200, 500); ?>)</a></li>
                        <li><a href="<?php echo generateUrl(['price_range' => '500-1000', 'page' => 1]); ?>" class="<?php echo $selectedPriceRange === '500-1000' ? 'active' : ''; ?>">$500 - $1000 (<?php echo countProductsByPriceRange($products, 500, 1000); ?>)</a></li>
                        <li><a href="<?php echo generateUrl(['price_range' => '1000-9999', 'page' => 1]); ?>" class="<?php echo $selectedPriceRange === '1000-9999' ? 'active' : ''; ?>">Over $1000 (<?php echo countProductsByPriceRange($products, 1000, 9999); ?>)</a></li>
                    </ul>
                </div>
                
                <!-- Clear Filters -->
                <?php if ($selectedCategory !== 'all' || $selectedPriceRange || $searchTerm || $selectedSort !== 'popularity'): ?>
                <div class="filter-section">
                    <a href="shop.php" class="btn btn-clear-filters">Clear All Filters</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
// Shop filter functionality
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
    display: block;
}

.add-to-cart-btn {
    width: 100%;
    margin-top: 10px;
}

.filter-list a.active {
    color: #007bff;
    font-weight: bold;
}

.btn-clear-filters {
    background-color: #6c757d;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    margin-top: 10px;
}

.btn-clear-filters:hover {
    background-color: #545b62;
    color: white;
}

.no-products {
    text-align: center;
    padding: 40px;
    font-size: 18px;
    color: #6c757d;
    grid-column: 1 / -1;
}
</style>
<style>
.product-price {
    color: #000000 !important;
}
</style>
<?php include 'includes/footer.php'; ?>