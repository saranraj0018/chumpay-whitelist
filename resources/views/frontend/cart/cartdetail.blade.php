<div class="max-w-6xl mx-auto px-4 mt-10 mb-[25px]">
    <section id="emptyCart"
        class="hidden w-full min-h-[70vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 rounded-2xl">
        <div class="w-full max-w-5xl flex flex-col md:flex-row items-center justify-center gap-10 md:gap-16">
            <div class="w-full md:w-1/2 flex justify-center">
                <img src="{{ asset('assets/images/nocart.png') }}" alt="Empty Cart"
                    class="w-[220px] sm:w-[260px] md:w-[300px] lg:w-[340px] object-contain">
            </div>
            <div class="w-full md:w-1/2 text-center md:text-left">
                <h2 class="text-[28px] sm:text-[34px] md:text-[38px] font-semibold text-[#1f1f1f] leading-tight">
                    Your cart is empty
                </h2>
                <p class="mt-3 text-[14px] sm:text-[15px] text-gray-400">Start shopping now to fill it up.</p>
                <a href="/shop"
                    class="inline-block mt-6 bg-black text-white text-[14px] sm:text-[15px] font-medium px-7 py-3 rounded-full hover:bg-gray-800 transition">
                    Start Shopping
                </a>
            </div>
        </div>
    </section>
    <div id="cartLoading" class="w-full min-h-[40vh] flex items-center justify-center">
        <p class="text-sm text-gray-500">Loading your cart...</p>
    </div>
    <div id="cartData" class="hidden grid grid-cols-1 lg:grid-cols-[70%_30%] gap-6">
        <div>
            <h4 class="text-lg font-medium block md:hidden mb-3">Cart</h4>
            <div class="h-auto md:h-[70vh] overflow-y-auto">
                <div
                    class="hidden md:grid grid-cols-5 text-slate-600 text-xs font-bold uppercase tracking-wider bg-slate-100 p-3 rounded-xl mb-4">
                    <div class="text-left pl-3">Product</div>
                    <div class="text-center">Qty Range</div>
                    <div class="text-center">Per Piece</div>
                    <div class="text-center">Quantity</div>
                    <div class="text-right pr-3">Total</div>
                </div>

                <div id="cartItems"></div>

                <div class="flex justify-end mt-6">
                    <button id="clearCartBtn" class="text-sm underline text-gray-600 hover:text-black">
                        Clear Shopping Cart
                    </button>
                </div>
            </div>
        </div>
        <div class="p-2 md:p-4 rounded-2xl space-y-4">
            <div class="border border-slate-200 rounded-2xl p-5 bg-white shadow-xs">
                <h3 class="font-bold text-slate-900 text-sm mb-3 flex items-center gap-1.5">
                    <span class="text-amber-500 font-extrabold">//</span> Apply Coupon
                </h3>
                <div class="flex flex-col sm:flex-row items-center gap-2.5">
                    <input id="couponCode" type="text" placeholder="Enter coupon code"
                        class="w-full border border-slate-300 rounded-full px-4 py-2.5 text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition">
                    <button id="applyCouponBtn"
                        class="bg-[#0f172a] text-white px-6 py-2.5 rounded-full text-sm font-bold w-full sm:w-auto hover:bg-amber-500 hover:text-black transition-all shadow-xs">
                        Apply
                    </button>
                </div>
                <p id="couponMsg" class="text-xs mt-2 hidden"></p>
                <button id="viewCouponsBtn" type="button" class="mt-3 text-xs font-bold text-amber-600 hover:text-amber-700 hover:underline">
                    View available coupons
                </button>
            </div>

            <!-- ORDER SUMMARY -->
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                <h2 class="font-bold text-slate-900 mb-4">Order Summary</h2>
                <div id="cartSummary" class="space-y-3 text-sm text-slate-600">
                    <p>Loading...</p>
                </div>
                <div class="border-t border-slate-100 my-4"></div>

                <button id="checkoutBtn" type="button"
                    class="w-full mt-6 bg-[#0f172a] text-white py-3.5 rounded-full block text-center font-bold hover:bg-amber-500 hover:text-black transition-all duration-200 shadow-md disabled:opacity-50">
                    Proceed to Checkout
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Available Coupons Modal -->
<div id="couponsModal" class="hidden fixed inset-0 z-[60] bg-black/50 flex items-center justify-center px-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-xl max-h-[80vh] flex flex-col">
        <div class="flex items-center justify-between p-5 border-b">
            <h3 class="text-lg font-semibold">Available Coupons</h3>
            <button type="button" onclick="closeCouponsModal()"
                class="text-gray-500 hover:text-black text-2xl leading-none">&times;</button>
        </div>
        <div id="couponsList" class="p-5 space-y-3 overflow-y-auto">
            <p class="text-sm text-gray-500">Loading coupons...</p>
        </div>
    </div>
