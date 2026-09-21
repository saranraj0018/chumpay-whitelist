<div class="max-w-6xl mx-auto px-4 mt-[10px] lg:mt-[50px] mb-[30px] grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- LEFT -->
    <div class="lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-slate-900 text-lg">Payment</h2>
            <a href="javascript:history.back()" class="text-sm font-medium text-slate-500 hover:text-slate-900 transition">← Back</a>
        </div>

        <div id="paymentAddress" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs mb-6">
            <p class="text-sm text-slate-500">Loading address...</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
            <h3 class="font-bold text-slate-900 mb-4">Payment Method</h3>
            <label class="flex items-center gap-3 border border-slate-200 rounded-xl p-4 cursor-pointer hover:border-amber-400/80 hover:bg-amber-50/20 transition-all">
                <input type="radio" name="paymethod" value="cashfree" checked class="accent-amber-500 w-4 h-4 cursor-pointer">
                <span class="text-sm font-bold text-slate-900">Pay Online (Cashfree)</span>
            </label>
            <p class="text-xs text-slate-400 mt-3">You'll be redirected to a secure Cashfree checkout.</p>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm h-fit">
        <h2 class="font-bold text-slate-900 mb-4">Order Summary</h2>
        <div id="orderSummary" class="space-y-3 text-sm text-slate-600">
            <p>Loading...</p>
        </div>
        <button id="payBtn" type="button" disabled
            class="w-full mt-6 bg-[#0f172a] text-white py-3.5 rounded-full font-bold hover:bg-amber-500 hover:text-black transition-all duration-200 shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
            Pay Now
        </button>
        <p id="payHint" class="text-xs text-red-500 mt-2 hidden"></p>
    </div>
</div>

<!-- Cashfree v3 SDK -->
<script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>

<script>
    const API_CART_DETAIL = "{{ route('cart_details') }}";
    const API_ORDER_CREATE = "{{ route('order.create') }}";

    const CASHFREE_MODE = "{{ env('CASHFREE_ENV') === 'sandbox' ? 'sandbox' : 'production' }}";

    const _params = new URLSearchParams(window.location.search);
    const _checkoutId = _params.get('checkout_id');
    const _isBuyNow = _params.get('is_buy_now') ? true : false;
    const _addressId = _params.get('address_id');

    let grandTotal = 0;

    function csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    function setPayHint(msg) {
        const el = document.getElementById('payHint');
        el.textContent = msg || '';
        el.classList.toggle('hidden', !msg);
    }

    if (!_addressId) {
        setPayHint('No address selected. Please go back and choose an address.');
    }

    /* ---------- LOAD SUMMARY + ADDRESS ---------- */
    async function loadSummary() {
        try {
            const res = await fetch(API_CART_DETAIL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf()
                },
                body: JSON.stringify({
                    is_buy_now: _isBuyNow,
                    checkout_id: _checkoutId
                })
            });
            const data = await res.json();

            if (data.status !== 200) {
                setPayHint(data.message || 'Could not load order.');
                return;
            }

            renderAddress(data.addressDetails);
            renderSummary(data.billSummary);

            grandTotal = parseFloat(String(data.billSummary.totalAmount).replace(/,/g, '')) || 0;
            document.getElementById('payBtn').disabled = !(_addressId && grandTotal > 0);
        } catch (e) {
            setPayHint('Failed to load order. Please try again.');
        }
    }

    function renderAddress(addr) {
        const box = document.getElementById('paymentAddress');
        if (!addr) {
            box.innerHTML = `<p class="text-sm text-red-500">Address not found.</p>`;
            return;
        }
        box.innerHTML = `
            <h3 class="font-medium">${addr.name ?? ''}
                <span class="text-[10px] uppercase tracking-wide px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 ml-2">${addr.address_type ?? ''}</span>
            </h3>
            <p class="text-sm text-gray-500 mt-2">
                ${[addr.address, addr.landmark, addr.city, addr.state, addr.pincode].filter(Boolean).join(', ')}
            </p>
            <p class="text-sm mt-2">Mobile: <span class="font-medium">${addr.phone_number ?? ''}</span></p>`;
    }

    function renderSummary(b) {
        document.getElementById('orderSummary').innerHTML = `
            <div class="flex justify-between"><span>Items</span><span>${b.totalItems}</span></div>
            <div class="flex justify-between"><span>Sub Total</span><span>₹${b.itemsAmount}</span></div>
            <div class="flex justify-between"><span>Coupon Discount</span><span>-₹${b.discount}</span></div>
            <div class="flex justify-between"><span>Shipping</span><span>₹${b.deliveryCharge}</span></div>
            <div class="flex justify-between"><span>Taxes</span><span>₹${b.TAX}</span></div>
            <div class="flex justify-between"><span>Platform Fee</span><span>₹${b.platform_fee}</span></div>
            <div class="border-t my-2"></div>
            <div class="flex justify-between font-semibold text-lg text-black"><span>Total</span><span>₹${b.totalAmount}</span></div>`;
    }

    /* ---------- PAY (create order, then full-page redirect) ---------- */
    document.getElementById('payBtn').addEventListener('click', async function() {
        if (!_addressId) {
            setPayHint('No address selected.');
            return;
        }
        if (grandTotal <= 0) {
            setPayHint('Invalid order total.');
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Processing...';
        setPayHint('');

        try {
            const createRes = await fetch(API_ORDER_CREATE, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf()
                },
                body: JSON.stringify({
                    grant_total: grandTotal,
                    address_id: _addressId,
                    is_buy_now: _isBuyNow ? 1 : 0,
                    checkout_id: _checkoutId
                })
            });
            const createData = await createRes.json();

            if (createData.status !== 200 || !createData.payment_session_id) {
                setPayHint(createData.message || 'Could not start payment.');
                btn.disabled = false;
                btn.textContent = 'Pay Now';
                return;
            }

            // full-page redirect to Cashfree, then back to our return page
            const cashfree = Cashfree({
                mode: CASHFREE_MODE
            });
            await cashfree.checkout({
                paymentSessionId: createData.payment_session_id,
                redirectTarget: '_self' // full-page redirect
            });
            // browser navigates away here; nothing after this runs
        } catch (e) {
            setPayHint('Payment failed: ' + e.message);
            btn.disabled = false;
            btn.textContent = 'Pay Now';
        }
    });

    loadSummary();
</script>