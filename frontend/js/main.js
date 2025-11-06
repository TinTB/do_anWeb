document.addEventListener('DOMContentLoaded', function() {
    // Product Database
    const products = [
        // Sofas - Category
        { id: 1, name: 'Modern Comfort Sofa', category: 'sofa', price: 750.00, image: 'images/Sofa/sofa1.png', rating: 5 },
        { id: 2, name: 'Elegant Sectional Sofa', category: 'sofa', price: 899.00, image: 'images/Sofa/sofa2.png', rating: 5 },
        { id: 3, name: 'Luxury Living Room Sofa', category: 'sofa', price: 1250.00, image: 'images/Sofa/sofa3.png', rating: 5 },
        { id: 4, name: 'Contemporary Gray Sofa', category: 'sofa', price: 680.00, image: 'images/Sofa/sofa1.png', rating: 4 },
        { id: 5, name: 'Modern Sectional Set', category: 'sofa', price: 1500.00, image: 'images/Sofa/sofa2.png', rating: 5 },
        { id: 6, name: 'Sleek Leather Sofa', category: 'sofa', price: 1100.00, image: 'images/Sofa/sofa3.png', rating: 5 },
        { id: 7, name: 'Compact Corner Sofa', category: 'sofa', price: 920.00, image: 'images/Sofa/sofa1.png', rating: 4 },
        { id: 8, name: 'Classic Family Sofa', category: 'sofa', price: 1050.00, image: 'images/Sofa/sofa2.png', rating: 5 },
        { id: 9, name: 'Modern Minimalist Sofa', category: 'sofa', price: 780.00, image: 'images/Sofa/sofa3.png', rating: 4 },
        { id: 10, name: 'Luxury Designer Sofa', category: 'sofa', price: 1800.00, image: 'images/Sofa/sofa1.png', rating: 5 },
        
        // Tables
        { id: 11, name: 'Modern Dining Table', category: 'table', price: 456.00, image: 'images/table/table1.png', rating: 5 },
        { id: 12, name: 'Contemporary Coffee Table', category: 'table', price: 299.00, image: 'images/table/table2.png', rating: 4 },
        { id: 13, name: 'Elegant Side Table', category: 'table', price: 189.00, image: 'images/table/table3.png', rating: 5 },
        { id: 14, name: 'Minimalist Work Table', category: 'table', price: 320.00, image: 'images/table/table1.png', rating: 5 },
        { id: 15, name: 'Luxury Glass Table', category: 'table', price: 550.00, image: 'images/table/table2.png', rating: 5 },
        { id: 16, name: 'Rustic Wooden Table', category: 'table', price: 420.00, image: 'images/table/table3.png', rating: 4 },
        { id: 17, name: 'Modern Console Table', category: 'table', price: 280.00, image: 'images/table/table1.png', rating: 5 },
        { id: 18, name: 'Extendable Dining Table', category: 'table', price: 680.00, image: 'images/table/table2.png', rating: 5 },
        { id: 19, name: 'Contemporary Accent Table', category: 'table', price: 350.00, image: 'images/table/table3.png', rating: 4 },
        { id: 20, name: 'Designer Nesting Tables', category: 'table', price: 380.00, image: 'images/table/table1.png', rating: 5 },
        
        // Lamps
        { id: 21, name: 'Modern Table Lamp', category: 'lamp', price: 156.00, image: 'images/lamp/lamp1.png', rating: 5 },
        { id: 22, name: 'Contemporary Floor Lamp', category: 'lamp', price: 234.00, image: 'images/lamp/lamp2.png', rating: 4 },
        { id: 23, name: 'Pendant Light Fixture', category: 'lamp', price: 189.00, image: 'images/lamp/lamp1.png', rating: 5 },
        { id: 24, name: 'Designer Table Lamp', category: 'lamp', price: 267.00, image: 'images/lamp/lamp2.png', rating: 5 },
        { id: 25, name: 'LED Floor Lamp', category: 'lamp', price: 198.00, image: 'images/lamp/lamp1.png', rating: 4 },
        { id: 26, name: 'Vintage Brass Lamp', category: 'lamp', price: 289.00, image: 'images/lamp/lamp2.png', rating: 5 },
        { id: 27, name: 'Smart Desk Lamp', category: 'lamp', price: 245.00, image: 'images/lamp/lamp1.png', rating: 5 },
        { id: 28, name: 'Arc Floor Lamp', category: 'lamp', price: 320.00, image: 'images/lamp/lamp2.png', rating: 4 },
        { id: 29, name: 'Crystal Chandelier', category: 'lamp', price: 450.00, image: 'images/lamp/lamp1.png', rating: 5 },
        { id: 30, name: 'Modern Wall Sconce', category: 'lamp', price: 178.00, image: 'images/lamp/lamp2.png', rating: 4 },
        
        // Beds
        { id: 31, name: 'Modern Queen Bed', category: 'bed', price: 899.00, image: 'images/bed/giuong1.png', rating: 5 },
        { id: 32, name: 'Luxury King Bed', category: 'bed', price: 1299.00, image: 'images/bed/giuong2.png', rating: 5 },
        { id: 33, name: 'Contemporary Twin Bed', category: 'bed', price: 599.00, image: 'images/bed/giuong1.png', rating: 4 },
        { id: 34, name: 'Modern Storage Bed', category: 'bed', price: 1050.00, image: 'images/bed/giuong2.png', rating: 5 },
        { id: 35, name: 'Designer Platform Bed', category: 'bed', price: 980.00, image: 'images/bed/giuong1.png', rating: 5 },
        { id: 36, name: 'Luxury Upholstered Bed', category: 'bed', price: 1450.00, image: 'images/bed/giuong2.png', rating: 5 },
        { id: 37, name: 'Minimalist Metal Bed', category: 'bed', price: 520.00, image: 'images/bed/giuong1.png', rating: 4 },
        { id: 38, name: 'Sleek Modern Bed', category: 'bed', price: 850.00, image: 'images/bed/giuong2.png', rating: 5 },
        { id: 39, name: 'Classic Wooden Bed', category: 'bed', price: 720.00, image: 'images/bed/giuong1.png', rating: 4 },
        { id: 40, name: 'Premium Adjustable Bed', category: 'bed', price: 1600.00, image: 'images/bed/giuong2.png', rating: 5 },
        
        // Bookshelves
        { id: 41, name: 'Modern Bookshelf', category: 'bookshelf', price: 345.00, image: 'images/bookshelf/ke1.png', rating: 4 },
        { id: 42, name: 'Contemporary Display Shelf', category: 'bookshelf', price: 289.00, image: 'images/bookshelf/ke2.png', rating: 5 },
        { id: 43, name: 'Industrial Storage Shelf', category: 'bookshelf', price: 420.00, image: 'images/bookshelf/ke1.png', rating: 5 },
        { id: 44, name: 'Minimalist Wall Shelf', category: 'bookshelf', price: 198.00, image: 'images/bookshelf/ke2.png', rating: 4 },
        { id: 45, name: 'Luxury Built-in Bookcase', category: 'bookshelf', price: 650.00, image: 'images/bookshelf/ke1.png', rating: 5 },
        { id: 46, name: 'Modern Corner Shelf', category: 'bookshelf', price: 267.00, image: 'images/bookshelf/ke2.png', rating: 5 },
        { id: 47, name: 'Floating Shelves Set', category: 'bookshelf', price: 234.00, image: 'images/bookshelf/ke1.png', rating: 4 },
        { id: 48, name: 'Designer Display Unit', category: 'bookshelf', price: 520.00, image: 'images/bookshelf/ke2.png', rating: 5 },
        { id: 49, name: 'Vertical Storage Tower', category: 'bookshelf', price: 380.00, image: 'images/bookshelf/ke1.png', rating: 5 },
        { id: 50, name: 'Premium Wooden Cabinet', category: 'bookshelf', price: 580.00, image: 'images/bookshelf/ke2.png', rating: 4 }
    ];

    const productsPerPage = 12;
    let currentPage = 1;
    let filteredProducts = [...products];

    // Function to save current page to sessionStorage
    function saveCurrentPage() {
        sessionStorage.setItem('lastShopPage', currentPage);
    }

    // Function to restore page from sessionStorage
    function restoreLastPage() {
        const lastPage = sessionStorage.getItem('lastShopPage');
        if (lastPage) {
            currentPage = parseInt(lastPage);
        }
    }

    // Function to render products
    function renderProducts() {
        const startIdx = (currentPage - 1) * productsPerPage;
        const endIdx = startIdx + productsPerPage;
        const pageProducts = filteredProducts.slice(startIdx, endIdx);
        
        const productContainer = document.getElementById('productContainer');
        if (!productContainer) return;
        
        // Get current category from the first filtered product or from URL
        let currentCategory = 'all';
        if (filteredProducts.length > 0) {
            currentCategory = filteredProducts[0].category;
        }
        
        productContainer.innerHTML = pageProducts.map(product => `
            <div class="product-card">
                <div class="product-image">
                    <a href="product.html?category=${product.category}&id=${product.id}" onclick="savProductClickInfo(${currentPage}, '${currentCategory}')">
                        <img src="${product.image}" alt="${product.name}">
                    </a>
                </div>
                <h3>${product.name}</h3>
                <div class="product-rating">
                    ${Array.from({length: 5}, (_, i) => 
                        `<i class="fa ${i < product.rating ? 'fa-star' : 'fa-star-o'}"></i>`
                    ).join('')}
                </div>
                <div class="product-price">$${product.price.toFixed(2)}</div>
            </div>
        `).join('');
        
        // Update pagination
        renderPagination();
        
        // Update results counter
        const resultsText = `Showing ${startIdx + 1}-${Math.min(endIdx, filteredProducts.length)} of ${filteredProducts.length} results`;
        const resultsDiv = document.querySelector('.shop-results');
        if (resultsDiv) resultsDiv.textContent = resultsText;
    }

    // Function to render pagination
    function renderPagination() {
        const paginationContainer = document.getElementById('paginationContainer');
        if (!paginationContainer) return;
        
        const totalPages = Math.ceil(filteredProducts.length / productsPerPage);
        let paginationHTML = '';

        // Previous button
        if (currentPage > 1) {
            paginationHTML += `<a href="#" class="prev" data-page="${currentPage - 1}"><i class="fa fa-chevron-left"></i></a>`;
        }

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === currentPage) {
                paginationHTML += `<a href="#" class="active" data-page="${i}">${i}</a>`;
            } else {
                paginationHTML += `<a href="#" data-page="${i}">${i}</a>`;
            }
        }

        // Next button
        if (currentPage < totalPages) {
            paginationHTML += `<a href="#" class="next" data-page="${currentPage + 1}"><i class="fa fa-chevron-right"></i></a>`;
        }

        paginationHTML += `<a href="#" class="view-all">view all</a>`;
        paginationContainer.innerHTML = paginationHTML;

        // Add event listeners
        paginationContainer.querySelectorAll('a[data-page]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                currentPage = parseInt(link.dataset.page);
                saveCurrentPage();
                renderProducts();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    }

    // Filter by category
    const filterLinks = document.querySelectorAll('.filter-list a[href*="?category="]');
    filterLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            const url = new URL(link.href, window.location.origin);
            const category = url.searchParams.get('category');
            if (category) {
                filteredProducts = products.filter(p => p.category === category);
                currentPage = 1;
                sessionStorage.removeItem('lastShopPage');
                sessionStorage.removeItem('lastCategory');
                renderProducts();
            }
        });
    });

    // Restore last page if returning from product detail
    restoreLastPage();
    
    // Handle page parameter from URL
    const urlParams = new URLSearchParams(window.location.search);
    const pageParam = urlParams.get('page');
    if (pageParam) {
        const pageNum = parseInt(pageParam);
        if (pageNum && pageNum > 0) {
            currentPage = pageNum;
        }
    }
    
    // Initial render
    renderProducts();

    // Product thumbnails gallery
    const thumbnails = document.querySelectorAll('.thumbnail');
    const mainImage = document.querySelector('.main-image img');
    
    if (thumbnails.length && mainImage) {
        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                // Remove active class from all thumbnails
                thumbnails.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked thumbnail
                this.classList.add('active');
                
                // Update main image
                const imgSrc = this.querySelector('img').src;
                mainImage.src = imgSrc;
            });
        });
    }
    
    // Quantity selector
    const minusBtn = document.querySelector('.quantity-btn.minus');
    const plusBtn = document.querySelector('.quantity-btn.plus');
    const quantityInput = document.querySelector('.quantity-input');
    
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
    
    // Product tabs
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    if (tabButtons.length && tabContents.length) {
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Remove active class from all tabs and content
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Add active class to current tab and content
                this.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });
    }
    
    // Mobile menu toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    
    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function() {
            mainNav.classList.toggle('show');
        });
    }
    
    // Product Detail Page
    function loadProductDetail() {
        // Get URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const productId = parseInt(urlParams.get('id'));
        const category = urlParams.get('category');
        
        // Find the product
        const product = products.find(p => p.id === productId && p.category === category);
        
        if (!product) {
            console.log('Product not found');
            return;
        }
        
        // Update page title
        document.title = `${product.name} - Soudemy`;
        
        // Update breadcrumb
        const breadcrumb = document.querySelector('.breadcrumb-list');
        if (breadcrumb) {
            const lastPage = sessionStorage.getItem('lastShopPage');
            const lastCategory = sessionStorage.getItem('lastCategory');
            let categoryLink = `shop.html?category=${category}`;
            
            if (lastPage && lastCategory) {
                categoryLink = `shop.html?category=${lastCategory}&page=${lastPage}`;
            }
            
            breadcrumb.innerHTML = `
                <li><a href="shop.html">shop</a></li>
                <li><span>/</span></li>
                <li><a href="${categoryLink}">${category}</a></li>
                <li><span>/</span></li>
                <li><span>${product.name}</span></li>
            `;
        }
        
        // Setup back button with saved page info
        const backButton = document.getElementById('backButton');
        if (backButton) {
            const lastPage = sessionStorage.getItem('lastShopPage');
            const lastCategory = sessionStorage.getItem('lastCategory');
            
            let backUrl = `shop.html?category=${category}`;
            
            if (lastPage && lastCategory) {
                backUrl = `shop.html?category=${lastCategory}&page=${lastPage}`;
            }
            
            backButton.href = backUrl;
            backButton.addEventListener('click', function(e) {
                e.preventDefault();
                // Clear saved page when clicking back
                sessionStorage.removeItem('lastShopPage');
                sessionStorage.removeItem('lastCategory');
                window.location.href = backUrl;
            });
        }
        
        // Update product gallery (main image)
        const mainImage = document.querySelector('.main-image img');
        if (mainImage) {
            mainImage.src = product.image;
            mainImage.alt = product.name;
        }
        
        // Update thumbnails
        const thumbnails = document.querySelectorAll('.gallery-thumbnails .thumbnail');
        if (thumbnails.length > 0) {
            thumbnails.forEach((thumb, index) => {
                const img = thumb.querySelector('img');
                if (img) {
                    img.src = product.image;
                    img.alt = `${product.name} - View ${index + 1}`;
                }
                if (index === 0) {
                    thumb.classList.add('active');
                } else {
                    thumb.classList.remove('active');
                }
            });
        }
        
        // Update product info
        const productTitle = document.querySelector('.product-info h1');
        if (productTitle) {
            productTitle.textContent = product.name;
        }
        
        // Update rating
        const productRating = document.querySelector('.product-info .product-rating');
        if (productRating) {
            productRating.innerHTML = Array.from({length: 5}, (_, i) => 
                `<i class="fa ${i < product.rating ? 'fa-star' : 'fa-star-o'}"></i>`
            ).join('') + `<span class="rating-count">(${product.rating} op.)</span>`;
        }
        
        // Update price
        const currentPrice = document.querySelector('.product-info .current-price');
        if (currentPrice) {
            currentPrice.textContent = `$${product.price.toFixed(2)}`;
        }
        
        // Update category meta
        const categoryMeta = document.querySelector('.product-meta .meta-item:nth-child(2)');
        if (categoryMeta) {
            categoryMeta.innerHTML = `<span>Category:</span> ${category}`;
        }
        
        // Load related products
        loadRelatedProducts(category, productId);
    }
    
    function loadRelatedProducts(category, currentProductId) {
        const relatedProducts = products.filter(p => 
            p.category === category && p.id !== currentProductId
        ).slice(0, 4);
        
        const productGrid = document.querySelector('.related-products .product-grid');
        if (productGrid && relatedProducts.length > 0) {
            productGrid.innerHTML = relatedProducts.map(product => `
                <div class="product-card">
                    <div class="product-image">
                        <a href="product.html?category=${product.category}&id=${product.id}">
                            <img src="${product.image}" alt="${product.name}">
                        </a>
                    </div>
                    <h3>${product.name}</h3>
                    <div class="product-rating">
                        ${Array.from({length: 5}, (_, i) => 
                            `<i class="fa ${i < product.rating ? 'fa-star' : 'fa-star-o'}"></i>`
                        ).join('')}
                    </div>
                    <div class="product-price">$${product.price.toFixed(2)}</div>
                </div>
            `).join('');
        }
    }
    
    // Load product detail if on product page
    if (document.querySelector('.product-detail')) {
        loadProductDetail();
    }
    
    // Function to save product click information
    window.savProductClickInfo = function(page, category) {
        sessionStorage.setItem('lastShopPage', page);
        sessionStorage.setItem('lastCategory', category);
    };

});
// Search Toggle Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchToggle = document.querySelector('.search-toggle');
    const searchBox = document.querySelector('.search-box');
    
    if (searchToggle && searchBox) {
        searchToggle.addEventListener('click', function(e) {
            e.preventDefault();
            searchBox.classList.toggle('active');
            
            // Focus vào input khi mở
            if (searchBox.classList.contains('active')) {
                setTimeout(() => {
                    const searchInput = searchBox.querySelector('input');
                    if (searchInput) searchInput.focus();
                }, 100);
            }
        });
        
        // Đóng search box khi click ra ngoài
        document.addEventListener('click', function(e) {
            if (!searchToggle.contains(e.target) && !searchBox.contains(e.target)) {
                searchBox.classList.remove('active');
            }
        });
    }
    
    // Mobile menu toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    
    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function() {
            mainNav.classList.toggle('active');
        });
    }
});