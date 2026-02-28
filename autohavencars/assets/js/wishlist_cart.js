// Wishlist and Cart JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Wishlist buttons
    document.querySelectorAll('.wishlist-btn, .wishlist-btn-detail').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const carId = this.getAttribute('data-car-id');
            const isActive = this.classList.contains('active');
            toggleWishlist(carId, this, !isActive);
        });
    });
    
    // Cart buttons
    document.querySelectorAll('.cart-btn, .cart-btn-detail').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const carId = this.getAttribute('data-car-id');
            const isActive = this.classList.contains('active');
            toggleCart(carId, this, !isActive);
        });
    });
    
    // Add to cart from wishlist
    document.querySelectorAll('.add-cart-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const carId = this.getAttribute('data-car-id');
            toggleCart(carId, null, true);
        });
    });
    
    // Add to wishlist from cart
    document.querySelectorAll('.add-wishlist-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const carId = this.getAttribute('data-car-id');
            toggleWishlist(carId, null, true);
        });
    });
    
    // Remove buttons
    document.querySelectorAll('.remove-wishlist').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const carId = this.getAttribute('data-car-id');
            removeFromWishlist(carId);
        });
    });
    
    document.querySelectorAll('.cart-remove-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const carId = this.getAttribute('data-car-id');
            removeFromCart(carId);
        });
    });
});

function toggleWishlist(carId, button, add) {
    const formData = new FormData();
    formData.append('action', add ? 'add' : 'remove');
    formData.append('car_id', carId);
    
    fetch('api/wishlist.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (button) {
                if (data.in_wishlist) {
                    button.classList.add('active');
                    button.setAttribute('title', 'Remove from wishlist');
                    if (button.querySelector('span')) {
                        button.querySelector('span').textContent = 'Remove from Wishlist';
                    }
                } else {
                    button.classList.remove('active');
                    button.setAttribute('title', 'Add to wishlist');
                    if (button.querySelector('span')) {
                        button.querySelector('span').textContent = 'Add to Wishlist';
                    }
                }
            }
            updateWishlistBadge(data.count);
            showNotification(data.message, 'success');
            if (!data.in_wishlist && window.location.pathname.includes('wishlist.php')) {
                setTimeout(() => location.reload(), 500);
            }
        } else {
            showNotification(data.message || 'Error updating wishlist', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating wishlist', 'error');
    });
}

function toggleCart(carId, button, add) {
    const formData = new FormData();
    formData.append('action', add ? 'add' : 'remove');
    formData.append('car_id', carId);
    
    fetch('api/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (button) {
                if (data.in_cart) {
                    button.classList.add('active');
                    button.setAttribute('title', 'Remove from cart');
                    if (button.querySelector('span')) {
                        button.querySelector('span').textContent = 'Remove from Cart';
                    }
                } else {
                    button.classList.remove('active');
                    button.setAttribute('title', 'Add to cart');
                    if (button.querySelector('span')) {
                        button.querySelector('span').textContent = 'Add to Cart';
                    }
                }
            }
            updateCartBadge(data.count);
            showNotification(data.message, 'success');
            if (!data.in_cart && window.location.pathname.includes('cart.php')) {
                setTimeout(() => location.reload(), 500);
            }
        } else {
            showNotification(data.message || 'Error updating cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating cart', 'error');
    });
}

function removeFromWishlist(carId) {
    toggleWishlist(carId, null, false);
}

function removeFromCart(carId) {
    toggleCart(carId, null, false);
}

function updateWishlistBadge(count) {
    const badge = document.querySelector('.nav-icon-link[href="wishlist.php"] .nav-badge');
    if (count > 0) {
        if (badge) {
            badge.textContent = count;
        } else {
            const link = document.querySelector('.nav-icon-link[href="wishlist.php"]');
            if (link) {
                const newBadge = document.createElement('span');
                newBadge.className = 'nav-badge';
                newBadge.textContent = count;
                link.appendChild(newBadge);
            }
        }
    } else {
        if (badge) {
            badge.remove();
        }
    }
}

function updateCartBadge(count) {
    const badge = document.querySelector('.nav-icon-link[href="cart.php"] .nav-badge');
    if (count > 0) {
        if (badge) {
            badge.textContent = count;
        } else {
            const link = document.querySelector('.nav-icon-link[href="cart.php"]');
            if (link) {
                const newBadge = document.createElement('span');
                newBadge.className = 'nav-badge';
                newBadge.textContent = count;
                link.appendChild(newBadge);
            }
        }
    } else {
        if (badge) {
            badge.remove();
        }
    }
}

function showNotification(message, type) {
    const existing = document.querySelector('.notification');
    if (existing) {
        existing.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}




