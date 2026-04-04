// ════════════════════════════════════════════════════
//  CART — localStorage per company slug
//  All functions on window.* for Alpine access
// ════════════════════════════════════════════════════

window.getCart = function () {
    try {
        const key = "cart_" + (window.__COMPANY_SLUG__ || "store");
        return JSON.parse(localStorage.getItem(key) || "[]");
    } catch {
        return [];
    }
};

window.saveCart = function (items) {
    const key = "cart_" + (window.__COMPANY_SLUG__ || "store");
    localStorage.setItem(key, JSON.stringify(items));
    if (window.__alpineCart) {
        window.__alpineCart.syncFromStorage();
    }
};

window.getCartTotals = function () {
    const cart = window.getCart();
    const subtotal = cart.reduce((sum, i) => sum + i.price * i.qty, 0);
    return {
        count: cart.reduce((sum, i) => sum + i.qty, 0),
        subtotal: subtotal.toFixed(2),
        total: subtotal.toFixed(2),
    };
};

window.addToCart = function (
    productId,
    skuId,
    productName,
    skuLabel,
    price,
    imageUrl,
    qty = 1,
) {
    const cart = window.getCart();
    const existing = cart.find((i) => i.sku_id === skuId);

    if (existing) {
        existing.qty += qty;
    } else {
        cart.push({
            product_id: productId,
            sku_id: skuId,
            name: productName,
            variant: skuLabel,
            price: parseFloat(price),
            image: imageUrl,
            qty: qty,
        });
    }

    window.saveCart(cart);

    // Open cart drawer
    // if (window.__alpineCart) {
    //     window.__alpineCart.cartOpen = true;
    //     window.__alpineCart.cartView = "cart"; // always show cart first
    // }

    console.log("[Cart] Added:", productName, "| SKU:", skuId, "| Qty:", qty);
    return cart;
};

window.removeFromCart = function (skuId) {
    const cart = window.getCart().filter((i) => i.sku_id !== skuId);
    window.saveCart(cart);
    console.log("[Cart] Removed SKU:", skuId);
};

window.updateCartQty = function (skuId, qty) {
    if (qty < 1) {
        window.removeFromCart(skuId);
        return;
    }
    const cart = window.getCart();
    const item = cart.find((i) => i.sku_id === skuId);
    if (item) {
        item.qty = parseInt(qty);
        window.saveCart(cart);
    }
};

window.clearCart = function () {
    const key = "cart_" + (window.__COMPANY_SLUG__ || "store");
    localStorage.removeItem(key);
    if (window.__alpineCart) window.__alpineCart.syncFromStorage();
    console.log("[Cart] Cleared");
};

// ════════════════════════════════════════════════════
//  PLACE ORDER — AJAX POST to /{slug}/orders
// ════════════════════════════════════════════════════

window.placeOrder = async function (formData) {
    const slug = window.__COMPANY_SLUG__ || "";
    const csrfToken =
        document.querySelector('meta[name="csrf-token"]')?.content ?? "";
    const cart = window.getCart();

    if (cart.length === 0) {
        console.warn("[Order] Cart is empty");
        return { success: false, message: "Your cart is empty." };
    }

    // Build payload — prices NOT sent, server recalculates from DB
    const payload = {
        customer_name: formData.name?.trim(),
        customer_phone: formData.phone?.trim(),
        customer_email: formData.email?.trim() || null,
        delivery_address: formData.address?.trim(),
        delivery_city: formData.city?.trim() || null,
        delivery_state: formData.state?.trim() || null,
        delivery_pincode: formData.pincode?.trim() || null,
        customer_notes: formData.notes?.trim() || null,
        payment_method: "cod",
        source: "storefront",
        order_type: "retail",
        // Cart items — only IDs and qty sent, prices from DB
        items: cart.map((i) => ({
            product_id: i.product_id,
            sku_id: i.sku_id,
            qty: i.qty,
            variant: i.variant || null,
            image: i.image || null,
        })),
    };

    console.log("[Order] Placing order...", {
        customer: payload.customer_phone,
        items: payload.items.length,
        slug,
    });

    try {
        const response = await fetch(`/${slug}/orders`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (response.ok && data.success) {
            console.log("[Order] Success:", data.order_number);
            window.clearCart();
            return { success: true, ...data };
        } else {
            console.warn("[Order] Failed:", data.message);
            return {
                success: false,
                message: data.message || "Order failed. Please try again.",
            };
        }
    } catch (err) {
        console.error("[Order] Network error:", err);
        return {
            success: false,
            message:
                "Network error. Please check your connection and try again.",
        };
    }
};
