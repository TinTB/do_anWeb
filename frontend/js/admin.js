// --- NEW: base path fallbacks to support backend location ---
const BACKEND_BASE = (window.BACKEND_BASE && window.BACKEND_BASE.endsWith('/')) 
    ? window.BACKEND_BASE 
    : (window.BACKEND_BASE ? window.BACKEND_BASE + '/' : '/Do_an/backend/');
const FRONTEND_BASE = (window.APP_BASE && window.APP_BASE.endsWith('/')) 
    ? window.APP_BASE 
    : (window.APP_BASE ? window.APP_BASE + '/' : '/Do_an/frontend/');

// Admin Tab Navigation
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

// Product Management
document.getElementById('addProductBtn')?.addEventListener('click', function() {
    openProductModal();
});

document.querySelectorAll('.edit-product').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.dataset.productId;
        openProductModal(productId);
    });
});

document.querySelectorAll('.delete-product').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.dataset.productId;
        deleteProduct(productId);
    });
});

// Product Modal
const productModal = document.getElementById('productModal');
const productForm = document.getElementById('productForm');

function openProductModal(productId = null) {
    const modalTitle = document.getElementById('productModalTitle');
    
    if (productId) {
        modalTitle.textContent = 'Chỉnh sửa sản phẩm';
        // Load product data and populate form
        loadProductData(productId);
    } else {
        modalTitle.textContent = 'Thêm sản phẩm mới';
        productForm.reset();
        delete productForm.dataset.productId;
    }
    
    productModal.style.display = 'block';
}

function loadProductData(productId) {
    // For future implementation if needed
}

function deleteProduct(productId) {
    if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('id', productId);
    formData.append('action', 'delete');
    
    fetch(BACKEND_BASE + 'admin_products.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showSuccessNotification(data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showErrorNotification('Lỗi: ' + (data.message || 'Xóa thất bại'));
        }
    })
    .catch(err => {
        console.error(err);
        showErrorNotification('Lỗi: ' + err.message);
    });
}

// Product Form Submission
if (productForm) {
    productForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate form before submit
        const name = this.querySelector('[name="name"]').value.trim();
        const category = this.querySelector('[name="category"]').value.trim();
        const price = parseFloat(this.querySelector('[name="price"]').value);
        const stock = parseInt(this.querySelector('[name="stock"]').value);
        const image = this.querySelector('[name="image"]').files[0];
        
        // Client-side validation
        if (!name) {
            showErrorNotification('Vui lòng nhập tên sản phẩm');
            return;
        }
        if (!category) {
            showErrorNotification('Vui lòng chọn danh mục');
            return;
        }
        if (!price || price <= 0) {
            showErrorNotification('Giá sản phẩm phải lớn hơn 0');
            return;
        }
        if (stock < 0) {
            showErrorNotification('Tồn kho không được âm');
            return;
        }
        if (!image) {
            showErrorNotification('Vui lòng chọn hình ảnh sản phẩm');
            return;
        }
        
        // Validate image
        const validImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!validImageTypes.includes(image.type)) {
            showErrorNotification('Chỉ chấp nhận hình ảnh JPG, PNG, GIF hoặc WebP');
            return;
        }
        if (image.size > 5 * 1024 * 1024) { // 5MB
            showErrorNotification('Hình ảnh không được vượt quá 5MB');
            return;
        }
        
        const productId = this.dataset.productId;
        const formData = new FormData(this);
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = 'Đang lưu...';
        submitBtn.disabled = true;
        
        fetch(BACKEND_BASE + 'admin_products.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                productModal.style.display = 'none';
                showSuccessNotification(data.message || 'Sản phẩm đã được lưu thành công!');
                
                setTimeout(() => {
                    // Chuyển hướng đến shop.php để xem sản phẩm mới
                    window.location.href = FRONTEND_BASE + 'shop.php';
                }, 1500);
            } else {
                showErrorNotification('Lỗi: ' + (data.message || 'Lưu thất bại'));
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            showErrorNotification('Lỗi: ' + err.message);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
}

// Close modal
document.querySelectorAll('.modal .close').forEach(closeBtn => {
    closeBtn.addEventListener('click', function() {
        this.closest('.modal').style.display = 'none';
    });
});

window.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
});

// Notification System
function showSuccessNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification notification-success';
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fa fa-check-circle"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Trigger animation
    setTimeout(() => notification.classList.add('show'), 10);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function showErrorNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification notification-error';
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fa fa-exclamation-circle"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Trigger animation
    setTimeout(() => notification.classList.add('show'), 10);
    
    // Remove after 4 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}
// Coupon Management - Edit and Delete
document.querySelectorAll('.edit-coupon').forEach(button => {
    button.addEventListener('click', function() {
        const couponId = this.dataset.couponId;
        openCouponModal(couponId);
    });
});

document.querySelectorAll('.delete-coupon').forEach(button => {
    button.addEventListener('click', function() {
        const couponId = this.dataset.couponId;
        deleteCoupon(couponId);
    });
});

function openCouponModal(couponId = null) {
    const modalTitle = document.getElementById('couponModalTitle');
    
    if (couponId) {
        modalTitle.textContent = 'Edit Coupon';
        loadCouponData(couponId);
    } else {
        modalTitle.textContent = 'Add Coupon';
        document.getElementById('couponForm').reset();
        document.getElementById('couponForm').dataset.couponId = '';
    }
    
    document.getElementById('couponModal').style.display = 'block';
}

