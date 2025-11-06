<?php
$pageTitle = "Soudemy - Modern Furniture Store";
include 'includes/header.php';

// Dữ liệu sản phẩm featured với ID chính xác
$featuredProducts = [
    [
        'id' => 4,
        'name' => 'Modern Bed', 
        'price' => 899.00, 
        'image' => 'images/bed/giuong1.png', 
        'category' => 'bed'
    ],
    [
        'id' => 3,
        'name' => 'Contemporary Lamp', 
        'price' => 156.00, 
        'image' => 'images/lamp/lamp1.png', 
        'category' => 'lamp'
    ],
    [
        'id' => 1,
        'name' => 'Comfort Sofa', 
        'price' => 750.00, 
        'image' => 'images/Sofa/sofa1.png', 
        'category' => 'sofa'
    ]
];
?>

<!-- Hero Section with Carousel -->
<section class="hero">
    <div class="hero-carousel">
        <!-- Slide 1 -->
        <div class="hero-slide active" style="background-image: url('images/Sofa/sofa1.png');">
            <div class="container">
                <div class="hero-content">
                    <h1>ALL FOR YOUR HOME</h1>
                    <p>Discover our premium collection of modern furniture designed for your comfort and style.</p>
                    <a href="shop.php" class="btn btn-primary">VIEW MORE</a>
                </div>
            </div>
        </div>

        <!-- Slide 2 -->
        <div class="hero-slide" style="background-image: url('images/bed/giuong1.png');">
            <div class="container">
                <div class="hero-content">
                    <h1>MODERN BED</h1>
                    <p>Experience ultimate comfort with our contemporary bed designs that blend style and functionality.</p>
                    <a href="shop.php?category=bed" class="btn btn-primary">EXPLORE BEDS</a>
                </div>
            </div>
        </div>

        <!-- Slide 3 -->
        <div class="hero-slide" style="background-image: url('images/lamp/lamp1.png');">
            <div class="container">
                <div class="hero-content">
                    <h1>CONTEMPORARY LIGHTING</h1>
                    <p>Illuminate your space with modern lighting solutions that add warmth and ambiance to any room.</p>
                    <a href="shop.php?category=lamp" class="btn btn-primary">EXPLORE LAMPS</a>
                </div>
            </div>
        </div>

        <!-- Navigation Dots -->
        <div class="carousel-dots">
            <span class="dot active" onclick="currentSlide(0)"></span>
            <span class="dot" onclick="currentSlide(1)"></span>
            <span class="dot" onclick="currentSlide(2)"></span>
        </div>
    </div>
</section>

