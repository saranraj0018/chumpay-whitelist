<div class="max-w-6xl mx-auto px-4 mt-[10px] lg:mt-[50px] mb-[30px] grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- LEFT -->
    <div class="lg:col-span-2">
        <div class="block md:hidden mb-[10px]">
            <a href="/shop/cart"><button class="p-2 rounded-full"><i class="fa-solid fa-arrow-left"></i></button></a>
        </div>
      @include('frontend.partials.address-manager', [
            'title' => 'Delivery Address',
            'selectable' => true,
        ])
        {{-- <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold">Delivery Address</h2>
            <button onclick="openModal()" type="button"
                class="text-sm border border-gray-300 rounded-md px-3 py-1.5 hover:bg-black hover:text-white transition">
                + Add Address
            </button>
        </div> --}}



        <!-- Address Modal -->
        {{-- <div id="addressModal" class="hidden fixed inset-0 z-50 bg-black/50 px-4 py-6 overflow-y-auto">
            <div class="min-h-full flex items-center justify-center">
                <div
                    class="relative w-full max-w-4xl bg-white rounded-3xl p-5 sm:p-6 md:p-8 shadow-xl mt-[40px] md:mt-0">
                    <button onclick="closeModal()" type="button"
                        class="absolute top-4 right-4 text-gray-500 hover:text-black text-2xl leading-none">&times;</button>
                    <h2 id="addressModalTitle" class="text-lg sm:text-xl font-semibold mb-6 pr-8">Add Address</h2>
                    <form id="addressForm">
                        <input type="hidden" name="address_id" id="addressId">
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5 h-[70vh] overflow-y-auto md:overflow-y-visible md:h-auto">
                            <div class="w-full">
                                <label class="text-sm text-gray-600">Full Name</label>
                                <input type="text" name="name" placeholder="Enter Your Full Name" required
                                    class="w-full mt-2 px-4 py-3 rounded-full border border-gray-200 text-sm sm:text-base outline-none">
                            </div>
                            <div class="w-full">
                                <label class="text-sm text-gray-600">Mobile Number</label>
                                <div
                                    class="flex items-center mt-2 border border-gray-200 rounded-full overflow-hidden w-full">
                                    <div
                                        class="flex items-center gap-2 px-3 sm:px-4 border-r border-gray-200 shrink-0 bg-white">
                                        <img src="https://flagcdn.com/w20/in.png" alt="India Flag"
                                            class="w-5 h-4 object-cover">
                                        <span class="text-sm text-gray-700">+91</span>
                                    </div>
                                    <input type="tel" name="phone_number" placeholder="Enter Mobile Number" required
                                        class="flex-1 min-w-0 px-4 py-3 text-sm sm:text-base outline-none">
                                </div>
                            </div>
                            <div class="w-full">
                                <label class="text-sm text-gray-600">Address Type</label>
                                <select name="address_type" required
                                    class="w-full mt-2 px-4 py-3 rounded-full border border-gray-200 text-sm sm:text-base outline-none bg-white">
                                    <option value="">Select Address Type</option>
                                    <option value="home">Home</option>
                                    <option value="work">Work</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="w-full">
                                <label class="text-sm text-gray-600">State</label>
                                <input type="text" name="state" placeholder="Select State" required
                                    class="w-full mt-2 px-4 py-3 rounded-full border border-gray-200 text-sm sm:text-base outline-none">
                            </div>
                            <div class="w-full">
                                <label class="text-sm text-gray-600">Street Address</label>
                                <input type="text" name="address" placeholder="Enter Street Address" required
                                    class="w-full mt-2 px-4 py-3 rounded-full border border-gray-200 text-sm sm:text-base outline-none">
                            </div>
                            <div class="w-full">
                                <label class="text-sm text-gray-600">City</label>
                                <input type="text" name="city" placeholder="Select City" required
                                    class="w-full mt-2 px-4 py-3 rounded-full border border-gray-200 text-sm sm:text-base outline-none">
                            </div>
                            <div class="w-full">
                                <label class="text-sm text-gray-600">Pincode</label>
                                <input type="text" name="pincode" placeholder="Enter Pincode" required
                                    class="w-full mt-2 px-4 py-3 rounded-full border border-gray-200 text-sm sm:text-base outline-none">
                            </div>
                            <div class="w-full">
                                <label class="text-sm text-gray-600">Landmark</label>
                                <input type="text" name="landmark" placeholder="Enter Landmark"
                                    class="w-full mt-2 px-4 py-3 rounded-full border border-gray-200 text-sm sm:text-base outline-none">
                            </div>
                        </div>
                        <p id="addressFormMessage" class="mt-4 hidden text-sm"></p>
                        <div class="mt-6 flex justify-start">
                            <button type="submit" id="addressSubmitBtn"
                                class="w-full sm:w-auto bg-black text-white px-6 sm:px-8 py-3 rounded-full text-sm sm:text-base hover:bg-gray-800 transition">
                                Save Address
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div> --}}
        <!-- Items -->
        <h2 class="font-semibold mb-4 mt-8">Your Items</h2>
        <div id="buyNowProducts" class="bg-white p-4 rounded-xl border">
            <p class="text-sm text-gray-500">Loading items...</p>
        </div>
    </div>
    <!-- RIGHT -->
    <div class="p-2 md:p-4 rounded-2xl space-y-4">
        <div class="border border-slate-200 rounded-2xl p-5 bg-white shadow-xs">
            <h3 class="font-bold text-slate-900 text-sm mb-3 flex items-center gap-1.5">
                <span class="text-amber-500 font-extrabold">//</span> Apply Coupon
            </h3>
            <div class="flex flex-col sm:flex-row items-center gap-2.5">
                <input id="couponCode" type="text" placeholder="Enter coupon code"
                    class="w-full border border-slate-300 rounded-full px-4 py-2.5 text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition">
                <button id="applyCouponBtn" class="bg-[#0f172a] text-white px-6 py-2.5 rounded-full text-sm font-bold w-full sm:w-auto hover:bg-amber-500 hover:text-black transition-all shadow-xs">
                    Apply
                </button>
            </div>
            <p id="couponMsg" class="text-xs mt-2 hidden"></p>
            <button id="viewCouponsBtn" type="button" class="mt-3 text-xs font-bold text-amber-600 hover:text-amber-700 hover:underline">
                View available coupons
            </button>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm h-fit">

            <h2 class="font-bold text-slate-900 mb-4">Order Summary</h2>
            <div id="orderSummary" class="space-y-3 text-sm text-slate-600">
                <p>Loading...</p>
            </div>
            <button id="continueBtn" type="button" disabled
                class="w-full mt-6 bg-[#0f172a] text-white py-3.5 rounded-full font-bold hover:bg-amber-500 hover:text-black transition-all duration-200 shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                Continue
            </button>
            <p id="continueHint" class="text-xs text-red-500 mt-2 hidden">Please select a delivery address.</p>
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

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 z-[60] bg-black/50 flex items-center justify-center px-4">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl">
        <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mb-3">
                <i class="fa-solid fa-trash text-red-600"></i>
            </div>
            <h3 class="text-lg font-semibold">Delete Address?</h3>
            <p class="text-sm text-gray-500 mt-1">This action cannot be undone.</p>
        </div>
        <div class="flex gap-3 mt-6">
            <button type="button" onclick="closeDeleteModal()"
                class="flex-1 border border-gray-300 rounded-full py-2.5 text-sm font-medium hover:bg-gray-100">
                Cancel
            </button>
            <button type="button" id="confirmDeleteBtn"
                class="flex-1 bg-red-600 text-white rounded-full py-2.5 text-sm font-medium hover:bg-red-700">
                Delete
            </button>
        </div>
    </div>
