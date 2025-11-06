// Update quantity
document.querySelectorAll('.quantity-btn').forEach(button => {
    button.addEventListener('click', function() {
        const cartItem = this.closest('.cart-item');
        const quantityInput = cartItem.querySelector('.quantity-input');
        let quantity = parseInt(quantityInput.value);
        const cartId = cartItem.dataset.cartId;

        if (this.classList.contains('plus')) {
            quantity++;
        } else if (this.classList.contains('minus') && quantity > 1) {
            quantity--;
        }

        quantityInput.value = quantity;
        updateCartItem(cartId, quantity);
    });
});

// Remove item
document.querySelectorAll('.remove-item').forEach(button => {
    button.addEventListener('click', function() {
        const cartId = this.dataset.cartId;
        removeCartItem(cartId);
    });
});

// Apply coupon
document.getElementById('applyCoupon')?.addEventListener('click', function() {
    const couponCode = document.getElementById('couponCode').value;
    applyCoupon(couponCode);
});

// Checkout
document.getElementById('checkoutBtn')?.addEventListener('click', function() {
    window.location.href = 'checkout.php';
});

function updateCartItem(cartId, quantity) {
    fetch('update_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ cart_id: cartId, quantity: quantity })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload to update totals
        }
    });
}

function removeCartItem(cartId) {
    if (confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
        fetch('remove_from_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ cart_id: cartId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

function applyCoupon(couponCode) {
    fetch('apply_coupon.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ coupon_code: couponCode })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('discountAmount').textContent = '-$' + data.discount.toFixed(2);
            document.getElementById('finalTotal').textContent = '$' + data.final_total.toFixed(2);
            alert('Áp dụng mã giảm giá thành công!');
        } else {
            alert(data.message);
        }
    });
}