</div>

<!-- Clear Cart Confirmation Modal -->
<div id="clearCartModal" class="hidden fixed inset-0 z-[60] bg-black/50 flex items-center justify-center px-4">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl">
        <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mb-3">
                <i class="fa-solid fa-trash text-red-600"></i>
            </div>
            <h3 class="text-lg font-semibold">Clear Cart?</h3>
            <p class="text-sm text-gray-500 mt-1">This will remove all items from your cart.</p>
        </div>
        <div class="flex gap-3 mt-6">
            <button type="button" onclick="closeClearModal()"
                class="flex-1 border border-gray-300 rounded-full py-2.5 text-sm font-medium hover:bg-gray-100">
                Cancel
            </button>
            <button type="button" id="confirmClearBtn"
                class="flex-1 bg-red-600 text-white rounded-full py-2.5 text-sm font-medium hover:bg-red-700">
                Clear All
            </button>
        </div>
    </div>
</div>

<script>
    const API_CART_DETAIL = "{{ route('cart_details') }}";
    const API_ADD_CART = "{{ route('add_toCart') }}";
    const API_CLEAR_CART = "{{ route('cart.clear') }}";
    const API_SELECT_ALL = "{{ route('cart.select_all') }}";
    const API_LIST_COUPONS = "{{ route('coupons.list') }}";
    const API_REMOVE_COUPON = "{{ route('coupons.remove') }}";

    let appliedCouponCode = null;
    let appliedCouponId = null;
    let latestSubtotal = 0;
    let cartHasItems = false;

    function csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    /* ---------- color/size breakdown chips (full accumulated matrix) ---------- */
    function matrixHtml(p) {
        if (!p.matrix || !p.matrix.length) return '';
        const chips = p.matrix.map(c =>
            `<span class="inline-block text-[11px] bg-gray-100 rounded px-2 py-0.5 mr-1 mb-1">
                ${c.color}${c.size ? ' / ' + c.size : ''}: <b>${c.qty}</b>
             </span>`
        ).join('');
        return `<div class="mt-1 flex flex-wrap">${chips}</div>`;
    }

    /* ---------- LOAD CART ---------- */
    async function loadCart(removeCouponFlag = false, showLoader = true) {
        if (showLoader) {
            document.getElementById('cartLoading').classList.remove('hidden');
            document.getElementById('cartData').classList.add('hidden');
            document.getElementById('emptyCart').classList.add('hidden');
        }

        try {
            const body = {
                is_cart: 1
            };
            if (removeCouponFlag) {
                body.remove_coupon = 1;
            } else if (appliedCouponId) {
                body.coupon_id = appliedCouponId;
            } else if (appliedCouponCode) {
                body.coupon_code = appliedCouponCode;
            }

            const res = await fetch(API_CART_DETAIL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf()
                },
                body: JSON.stringify(body)
            });
            const data = await res.json();

            if (showLoader) {
                document.getElementById('cartLoading').classList.add('hidden');
            }

            // remember the real subtotal for coupon eligibility checks
            latestSubtotal = parseFloat(String(data.billSummary?.itemsAmount ?? '0').replace(/,/g, '')) || 0;

            // restore applied-coupon state from the server (cached coupon),
            // so the Remove button shows after a page reload too.
            const loadedDiscount = parseFloat(String(data.billSummary?.discount ?? '0').replace(/,/g, '')) || 0;
            if (data.coupon_status && loadedDiscount > 0 && data.couponDetail) {
                appliedCouponId = data.couponDetail.id ?? appliedCouponId;
                appliedCouponCode = data.couponDetail.code ?? appliedCouponCode;
            }

            const items = data.productData || [];
            cartHasItems = items.length > 0;

            if (!cartHasItems) {
                document.getElementById('cartData').classList.add('hidden');
                document.getElementById('emptyCart').classList.remove('hidden');
                return;
            }

            document.getElementById('emptyCart').classList.add('hidden');
            document.getElementById('cartData').classList.remove('hidden');
            renderItems(items);
            renderSummary(data.billSummary);
        } catch (e) {
            document.getElementById('cartLoading').classList.add('hidden');
            document.getElementById('cartData').classList.remove('hidden');
            document.getElementById('cartItems').innerHTML =
                `<p class="text-sm text-red-500">Failed to load cart. Please refresh.</p>`;
        }
    }

    /* ---------- RENDER ITEMS ---------- */
    function renderItems(items) {
        document.getElementById('cartItems').innerHTML = items.map(p => {
            const unit = p.quantity > 0 ? (p.discountedPrice / p.quantity) : p.discountedPrice;
            const isBulk = p.product_type === 'bulk';
            const variantId = p.variant_id ?? '';

            const atMinQty = p.quantity <= 1;
            const qtyControls = isBulk ?
                `<span class="px-3 text-sm">${p.quantity}</span>` :
                `
                    <button onclick="updateQty(${p.id}, '${variantId}', -1)" ${atMinQty ? 'disabled' : ''}
                        class="w-7 h-7 flex items-center justify-center rounded-full bg-white shadow ${atMinQty ? 'opacity-40 cursor-not-allowed' : ''}">-</button>
                    <span class="px-3 text-sm">${p.quantity}</span>
                    <button onclick="updateQty(${p.id}, '${variantId}', 1)"
                        class="w-7 h-7 flex items-center justify-center rounded-full bg-white shadow">+</button>`;

            // tier range column (bulk only; blank for single/variant)
            const tierRangeCol = isBulk && p.tierRange ?
                `${p.tierRange} <span class="text-gray-400">(₹${Math.round(p.tierPrice ?? 0)})</span>` :
                '-';
            // per-piece column: bulk tier price, or the variant's own sale price
            // when the item has its own variant image
            const perPieceCol = (p.perPiece || p.perPiece === 0) ? `₹${Math.round(p.perPiece)}` : '-';

            return `
            <div class="grid grid-cols-1 md:grid-cols-5 md:items-center gap-3 border border-gray-300 p-2.5 rounded-lg mb-2.5">

                <!-- Product -->
                <div class="flex items-center gap-4">
                    <img src="${p.image}" class="w-16 h-16 rounded-md object-cover" alt="Product">
                    <div>
                        <h3 class="text-sm font-medium">${p.name}</h3>
                        ${p.variation ? `<p class="text-xs text-gray-500">${p.variation.variant_name}</p>` : ''}
                        ${isBulk ? `<p class="text-[10px] uppercase tracking-wide text-gray-400 mt-1">Bulk</p>` : ''}
                        ${matrixHtml(p)}
                    </div>
                </div>

                <!-- Qty Range -->
                <div class="text-sm text-left md:text-center">
                    <span class="md:hidden text-gray-400 mr-1">Range:</span>${tierRangeCol}
                </div>

                <!-- Per Piece -->
                <div class="text-sm text-left md:text-center">
                    <span class="md:hidden text-gray-400 mr-1">Per Piece:</span>${perPieceCol}
                </div>

                <!-- Quantity -->
                <div class="flex justify-start md:justify-center">
                    <div class="flex items-center bg-gray-100 rounded-full px-2 py-1">
                        ${qtyControls}
                    </div>
                </div>

                <!-- Total + remove -->
                <div class="flex items-center justify-between md:justify-end gap-3">
                    <span class="text-sm font-semibold">₹${p.discountedPrice}</span>
                    <button onclick="removeItem(${p.id}, '${variantId}', ${p.quantity})"
                        class="text-gray-500 hover:text-red-500 text-lg">&times;</button>
                </div>
            </div>`;
        }).join('');
    }

    /* ---------- RENDER SUMMARY ---------- */
    function renderSummary(b) {
        if (!b) return;
        const discountVal = parseFloat(String(b.discount ?? '0').replace(/,/g, '')) || 0;
        const couponApplied = (appliedCouponId || appliedCouponCode) && discountVal > 0;

        const couponLine = couponApplied ?
            `<div class="flex justify-between items-center">
                   <span class="flex items-center gap-2">
                       Coupon Discount
                       <button type="button" onclick="removeCoupon()"
                           class="text-[11px] text-red-500 hover:underline">Remove</button>
                   </span>
                   <span class="text-green-600">-₹${b.discount}</span>
               </div>` :
            `<div class="flex justify-between"><span>Coupon Discount</span><span class="text-green-600">-₹${b.discount}</span></div>`;

        document.getElementById('cartSummary').innerHTML =
            `
            <div class="flex justify-between"><span>Items</span><span>${b.totalItems}</span></div>
            <div class="flex justify-between"><span>Sub Total</span><span>₹${b.itemsAmount}</span></div>
            <div class="flex justify-between"><span>Shipping</span><span>₹${b.deliveryCharge}</span></div>
            <div class="flex justify-between"><span>Taxes</span><span>₹${b.TAX}</span></div>
            <div class="flex justify-between"><span>Platform Fee</span><span>₹${b.platform_fee}</span></div>
            ${couponLine}
            <div class="border-t my-2"></div>
            <div class="flex justify-between font-semibold text-lg text-black"><span>Total</span><span>₹${b.totalAmount}</span></div>`;
    }

    /* ---------- QTY UPDATE ---------- */
    async function updateQty(productId, variantId, delta) {
        try {
            const res = await fetch(API_ADD_CART, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf()
                },
                body: JSON.stringify({
                    product_id: productId,
                    variant_id: variantId || null,
                    quantity: delta,
                    is_buy_now: false
                })
            });
            const data = await res.json();
            if (data.status === 200) {
                loadCart(false, false);
            } else {
                showToast(data.message || 'Could not update quantity', 'error');
            }
        } catch (e) {
            showToast('Failed to update.', 'error');
        }
    }

    function removeItem(productId, variantId, qty) {
        updateQty(productId, variantId, -qty);
    }
    async function applyCoupon(opts) {
        const msg = document.getElementById('couponMsg');
        const code = (opts.code || '').trim();
        const id = opts.id || null;

        if (!id && !code) {
            msg.textContent = 'Enter a coupon code.';
            msg.className = 'text-xs mt-2 text-red-500';
            msg.classList.remove('hidden');
            return false;
        }

        const body = {
            is_cart: 1
        };
        if (id) body.coupon_id = id;
        else body.coupon_code = code;

        try {
            const res = await fetch(API_CART_DETAIL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf()
                },
                body: JSON.stringify(body)
            });

            // handle session/CSRF expiry distinctly from a bad coupon
            if (res.status === 419) {
                msg.textContent = 'Your session expired. Please refresh the page and try again.';
                msg.className = 'text-xs mt-2 text-red-500';
                msg.classList.remove('hidden');
                return false;
            }

            if (!res.ok) {
                msg.textContent = 'Something went wrong. Please try again.';
                msg.className = 'text-xs mt-2 text-red-500';
                msg.classList.remove('hidden');
                return false;
            }

            const data = await res.json();

            const discountVal = parseFloat(String(data.billSummary?.discount ?? '0').replace(/,/g, ''));
            if (data.status === 200 && data.coupon_status && discountVal > 0) {
                appliedCouponId = id || (data.couponDetail?.id ?? null);
                appliedCouponCode = code || (data.couponDetail?.code ?? null);
                renderSummary(data.billSummary);
                msg.classList.add('hidden');
                return true;
            } else {
                appliedCouponId = null;
                appliedCouponCode = null;
                // use the dedicated coupon_message field, not the generic cart "message"
                msg.textContent = data.coupon_message || 'This coupon is not available.';
                msg.className = 'text-xs mt-2 text-red-500';
                renderSummary(data.billSummary);
                msg.classList.remove('hidden');
                return false;
            }
        } catch (e) {
            msg.textContent = 'Failed to apply coupon. Please try again.';
            msg.className = 'text-xs mt-2 text-red-500';
            msg.classList.remove('hidden');
            return false;
        }
    }

    async function removeCoupon() {
        appliedCouponId = null;
        appliedCouponCode = null;
        document.getElementById('couponCode').value = '';
        const msg = document.getElementById('couponMsg');
        msg.classList.add('hidden');

        // clear the server-cached coupon so cartDetail won't re-apply it
        try {
            await fetch(API_REMOVE_COUPON, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf()
                },
                body: JSON.stringify({})
            });
        } catch (e) {
            /* ignore */
        }

        await loadCart(false, false); // reload totals without the coupon
        showToast('Coupon removed.', 'success');
    }

    document.getElementById('applyCouponBtn')?.addEventListener('click', async function() {
        const btn = this;
        btn.disabled = true;
        const original = btn.textContent;
        btn.textContent = 'Applying...';
        await applyCoupon({
            code: document.getElementById('couponCode').value
        });
        btn.disabled = false;
        btn.textContent = original;
    });

    /* ---------- VIEW COUPONS POPUP ---------- */
    function openCouponsModal() {
        document.getElementById('couponsModal').classList.remove('hidden');
        loadCoupons();
    }

    function closeCouponsModal() {
        document.getElementById('couponsModal').classList.add('hidden');
    }
    document.getElementById('viewCouponsBtn')?.addEventListener('click', openCouponsModal);
    document.getElementById('couponsModal').addEventListener('click', function(e) {
        if (e.target === this) closeCouponsModal();
    });

    async function loadCoupons() {
        const list = document.getElementById('couponsList');
        list.innerHTML = `<p class="text-sm text-gray-500">Loading coupons...</p>`;
        try {
            // send the REAL cart subtotal so eligibility (min/max) is correct for bulk too
            const res = await fetch(API_LIST_COUPONS + '?subtotal=' + encodeURIComponent(latestSubtotal), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf()
                }
            });
            const data = await res.json();
            const coupons = data.coupons || [];

            if (!coupons.length) {
                list.innerHTML = `<p class="text-sm text-gray-500">No coupons available right now.</p>`;
                return;
            }

            list.innerHTML = coupons.map(c => {
                const applyBtn = c.eligible ?
                    `<button type="button" onclick="applyCouponFromList(${c.id}, this)"
                          class="shrink-0 bg-black text-white text-xs rounded-full px-4 py-2 hover:bg-gray-800">
                          Apply
                       </button>` :
                    `<button type="button" disabled
                          class="shrink-0 bg-gray-200 text-gray-400 text-xs rounded-full px-4 py-2 cursor-not-allowed">
                          Apply
                       </button>`;

                const reasonMsg = (!c.eligible && c.reason) ?
                    `<p class="text-[11px] text-red-500 mt-1">${c.reason}</p>` :
                    '';

                // show the discount preview for eligible coupons
                const savePreview = (c.eligible && c.discount_amount > 0) ?
                    `<p class="text-[11px] text-green-600 mt-1">You save ₹${Math.round(c.discount_amount)}</p>` :
                    '';

                return `
                <div class="border rounded-xl p-3 flex items-start justify-between gap-3 ${c.eligible ? '' : 'opacity-70'}">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-semibold text-sm">${c.display_code}</span>
                            <span class="text-[10px] uppercase tracking-wide px-2 py-0.5 rounded-full bg-green-100 text-green-700">${c.discount_label}</span>
                        </div>
                        ${c.description ? `<p class="text-xs text-gray-500 mt-1">${c.description}</p>` : ''}
                        ${c.conditions ? `<p class="text-[11px] text-gray-400 mt-1">${c.conditions}</p>` : ''}
                        ${c.expires_at ? `<p class="text-[11px] text-gray-400 mt-0.5">Valid till ${c.expires_at}</p>` : ''}
                        ${savePreview}
                        ${reasonMsg}
                    </div>
                    ${applyBtn}
                </div>`;
            }).join('');
        } catch (e) {
            list.innerHTML = `<p class="text-sm text-red-500">Failed to load coupons.</p>`;
        }
    }

    async function applyCouponFromList(couponId, btn) {
        btn.disabled = true;
        const original = btn.textContent;
        btn.textContent = 'Applying...';
        const ok = await applyCoupon({
            id: couponId
        });
        btn.disabled = false;
        btn.textContent = original;
        if (ok) {
            closeCouponsModal();
            showToast('Coupon applied successfully.', 'success');
        } else {
            showToast('This coupon cannot be applied to your cart.', 'error');
        }
    }

    /* ---------- PROCEED TO CHECKOUT ---------- */
    document.getElementById('checkoutBtn')?.addEventListener('click', async function() {
        if (!cartHasItems) {
            showToast('Your cart is empty.', 'error');
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Processing...';

        try {
            const res = await fetch(API_SELECT_ALL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf()
                },
                body: JSON.stringify({})
            });
            const data = await res.json();

            if (data.status !== 200) {
                showToast(data.message || 'Could not proceed.', 'error');
                btn.disabled = false;
                btn.textContent = 'Proceed to Checkout';
                return;
            }

            window.location.href = '/shop/delivery';
        } catch (e) {
            showToast('Failed to proceed to checkout.', 'error');
            btn.disabled = false;
            btn.textContent = 'Proceed to Checkout';
        }
    });

    /* ---------- CLEAR CART ---------- */
    function openClearModal() {
        document.getElementById('clearCartModal').classList.remove('hidden');
    }

    function closeClearModal() {
        document.getElementById('clearCartModal').classList.add('hidden');
    }
    document.getElementById('clearCartBtn')?.addEventListener('click', openClearModal);
    document.getElementById('clearCartModal').addEventListener('click', function(e) {
        if (e.target === this) closeClearModal();
    });
    document.getElementById('confirmClearBtn')?.addEventListener('click', async function() {
        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Clearing...';
        try {
            const res = await fetch(API_CLEAR_CART, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf()
                },
                body: JSON.stringify({})
            });
            const data = await res.json();
            closeClearModal();
            appliedCouponCode = null;
            if (data.status === 200) {
                loadCart();
            } else {
                showToast(data.message || 'Could not clear cart.', 'error');
            }
        } catch (e) {
            closeClearModal();
            showToast('Failed to clear cart.', 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Clear All';
        }
    });

    /* ---------- INIT ---------- */
    loadCart();
</script>