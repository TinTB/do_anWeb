<?php
$pageTitle = "About Us - Soudemy";
include 'includes/header.php';
?>

<!-- About Hero Section with Carousel -->
<section class="hero about-hero">
    <div class="hero-carousel">
        <!-- Slide 1 -->
        <div class="hero-slide active" style="background-image: url('images/Sofa/sofa13.png');">
            <div class="container">
                <div class="hero-content">
                    <h1>About Us</h1>
                    <p>Discover our story of craftsmanship, quality, and commitment to modern living.</p>
                    <a href="#about-story" class="btn btn-primary">Learn More</a>
                </div>
            </div>
        </div>

        <!-- Navigation Dots -->
        <div class="carousel-dots">
            <span class="dot active" onclick="currentSlide(0)"></span>
        </div>
    </div>
</section>

<style>
/* Hero Carousel Styles - Same as Homepage */
.hero {
    position: relative;
    width: 100%;
    height: 600px;
    overflow: hidden;
    background: #f5f5f5;
}

.hero-carousel {
    position: relative;
    width: 100%;
    height: 100%;
}

.hero-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    opacity: 0;
    transition: opacity 0.8s ease-in-out;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hero-slide.active {
    opacity: 1;
    z-index: 10;
}

.hero-slide::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.4);
    z-index: 1;
}

.hero-slide .container {
    position: relative;
    z-index: 2;
    text-align: center;
}

.hero-content {
    color: white;
    max-width: 600px;
    margin: 0 auto;
}

.hero-content h1 {
    font-size: 3.5rem;
    font-weight: 800;
    margin: 0 0 20px 0;
    text-shadow: 2px 2px 8px rgba(0,0,0,0.5);
    letter-spacing: 1px;
    text-transform: uppercase;
}

.hero-content p {
    font-size: 1.2rem;
    margin: 0 0 30px 0;
    font-weight: 300;
    text-shadow: 1px 1px 4px rgba(0,0,0,0.5);
    line-height: 1.6;
}

.carousel-dots {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 15px;
    z-index: 20;
}

.dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255,255,255,0.6);
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.dot.active {
    background: #d4a574;
    width: 14px;
    height: 14px;
    box-shadow: 0 0 8px rgba(212,165,116,0.8);
}

.dot:hover {
    background: rgba(255,255,255,0.9);
}

@media (max-width: 768px) {
    .hero {
        height: 400px;
    }
    .hero-content h1 {
        font-size: 2rem;
    }
    .hero-content p {
        font-size: 1rem;
    }
}

@media (max-width: 480px) {
    .hero {
        height: 300px;
    }
    .hero-content h1 {
        font-size: 1.5rem;
    }
    .hero-content p {
        font-size: 0.9rem;
    }
    .carousel-dots {
        bottom: 15px;
        gap: 10px;
    }
}
</style>

<!-- Carousel Script -->
<script>
let currentSlideIndex = 0;
const autoSlideInterval = 5000; // 5 seconds

function showSlide(index) {
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.dot');
    
    if (index >= slides.length) {
        currentSlideIndex = 0;
    } else if (index < 0) {
        currentSlideIndex = slides.length - 1;
    } else {
        currentSlideIndex = index;
    }
    
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    
    slides[currentSlideIndex].classList.add('active');
    dots[currentSlideIndex].classList.add('active');
}

function currentSlide(index) {
    showSlide(index);
    resetAutoSlide();
}

function nextSlide() {
    showSlide(currentSlideIndex + 1);
}

function resetAutoSlide() {
    clearInterval(window.autoSlideTimer);
    // Chỉ auto-slide nếu có nhiều hơn 1 slide
    if (document.querySelectorAll('.hero-slide').length > 1) {
        window.autoSlideTimer = setInterval(nextSlide, autoSlideInterval);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    showSlide(0);
    resetAutoSlide();
});
</script>
</style>
    </style>

<!-- Features -->
<section class="features">
    <div class="container">
        <div class="feature-grid">
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fa fa-clock"></i>
                </div>
                <h3>Shop online</h3>
                <p>Browse our complete collection from the comfort of your home with easy online ordering</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fa fa-shipping-fast"></i>
                </div>
                <h3>Free shipping</h3>
                <p>Enjoy complimentary delivery on all orders over $500 to your doorstep</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fa fa-undo"></i>
                </div>
                <h3>Return policy</h3>
                <p>30-day hassle-free returns with full money-back guarantee</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fa fa-credit-card"></i>
                </div>
                <h3>PAYMENT</h3>
                <p>Secure payment processing with multiple options including credit cards and PayPal</p>
            </div>
        </div>
    </div>
</section>

<!-- Auto-play Video Section - Full Width -->
<section class="video-hero-section">
    <div class="video-container">
        <video autoplay muted loop playsinline poster="images/video-preview.jpg">
            <source src="images/video/about-us.mp4" type="video/mp4">
            <source src="images/video/about-us.webm" type="video/webm">
            Your browser does not support the video tag.
        </video>
        <div class="video-overlay">
            <!-- Hero panel inside video (bottom-center) -->
            <div class="hero-panel" id="heroPanel">
                <h2>Our Story</h2>
                <p>Discover the craftsmanship behind our furniture</p>
                <a href="shop.php" class="btn btn-primary btn-cta">Explore Collection</a>
            </div>

            <!-- play/pause (shown if autoplay blocked or on mobile) -->
            <button id="bannerToggle" class="video-btn" aria-label="Play/Pause video">❚❚</button>
        </div>
    </div>
</section>
<style>
    /* Full Width Video Section */
/* Auto-play Video Hero Section */
.video-hero-section {
    position: relative;
    width: 100vw;
    margin-left: calc(-50vw + 50%);
    height: 90vh;
    overflow: hidden;
    background: #000;
}

