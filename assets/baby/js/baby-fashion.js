/**
 * Baby Fashion BD - Client-side Shopping Interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    // CSRF Token setup for AJAX
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // =========================================================================
    // Toast Notification System
    // =========================================================================
    function showToast(message, type = 'success') {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toast-msg toast-${type}`;
        toast.innerHTML = `<span>${type === 'success' ? '✓' : 'ℹ'}</span> <span>${message}</span>`;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3200);
    }
    window.showToast = showToast;

    // =========================================================================
    // Cart Drawer Controls & Dynamic Rendering
    // =========================================================================
    const cartDrawer = document.getElementById('cartDrawer');
    const cartDrawerOverlay = document.getElementById('cartDrawerOverlay');
    const cartToggleBtns = document.querySelectorAll('.cart-toggle-trigger');
    const cartCloseBtn = document.getElementById('cartCloseBtn');

    function openCartDrawer() {
        if (cartDrawer && cartDrawerOverlay) {
            cartDrawer.classList.add('active');
            cartDrawerOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            fetchCartData();
        }
    }

    function closeCartDrawer() {
        if (cartDrawer && cartDrawerOverlay) {
            cartDrawer.classList.remove('active');
            cartDrawerOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    cartToggleBtns.forEach(btn => btn.addEventListener('click', (e) => {
        e.preventDefault();
        openCartDrawer();
    }));

    if (cartCloseBtn) cartCloseBtn.addEventListener('click', closeCartDrawer);
    if (cartDrawerOverlay) cartDrawerOverlay.addEventListener('click', closeCartDrawer);

    window.openCartDrawer = openCartDrawer;
    window.closeCartDrawer = closeCartDrawer;

    // Render Cart State
    function renderCart(summary) {
        // Update header badges
        document.querySelectorAll('.cart-badge-count').forEach(el => {
            el.textContent = summary.item_count;
        });

        // Update Drawer Elements
        const drawerItems = document.getElementById('drawerItems');
        const drawerSubtotal = document.getElementById('drawerSubtotal');
        const shippingProgressText = document.getElementById('shippingProgressText');
        const shippingProgressFill = document.getElementById('shippingProgressFill');

        if (drawerSubtotal) {
            drawerSubtotal.textContent = summary.subtotal_formatted;
        }

        if (shippingProgressText && shippingProgressFill && summary.free_shipping) {
            shippingProgressText.textContent = summary.free_shipping.message;
            shippingProgressFill.style.width = `${summary.free_shipping.progress}%`;
        }

        if (drawerItems) {
            if (!summary.items || summary.items.length === 0) {
                drawerItems.innerHTML = `
                    <div class="drawer-empty-state">
                        <div class="drawer-empty-icon">🛍️</div>
                        <h4>Your shopping bag is empty</h4>
                        <p style="color:#888; font-size:0.88rem; margin: 0.5rem 0 1.2rem;">Discover the cutest outfits for your little ones!</p>
                        <a href="/shop" class="btn-primary" style="font-size:0.88rem; padding: 0.65rem 1.4rem;" onclick="closeCartDrawer()">Start Shopping</a>
                    </div>
                `;
                const checkoutBtn = document.getElementById('drawerCheckoutBtn');
                if (checkoutBtn) checkoutBtn.style.display = 'none';
            } else {
                const checkoutBtn = document.getElementById('drawerCheckoutBtn');
                if (checkoutBtn) checkoutBtn.style.display = 'flex';

                drawerItems.innerHTML = summary.items.map(item => `
                    <div class="cart-item" data-cart-key="${item.id}">
                        <img src="${item.image}" alt="${item.title}" class="cart-item-img">
                        <div class="cart-item-details">
                            <h4 class="cart-item-title">${item.title}</h4>
                            <div class="cart-item-variant">Size: <strong>${item.size}</strong></div>
                            <div class="cart-item-price-row">
                                <div class="qty-control">
                                    <button class="qty-btn" onclick="updateCartItemQty('${item.id}', ${item.quantity - 1})">-</button>
                                    <span class="qty-val">${item.quantity}</span>
                                    <button class="qty-btn" onclick="updateCartItemQty('${item.id}', ${item.quantity + 1})">+</button>
                                </div>
                                <div style="font-weight: 800; color: var(--color-primary);">৳ ${(item.price * item.quantity).toLocaleString()}</div>
                                <button class="cart-item-remove" onclick="removeCartItem('${item.id}')">✕</button>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        }
    }

    async function fetchCartData() {
        try {
            const res = await fetch('/cart/json');
            const data = await res.json();
            renderCart(data);
        } catch (err) {
            console.error('Error fetching cart:', err);
        }
    }

    window.addToCart = async function(productId, size = null, color = null, quantity = 1) {
        try {
            const res = await fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ product_id: productId, size, color, quantity })
            });
            const data = await res.json();
            if (data.success) {
                renderCart(data.cart);
                showToast(data.message || 'Added to bag!');
                openCartDrawer();
            } else {
                showToast(data.message || 'Could not add item', 'error');
            }
        } catch (err) {
            showToast('Error adding to cart', 'error');
        }
    };

    window.updateCartItemQty = async function(cartKey, quantity) {
        try {
            const res = await fetch('/cart/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ cart_key: cartKey, quantity })
            });
            const data = await res.json();
            if (data.success) {
                renderCart(data.cart);
            }
        } catch (err) {
            console.error(err);
        }
    };

    window.removeCartItem = async function(cartKey) {
        try {
            const res = await fetch('/cart/remove', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ cart_key: cartKey })
            });
            const data = await res.json();
            if (data.success) {
                renderCart(data.cart);
                showToast('Item removed');
            }
        } catch (err) {
            console.error(err);
        }
    };

    // =========================================================================
    // Quick View Modal
    // =========================================================================
    const quickViewModalOverlay = document.getElementById('quickViewModalOverlay');
    const quickViewModalBody = document.getElementById('quickViewModalBody');
    const quickViewCloseBtn = document.getElementById('quickViewCloseBtn');

    window.openQuickView = async function(slug) {
        if (!quickViewModalOverlay || !quickViewModalBody) return;

        quickViewModalBody.innerHTML = '<div style="text-align:center; padding: 3rem;">Loading product details...</div>';
        quickViewModalOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';

        try {
            const res = await fetch(`/api/quick-view/${slug}`);
            const data = await res.json();
            if (data.success) {
                const prod = data.product;
                quickViewModalBody.innerHTML = `
                    <div class="modal-product-grid">
                        <div>
                            <div class="modal-gallery-main">
                                <img id="qvMainImg" src="${prod.primary_image}" alt="${prod.title}">
                            </div>
                            <div class="gallery-thumbs" style="margin-top: 0.8rem;">
                                ${(prod.gallery || [prod.primary_image]).map(img => `
                                    <div class="gallery-thumb" onclick="document.getElementById('qvMainImg').src='${img}'">
                                        <img src="${img}" alt="">
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                        <div>
                            <span class="product-category">${prod.category_name}</span>
                            <h2 style="font-family: var(--font-heading); font-size: 1.4rem; margin: 0.3rem 0 0.8rem;">${prod.title}</h2>
                            <div class="product-price-wrap" style="margin-bottom: 1rem;">
                                <span class="current-price" style="font-size: 1.5rem;">৳ ${prod.price.toLocaleString()}</span>
                                ${prod.original_price ? `<span class="original-price">৳ ${prod.original_price.toLocaleString()}</span>` : ''}
                                ${prod.discount_percent ? `<span class="discount-tag">${prod.discount_percent}% OFF</span>` : ''}
                            </div>
                            <p style="font-size: 0.88rem; color: var(--color-text-muted); margin-bottom: 1.2rem;">${prod.short_description || ''}</p>

                            <div class="option-group">
                                <label class="option-label">Select Age/Size:</label>
                                <div class="size-selector-pills" id="qvSizes">
                                    ${(prod.sizes || []).map((s, idx) => `
                                        <button type="button" class="size-pill-lg ${idx === 0 ? 'selected' : ''}" onclick="selectQvSize(this, '${s}')">${s}</button>
                                    `).join('')}
                                </div>
                            </div>

                            <div style="display: flex; gap: 0.8rem; margin-top: 1.5rem;">
                                <button type="button" class="btn-primary" style="flex:1; justify-content:center;" onclick="addQvToCart(${prod.id})">
                                    🛒 Add to Bag
                                </button>
                                <a href="/checkout?direct_product_id=${prod.id}" class="btn-primary" style="background:#26A69A; flex:1; justify-content:center;">
                                    ⚡ Order Now (COD)
                                </a>
                            </div>

                            <div style="margin-top: 1rem; text-align: center;">
                                <a href="/product/${prod.slug}" style="font-size: 0.85rem; color: var(--color-primary); text-decoration: underline; font-weight: 600;">View Full Product Details →</a>
                            </div>
                        </div>
                    </div>
                `;
            }
        } catch (err) {
            quickViewModalBody.innerHTML = '<div style="text-align:center; padding: 2rem; color:red;">Failed to load product.</div>';
        }
    };

    let selectedQvSize = '';
    window.selectQvSize = function(btn, size) {
        document.querySelectorAll('#qvSizes .size-pill-lg').forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');
        selectedQvSize = size;
    };

    window.addQvToCart = function(productId) {
        const size = selectedQvSize || document.querySelector('#qvSizes .size-pill-lg.selected')?.textContent || 'Standard';
        addToCart(productId, size, null, 1);
        if (quickViewModalOverlay) {
            quickViewModalOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    };

    function closeQuickView() {
        if (quickViewModalOverlay) {
            quickViewModalOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    if (quickViewCloseBtn) quickViewCloseBtn.addEventListener('click', closeQuickView);
    if (quickViewModalOverlay) {
        quickViewModalOverlay.addEventListener('click', (e) => {
            if (e.target === quickViewModalOverlay) closeQuickView();
        });
    }

    // =========================================================================
    // Predictive Live Search
    // =========================================================================
    const searchInputs = document.querySelectorAll('.live-search-input');
    const searchDropdown = document.getElementById('searchResultsDropdown');

    let searchTimeout = null;
    searchInputs.forEach(input => {
        input.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();

            if (query.length < 2) {
                if (searchDropdown) searchDropdown.classList.remove('active');
                return;
            }

            searchTimeout = setTimeout(async () => {
                try {
                    const res = await fetch(`/api/search?q=${encodeURIComponent(query)}`);
                    const data = await res.json();
                    if (searchDropdown) {
                        if (data.results && data.results.length > 0) {
                            searchDropdown.innerHTML = data.results.map(p => `
                                <a href="/product/${p.slug}" class="search-result-item">
                                    <img src="${p.primary_image}" alt="${p.title}" class="search-result-thumb">
                                    <div class="search-result-info">
                                        <h4>${p.title}</h4>
                                        <div class="price">৳ ${p.price.toLocaleString()}</div>
                                    </div>
                                </a>
                            `).join('') + `
                                <a href="/shop?q=${encodeURIComponent(query)}" style="display:block; text-align:center; padding: 0.6rem; font-size:0.85rem; font-weight:700; color:var(--color-primary); background:var(--color-bg-warm);">
                                    View all ${data.count} results →
                                </a>
                            `;
                            searchDropdown.classList.add('active');
                        } else {
                            searchDropdown.innerHTML = `<div style="padding: 1rem; text-align: center; color:#888; font-size:0.88rem;">No products found for "${query}"</div>`;
                            searchDropdown.classList.add('active');
                        }
                    }
                } catch (err) {
                    console.error('Search error:', err);
                }
            }, 250);
        });
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.header-search') && searchDropdown) {
            searchDropdown.classList.remove('active');
        }
    });

    // =========================================================================
    // Hero Banner Slider
    // =========================================================================
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.slider-dot');
    let currentSlide = 0;
    let slideInterval = null;

    function showSlide(index) {
        if (!slides.length) return;
        slides.forEach((s, i) => {
            s.classList.toggle('active', i === index);
        });
        dots.forEach((d, i) => {
            d.classList.toggle('active', i === index);
        });
        currentSlide = index;
    }

    function nextSlide() {
        if (!slides.length) return;
        let next = (currentSlide + 1) % slides.length;
        showSlide(next);
    }

    function prevSlide() {
        if (!slides.length) return;
        let prev = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(prev);
    }

    const nextBtn = document.getElementById('heroNextBtn');
    const prevBtn = document.getElementById('heroPrevBtn');
    if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); resetSlideTimer(); });
    if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); resetSlideTimer(); });

    dots.forEach((dot, idx) => {
        dot.addEventListener('click', () => {
            showSlide(idx);
            resetSlideTimer();
        });
    });

    function startSlideTimer() {
        if (slides.length > 1) {
            slideInterval = setInterval(nextSlide, 5000);
        }
    }

    function resetSlideTimer() {
        clearInterval(slideInterval);
        startSlideTimer();
    }

    startSlideTimer();

    // =========================================================================
    // Mobile Drawer Toggle
    // =========================================================================
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mobileNavDrawer = document.getElementById('mobileNavDrawer');
    const mobileDrawerClose = document.getElementById('mobileDrawerClose');

    if (mobileMenuToggle && mobileNavDrawer) {
        mobileMenuToggle.addEventListener('click', () => {
            mobileNavDrawer.classList.toggle('active');
        });
    }
    if (mobileDrawerClose && mobileNavDrawer) {
        mobileDrawerClose.addEventListener('click', () => {
            mobileNavDrawer.classList.remove('active');
        });
    }

    // Initial cart load
    fetchCartData();
});