function loadCouponData(couponId) {
    fetch(`api/coupons.php?action=get&id=${couponId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const coupon = data.coupon;
                const form = document.getElementById('couponForm');
                
                form.querySelector('[name="code"]').value = coupon.code || '';
                form.querySelector('[name="discount_type"]').value = coupon.discount_type || 'percentage';
                form.querySelector('[name="discount_value"]').value = coupon.discount_value || '';
                form.querySelector('[name="min_order_amount"]').value = coupon.min_order_amount || '';
                form.querySelector('[name="max_discount_amount"]').value = coupon.max_discount_amount || '';
                form.querySelector('[name="usage_limit"]').value = coupon.usage_limit || '';
                
                // Format dates for datetime-local input
                if (coupon.start_date) {
                    form.querySelector('[name="start_date"]').value = coupon.start_date.replace(' ', 'T');
                }
                if (coupon.end_date) {
                    form.querySelector('[name="end_date"]').value = coupon.end_date.replace(' ', 'T');
                }
                
                form.querySelector('[name="is_active"]').checked = coupon.is_active == 1;
                
                // Store coupon ID for update
                form.dataset.couponId = couponId;
            }
        });
}

function deleteCoupon(couponId) {
    if (confirm('Are you sure you want to delete this coupon?')) {
        fetch('api/coupons.php?action=delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${couponId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Coupon deleted successfully');
                location.reload();
            } else {
                alert('Failed to delete coupon: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error deleting coupon:', error);
            alert('Error deleting coupon');
        });
    }
}
// Coupon Management - Fixed version
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - initializing coupon handlers');
    
    const addCouponBtn = document.getElementById('addCouponBtn');
    const couponModal = document.getElementById('couponModal');
    
    if (addCouponBtn) {
        console.log('Add coupon button found');
        addCouponBtn.addEventListener('click', function() {
            console.log('Add Coupon button clicked');
            openCouponModal();
        });
    } else {
        console.log('Add coupon button NOT found');
    }

    // Close modal handlers
    document.querySelectorAll('.modal .close').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            console.log('Close button clicked');
            this.closest('.modal').style.display = 'none';
        });
    });

    // Close modal when clicking outside
    if (couponModal) {
        window.addEventListener('click', function(e) {
            if (e.target === couponModal) {
                console.log('Modal background clicked');
                couponModal.style.display = 'none';
            }
        });
    }

    // Coupon form submission
    const couponForm = document.getElementById('couponForm');
    if (couponForm) {
        couponForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Coupon form submitted');
            submitCouponForm(this);
        });
    }

    // Edit and Delete buttons
    document.querySelectorAll('.edit-coupon').forEach(button => {
        button.addEventListener('click', function() {
            const couponId = this.dataset.couponId;
            console.log('Edit coupon:', couponId);
            openCouponModal(couponId);
        });
    });

    document.querySelectorAll('.delete-coupon').forEach(button => {
        button.addEventListener('click', function() {
            const couponId = this.dataset.couponId;
            console.log('Delete coupon:', couponId);
            deleteCoupon(couponId);
        });
    });
});

function openCouponModal(couponId = null) {
    console.log('Opening coupon modal for ID:', couponId);
    const modal = document.getElementById('couponModal');
    const modalTitle = document.getElementById('couponModalTitle');
    
    if (!modal) {
        console.error('Coupon modal not found!');
        return;
    }
    
    if (couponId) {
        modalTitle.textContent = 'Edit Coupon';
        loadCouponData(couponId);
    } else {
        modalTitle.textContent = 'Add Coupon';
        document.getElementById('couponForm').reset();
        delete document.getElementById('couponForm').dataset.couponId;
    }
    
    modal.style.display = 'block';
}

function submitCouponForm(form) {
    const formData = new FormData(form);
    const couponId = form.dataset.couponId;
    
    let url = 'api/coupons.php?action=create';
    let method = 'POST';
    
    if (couponId) {
        url = 'api/coupons.php?action=update';
        formData.append('id', couponId);
    }
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = 'Saving...';
    submitBtn.disabled = true;
    
    fetch(url, {
        method: method,
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Coupon ' + (couponId ? 'updated' : 'created') + ' successfully!');
            document.getElementById('couponModal').style.display = 'none';
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error ' + (couponId ? 'updating' : 'creating') + ' coupon');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function loadCouponData(couponId) {
    fetch(`api/coupons.php?action=get&id=${couponId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const coupon = data.coupon;
                const form = document.getElementById('couponForm');
                
                form.querySelector('[name="code"]').value = coupon.code || '';
                form.querySelector('[name="discount_type"]').value = coupon.discount_type || 'percentage';
                form.querySelector('[name="discount_value"]').value = coupon.discount_value || '';
                form.querySelector('[name="min_order_amount"]').value = coupon.min_order_amount || '';
                form.querySelector('[name="max_discount_amount"]').value = coupon.max_discount_amount || '';
                form.querySelector('[name="usage_limit"]').value = coupon.usage_limit || '';
                
                // Format dates for datetime-local input
                if (coupon.start_date) {
                    const startDate = new Date(coupon.start_date);
                    form.querySelector('[name="start_date"]').value = startDate.toISOString().slice(0, 16);
                }
                if (coupon.end_date) {
                    const endDate = new Date(coupon.end_date);
                    form.querySelector('[name="end_date"]').value = endDate.toISOString().slice(0, 16);
                }
                
                form.querySelector('[name="is_active"]').checked = coupon.is_active == 1;
                form.dataset.couponId = couponId;
            }
        })
        .catch(error => {
            console.error('Error loading coupon data:', error);
            alert('Error loading coupon data');
        });
}

function deleteCoupon(couponId) {
    if (confirm('Are you sure you want to delete this coupon?')) {
        fetch('api/coupons.php?action=delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${couponId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Coupon deleted successfully');
                location.reload();
            } else {
                alert('Failed to delete coupon: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error deleting coupon:', error);
            alert('Error deleting coupon');
        });
    }
}