.video-hero-section .video-container {
    width: 100%;
    height: 100%;
    position: relative;
}

.video-hero-section video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: brightness(0.95) contrast(1.05) saturate(1.1);
    display: block;
}

/* Video Overlay - subtle gradient to not overpower */
.video-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.25) 50%, rgba(0,0,0,0.3) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 5vw;
    color: white;
    z-index: 3;
}

/* Hero panel (inside video at center with prominent frame) */
.hero-panel {
    background: rgba(15,15,15,0.78);
    border: 2px solid rgba(212,165,116,0.35);
    padding: 32px 40px;
    border-radius: 16px;
    max-width: 1000px;
    width: 100%;
    display: flex;
    gap: 32px;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 20px 50px rgba(0,0,0,0.55), inset 0 1px 2px rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.hero-panel h2 {
    margin: 0;
    font-size: 2.2rem;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 800;
    color: #fff;
    min-width: 180px;
}

.hero-panel p {
    margin: 0;
    color: rgba(255,255,255,0.98);
    font-weight: 300;
    flex: 1;
    font-size: 1.1rem;
    line-height: 1.6;
}

.hero-panel .btn-cta {
    margin-left: 20px;
    white-space: nowrap;
    padding: 14px 32px;
    background: linear-gradient(135deg, #d4a574 0%, #c9945f 100%);
    color: #fff;
    font-weight: 600;
    font-size: 0.95rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.hero-panel .btn-cta:hover {
    background: linear-gradient(135deg, #e0b58a 0%, #d4a574 100%);
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(212,165,116,0.3);
}

/* Play/Pause Button */
.video-btn {
    position: absolute;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    background: rgba(212,165,116,0.9);
    border: none;
    border-radius: 50%;
    color: #fff;
    font-size: 1.2rem;
    cursor: pointer;
    z-index: 5;
    display: none;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}

.video-btn:hover {
    background: rgba(212,165,116,1);
    transform: scale(1.1);
}

@media (max-width: 1024px) {
    .video-hero-section { height: 85vh; }
    .hero-panel {
        gap: 24px;
        padding: 28px 32px;
    }
    .hero-panel h2 { font-size: 1.9rem; }
    .hero-panel p { font-size: 1rem; }
}

@media (max-width: 768px) {
    .video-hero-section { height: 70vh; }
    .video-overlay { padding: 30px 4vw; }
    .hero-panel {
        flex-direction: column;
        align-items: flex-start;
        gap: 18px;
        padding: 24px;
        border-radius: 12px;
    }
    .hero-panel h2 { 
        font-size: 1.6rem; 
        letter-spacing: 1px;
        min-width: auto;
    }
    .hero-panel p { 
        margin-left: 0; 
        font-size: 0.95rem;
        line-height: 1.5;
    }
    .hero-panel .btn-cta { 
        margin-left: 0;
        margin-top: 8px;
        width: 100%;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .video-hero-section { height: 60vh; }
    .video-overlay { padding: 20px 3vw; }
    .hero-panel {
        padding: 18px;
        border-radius: 10px;
        gap: 14px;
    }
    .hero-panel h2 { 
        font-size: 1.3rem; 
        letter-spacing: 0.8px;
    }
    .hero-panel p { 
        font-size: 0.85rem;
    }
    .hero-panel .btn-cta {
        padding: 12px 24px;
        font-size: 0.85rem;
    }
}
    </style>

<!-- Functionality Section -->
<section class="functionality-section">
    <div class="container">
        <div class="functionality-layout">
            <div class="functionality-content">
                <h2>Functionality<br>meets perfection</h2>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse massa libero, mattis vulputat id. Egestas adipiscing placerat eleifend a nascetur. Mattis proin enim, nam porttitor vitae.</p>
            </div>
            
            <div class="functionality-stats">
                <div class="stat-item">
                    <div class="stat-header">
                        <h3>Creativity</h3>
                        <span class="progress-percent">72 %</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress" style="width: 72%"></div>
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-header">
                        <h3>Advertising</h3>
                        <span class="progress-percent">84 %</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress" style="width: 84%"></div>
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-header">
                        <h3>Design</h3>
                        <span class="progress-percent">72 %</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress" style="width: 72%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Blog Posts Preview -->
<section class="blog-preview about-blog">
    <div class="container">
        <div class="blog-header">
            <h2 class="section-title">last blog post</h2>
            <div class="blog-nav">
                <button class="blog-nav-btn"><i class="fa fa-chevron-left"></i></button>
                <button class="blog-nav-btn"><i class="fa fa-chevron-right"></i></button>
            </div>
        </div>
        
        <div class="blog-grid">
            <div class="blog-card">
                <div class="blog-image">
                    <img src="images/Sofa/sofa5.png" alt="Living Room Design">
                </div>
                <div class="blog-date">Sep 26, 2022</div>
                <h3>Paint your office in natural colors only</h3>
                <a href="#" class="read-more">Read more</a>
            </div>
            
            <div class="blog-card">
                <div class="blog-image">
                    <img src="images/Sofa/sofa6.png" alt="Office Design">
                </div>
                <div class="blog-date">Sep 26, 2022</div>
                <h3>Paint your office in natural colors only</h3>
                <a href="#" class="read-more">Read more</a>
            </div>
            
            <div class="blog-card">
                <div class="blog-image">
                    <img src="images/Sofa/sofa8.png" alt="Interior Design">
                </div>
                <div class="blog-date">Sep 26, 2022</div>
                <h3>Paint your office in natural colors only</h3>
                <a href="#" class="read-more">Read more</a>
            </div>
        </div>
    </div>
</section>

<script>
</script>

<?php include 'includes/footer.php'; ?>