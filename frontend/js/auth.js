// Login Modal
const loginModal = document.getElementById('loginModal');
const loginToggle = document.querySelector('.login-toggle');
const closeModal = document.querySelector('.close');

if (loginToggle) {
    loginToggle.addEventListener('click', () => {
        loginModal.style.display = 'block';
    });
}

if (closeModal) {
    closeModal.addEventListener('click', () => {
        loginModal.style.display = 'none';
    });
}

window.addEventListener('click', (e) => {
    if (e.target === loginModal) {
        loginModal.style.display = 'none';
    }
});

// Search Toggle
const searchToggle = document.querySelector('.search-toggle');
if (searchToggle) {
    searchToggle.addEventListener('click', () => {
        searchToggle.classList.toggle('active');
    });
}

// Add to Cart
document.querySelectorAll('.add-to-cart-btn').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.dataset.productId;
        
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ product_id: productId, quantity: 1 })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update cart count
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = data.cart_count;
                }
                
                // Show success message
                alert('Product added to cart!');
            } else {
                alert('Error adding product to cart');
            }
        });
    });
});

// AJAX login: gửi cookies (credentials) để server set session cookie và frontend reload
document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('loginForm');
    const loginModal = document.getElementById('loginModal');
    const loginError = document.getElementById('loginError');

    loginForm?.addEventListener('submit', function (e) {
        e.preventDefault();
        loginError.style.display = 'none';
        const email = document.getElementById('loginEmail').value.trim();
        const password = document.getElementById('loginPassword').value;

        fetch('/Do_an/frontend/includes/process_login_ajax.php', {
            method: 'POST',
            credentials: 'same-origin', // <= gửi/nhận cookie session
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email, password: password })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // reload để header.php đọc $_SESSION và hiển thị tên user
                window.location.reload();
            } else {
                loginError.textContent = data.message || 'Đăng nhập thất bại';
                loginError.style.display = 'block';
            }
        })
        .catch(err => {
            console.error(err);
            loginError.textContent = 'Lỗi kết nối';
            loginError.style.display = 'block';
        });
    });

    // existing modal open/close handlers remain
});