<!-- Products of the Week -->
<section class="products-week">
    <div class="container">
        <h2 class="section-title">PRODUCTS OF THE WEEK</h2>
        <p class="section-desc">Explore our carefully selected furniture pieces that combine style, comfort, and quality craftsmanship.</p>
        
        <div class="product-grid">
            <?php foreach ($featuredProducts as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <a href="product.php?id=<?php echo $product['id']; ?>">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    </a>
                </div>
                <h3><?php echo $product['name']; ?></h3>
                <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="section-banner">
            <div class="banner-content">
                <p>Experience luxury and comfort with our premium furniture collection. Each piece is crafted with attention to detail.</p>
                <a href="shop.php" class="btn btn-outline">View more</a>
            </div>
        </div>
    </div>
</section>

<!-- Furniture Categories -->
<section class="furniture-categories">
    <div class="container">
        <div class="category-item">
            <div class="category-content">
                <h2>STYLISH CHAIRS</h2>
                <p>Comfortable and elegant seating solutions that blend perfectly with modern interior design.</p>
                <a href="shop.php?category=sofa" class="btn btn-outline">View more</a>
            </div>
            <div class="category-image">
                <img src="images/Sofa/sofa2.png" alt="Stylish Chair">
            </div>
        </div>
        
        <div class="category-item">
            <div class="category-content">
                <h2>ELEGANT TABLES</h2>
                <p>Functional and beautiful tables that serve as the centerpiece of your dining and living spaces.</p>
                <a href="shop.php?category=table" class="btn btn-outline">View more</a>
            </div>
            <div class="category-image">
                <img src="images/table/table2.png" alt="Elegant Table">
            </div>
        </div>
        
        <div class="category-item">
            <div class="category-content">
                <h2>CONTEMPORARY LAMPS</h2>
                <p>Illuminate your space with our modern lighting solutions that add warmth and ambiance.</p>
                <a href="shop.php?category=lamp" class="btn btn-outline">View more</a>
            </div>
            <div class="category-image">
                <img src="images/lamp/lamp3.png" alt="Contemporary Lamp">
            </div>
        </div>
    </div>
</section>

<!-- Express Delivery Banner -->
<section class="express-delivery">
    <div class="container">
        <div class="delivery-content">
            <p>order now for an express delivery in 24h !</p>
            <a href="shop.php" class="btn btn-outline">View more</a>
        </div>
    </div>
</section>

<!-- Features -->
<section class="features">
    <div class="container">
        <div class="feature-grid">
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fa fa-clock"></i>
                </div>
                <h3>Shop online</h3>
                <p>Browse our complete collection from the comfort of your home</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fa fa-shipping-fast"></i>
                </div>
                <h3>Free shipping</h3>
                <p>Enjoy free delivery on all orders over $500</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fa fa-undo"></i>
                </div>
                <h3>Return policy</h3>
                <p>30-day return guarantee for your peace of mind</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fa fa-credit-card"></i>
                </div>
                <h3>PAYMENT</h3>
                <p>Secure payment options including credit cards and PayPal</p>
            </div>
        </div>
    </div>
</section>

<!-- Blog Posts Preview -->
<section class="blog-preview">
    <div class="container">
        <h2 class="section-title">last blog post</h2>
        
        <div class="blog-grid">
            <div class="blog-card">
                <div class="blog-image">
                    <img src="images/table/table3.png" alt="Interior Design Tips">
                </div>
                <div class="blog-date">Oct 15, 2024</div>
                <h3>How to choose the perfect dining table</h3>
                <a href="#" class="read-more">Read more</a>
            </div>
            
            <div class="blog-card">
                <div class="blog-image">
                    <img src="images/bed/giuong2.png" alt="Bedroom Design">
                </div>
                <div class="blog-date">Oct 12, 2024</div>
                <h3>Creating a cozy bedroom sanctuary</h3>
                <a href="#" class="read-more">Read more</a>
            </div>
            
            <div class="blog-card">
                <div class="blog-image">
                    <img src="images/bookshelf/ke1.png" alt="Storage Solutions">
                </div>
                <div class="blog-date">Oct 10, 2024</div>
                <h3>Smart storage solutions for small spaces</h3>
                <a href="#" class="read-more">Read more</a>
            </div>
        </div>
    </div>
</section>
<style>
.product-price {
    color: #000000 !important;
}
</style>

<!-- Carousel JavaScript -->
<script>
    let currentSlideIndex = 0;
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.dot');
    const totalSlides = slides.length;

    // Auto-rotate slides every 5 seconds
    setInterval(function() {
        currentSlideIndex = (currentSlideIndex + 1) % totalSlides;
        showSlide(currentSlideIndex);
    }, 5000);

    // Function to show specific slide
    function showSlide(index) {
        // Remove active class from all slides and dots
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));

        // Add active class to current slide and dot
        slides[index].classList.add('active');
        dots[index].classList.add('active');
    }

    // Function called by dot clicks
    function currentSlide(index) {
        currentSlideIndex = index;
        showSlide(currentSlideIndex);
    }

    // Initialize first slide
    showSlide(0);
</script>

<?php include 'includes/footer.php'; ?>