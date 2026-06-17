// Arts Store — Main JavaScript

// Scroll to top button
window.addEventListener('scroll', function() {
    var btn = document.getElementById('scrollTop');
    if(btn) btn.classList.toggle('show', window.scrollY > 300);
});

// Add to cart via AJAX (fallback: form submit)
function addToCart(productId, qty) {
    qty = qty || 1;
    fetch('add-to-cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + productId + '&qty=' + qty
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if(data.success) {
            showToast('Added to cart!', 'success');
            // Update badge
            var badges = document.querySelectorAll('.nav-icon .badge');
            badges.forEach(function(b) {
                if(b.closest('a[href*="cart"]')) b.textContent = data.cart_count;
            });
        }
    })
    .catch(function() {
        // Fallback
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = 'cart.php';
        form.innerHTML = '<input name="product_id" value="'+productId+'"/><input name="qty" value="'+qty+'"/>';
        document.body.appendChild(form);
        form.submit();
    });
}

// Toggle wishlist
function toggleWishlist(btn, productId) {
    btn.classList.toggle('active');
    fetch('wishlist-toggle.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + productId
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        showToast(data.added ? 'Added to wishlist!' : 'Removed from wishlist', data.added ? 'success' : 'info');
    })
    .catch(function() {});
}

// Toast notification
function showToast(message, type) {
    var existing = document.getElementById('arts-toast');
    if(existing) existing.remove();

    var toast = document.createElement('div');
    toast.id = 'arts-toast';
    toast.style.cssText = [
        'position:fixed',
        'bottom:80px',
        'right:30px',
        'background:' + (type==='success' ? '#7B5EA7' : type==='error' ? '#e74c3c' : '#444'),
        'color:#fff',
        'padding:14px 24px',
        'border-radius:4px',
        'font-family:"Josefin Sans",sans-serif',
        'font-size:13px',
        'font-weight:400',
        'letter-spacing:.05em',
        'z-index:9999',
        'box-shadow:0 4px 20px rgba(0,0,0,0.15)',
        'transition:opacity .3s'
    ].join(';');
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(function() {
        toast.style.opacity = '0';
        setTimeout(function() { toast.remove(); }, 300);
    }, 2500);
}

// Lazy load images
if('IntersectionObserver' in window) {
    var imgs = document.querySelectorAll('img[data-src]');
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(e) {
            if(e.isIntersecting) {
                e.target.src = e.target.dataset.src;
                observer.unobserve(e.target);
            }
        });
    });
    imgs.forEach(function(img) { observer.observe(img); });
}

// Mobile nav toggle
var menuToggle = document.getElementById('menuToggle');
var navMenu    = document.querySelector('.nav-menu');
if(menuToggle && navMenu) {
    menuToggle.addEventListener('click', function() {
        navMenu.style.display = navMenu.style.display === 'flex' ? 'none' : 'flex';
    });
}

// Product card hover — show add-to-cart quick action
document.querySelectorAll('.product-card').forEach(function(card) {
    card.addEventListener('mouseenter', function() {
        var actions = this.querySelector('.product-actions');
        if(actions) {
            actions.style.opacity = '1';
            actions.style.transform = 'translateY(0)';
        }
    });
    card.addEventListener('mouseleave', function() {
        var actions = this.querySelector('.product-actions');
        if(actions) {
            actions.style.opacity = '0';
            actions.style.transform = 'translateY(10px)';
        }
    });
});