</div>

<script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
<script>
    const API_CART_DETAIL = "{{ route('cart_details') }}";
    const API_ORDER_CREATE = "{{ route('order.create') }}";
    const API_SELECT_ALL = "{{ route('cart.select_all') }}";
    const API_LIST_COUPONS = "{{ route('coupons.list') }}";
    const API_REMOVE_COUPON = "{{ route('coupons.remove') }}";
    const CASHFREE_MODE = "{{ env('CASHFREE_ENV') === 'sandbox' ? 'sandbox' : 'production' }}";

    const _params = new URLSearchParams(window.location.search);
    const _checkoutId = _params.get('checkout_id');
    const _isBuyNow = _params.get('is_buy_now') ? true : false;

    let currentBillSummary = null;
    let appliedCouponCode = null;
    let appliedCouponId = null;
    let latestSubtotal = 0;

    function csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    /* ---------- color/size breakdown chips ---------- */
    function matrixHtml(p) {
        if (!p.matrix || !p.matrix.length) return '';
        const chips = p.matrix.map(c =>
            `<span class="inline-block text-[11px] bg-gray-100 rounded px-2 py-0.5 mr-1 mb-1">
                ${c.color}${c.size ? ' / ' + c.size : ''}: <b>${c.qty}</b>
             </span>`
        ).join('');
        return `<div class="mt-1 flex flex-wrap">${chips}</div>`;
    }

    /* ---------- ADDRESS SELECTION HOOK (driven by AddressManager) ---------- */
    window.onAddressListChanged = function(addresses, selectedId) {
        updateContinueState(selectedId);
    };

    function updateContinueState(selectedId) {
        const btn = document.getElementById('continueBtn');
        const hint = document.getElementById('continueHint');
        const ok = !!selectedId;
        btn.disabled = !ok;
        hint.classList.toggle('hidden', ok);
    }

    /* ---------- CONTINUE -> create order + open Cashfree directly ---------- */
    document.getElementById('continueBtn').addEventListener('click', async function() {
        const selectedAddressId = AddressManager.getSelectedId();

        if (!selectedAddressId) {
            updateContinueState(selectedAddressId);
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Processing...';

        try {
            const grandTotal = parseFloat(String(currentBillSummary?.totalAmount ?? 0).replace(/,/g, '')) ||
                0;
            if (grandTotal <= 0) {
                showToast('Invalid order total.', 'error');
                btn.disabled = false;
                btn.textContent = 'Continue';
                return;
            }

            const createRes = await fetch(API_ORDER_CREATE, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf()
                },
                body: JSON.stringify({
                    grant_total: grandTotal,
                    address_id: selectedAddressId,
                    is_buy_now: _isBuyNow ? 1 : 0,
                    checkout_id: _checkoutId,
                    coupon_id: appliedCouponId,
                    coupon_code: appliedCouponCode
                })
            });
            const createData = await createRes.json();

            if (createData.status !== 200 || !createData.payment_session_id) {
                showToast(createData.message || 'Could not start payment.', 'error');
                btn.disabled = false;
                btn.textContent = 'Continue';
                return;
            }

            const cashfree = Cashfree({
                mode: CASHFREE_MODE
            });
            await cashfree.checkout({
                paymentSessionId: createData.payment_session_id,
                redirectTarget: '_self'
            });
        } catch (e) {
            showToast('Payment failed: ' + e.message, 'error');
            btn.disabled = false;
            btn.textContent = 'Continue';
        }
    });

    /* ---------- ITEMS + SUMMARY ---------- */
    async function loadDeliveryData(removeCouponFlag = false) {
        try {
            const body = _isBuyNow ?
                {
                    is_buy_now: 1,
                    checkout_id: _checkoutId
                } :
                {
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

            if (data.status !== 200) {
                document.getElementById('buyNowProducts').innerHTML =
                    `<p class="text-sm text-red-500">${data.message || 'Error loading cart'}</p>`;
                return;
            }

            latestSubtotal = parseFloat(String(data.billSummary?.itemsAmount ?? '0').replace(/,/g, '')) || 0;

            const loadedDiscount = parseFloat(String(data.billSummary?.discount ?? '0').replace(/,/g, '')) || 0;
            if (data.coupon_status && loadedDiscount > 0 && data.couponDetail) {
                appliedCouponId = data.couponDetail.id ?? appliedCouponId;
                appliedCouponCode = data.couponDetail.code ?? appliedCouponCode;
            }

            renderProducts(data.productData);
            renderSummary(data.billSummary);
        } catch (e) {
            document.getElementById('buyNowProducts').innerHTML =
                `<p class="text-sm text-red-500">Failed to load. Please try again.</p>`;
        }
    }

    function renderProducts(products) {
        if (!products || !products.length) {
            document.getElementById('buyNowProducts').innerHTML =
                `<p class="text-sm text-gray-500">No items found.</p>`;
            return;
        }
        document.getElementById('buyNowProducts').innerHTML = products.map(p => `
            <div class="flex items-start gap-4 border-b last:border-b-0 py-4">
                <img src="${p.image}" class="w-20 h-20 object-cover rounded-lg">
                <div class="flex-1">
                    <h3 class="font-medium">${p.name}</h3>
                    ${p.variation ? `<p class="text-sm text-gray-500">${p.variation.variant_name}</p>` : ''}
                    <p class="text-sm text-gray-500">Qty: ${p.quantity}</p>
                    ${matrixHtml(p)}
                </div>
                <div class="font-semibold">₹${p.discountedPrice}</div>
            </div>`).join('');
    }

    function renderSummary(b) {
        currentBillSummary = b;
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

        document.getElementById('orderSummary').innerHTML =
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

    /* ---------- COUPON: shared apply (manual input + popup) ---------- */
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
            /* ignore */ }

        await loadDeliveryData(true);
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
    document.getElementById('couponsModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeCouponsModal();
    });

    async function loadCoupons() {
        const list = document.getElementById('couponsList');
        list.innerHTML = `<p class="text-sm text-gray-500">Loading coupons...</p>`;
        try {
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

    /* ---------- toast fallback (replace with your own if you have a global one) ---------- */
    function showToast(message, type) {
        if (typeof window._globalShowToast === 'function') {
            window._globalShowToast(message, type);
            return;
        }
        alert(message);
    }

    /* ---------- INIT ---------- */
    (async function init() {
        if (!_isBuyNow) {
            try {
                await fetch(API_SELECT_ALL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf()
                    },
                    body: JSON.stringify({})
                });
            } catch (e) {
                /* ignore */ }
        }
        loadDeliveryData();
    })();
</script>
