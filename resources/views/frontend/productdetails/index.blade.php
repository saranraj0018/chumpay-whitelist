@extends('frontend.app')
@section('content')
@php
$isBulk = $product->product_type === 'bulk';
// Default view (before a color is picked) shows every gallery image
// for the product. Picking a color narrows this down to just that
// color's own images — see COLOR_GALLERY/renderGallery() below.
$gallery = $product->product_gallery_image ?? collect();
$colorList = collect();
$sizeList = collect();

if ($isBulk) {
foreach ($product->bulk_product as $bulk) {
foreach ($bulk->bulk_product_variants as $bv) {
$attrName = strtolower(optional($bv->attribute_value?->get_attribute)->name ?? '');
$value = $bv->attribute_value->value ?? null;
if (!$value) {
continue;
}

if (in_array($attrName, ['color', 'colour'])) {
$colorList->push([
'name' => $value,
'value' => $value,
'attr_value_id' => $bv->attribute_value_id ?? null,
]);
} elseif ($attrName === 'size') {
$sizeList->push([
'value' => $value,
'variant_id' => $bulk->id ?? null,
'attr_value_id' => $bv->attribute_value_id ?? null,
]);
}
}
}
} else {
// Each product_variant row is one fixed size+color SKU, so size and
// color are collected here only for building the pickers; the actual
// variant_id for a chosen pair is resolved client-side from $variantMatrix.
foreach ($product->product_variant as $variant) {
foreach ($variant->variantValues as $val) {
$attrName = strtolower(optional($val->attribute_value?->get_attribute)->name ?? '');
$value = $val->attribute_value->value ?? null;
if (!$value) {
continue;
}

if (in_array($attrName, ['color', 'colour'])) {
$colorList->push([
'name' => $value,
'value' => $value,
'attr_value_id' => $val->attribute_value_id ?? null,
]);
} elseif ($attrName === 'size') {
$sizeList->push([
'value' => $value,
'attr_value_id' => $val->attribute_value_id ?? null,
]);
}
}
}
}

$colorList = $colorList->unique('attr_value_id')->values();
$sizeList = $sizeList->unique('attr_value_id')->values();

$bulkTiers = $isBulk ? $product->bulk_product->sortBy('minimum')->values() : collect();
$firstTier = $bulkTiers->first();
@endphp
<div class="max-w-7xl mx-auto px-6 md:px-12 py-10">
    <a href="/shop" class="flex items-center gap-2 cursor-pointer pb-[25px]">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-black" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        <span class="text-sm font-medium">Back</span>
    </a>
    <!-- GRID -->
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- PRODUCT IMAGE SECTION -->
        <div class="w-full lg:w-1/2 lg:max-w-[580px] relative">
            @php
            $mainImg = $product->main_image ? asset('storage/' . $product->main_image) : '';
            @endphp
            <div class="h-[300px] sm:h-[400px] flex justify-center rounded-[42px] overflow-hidden relative"
                id="imageZoomContainer">
                <img id="mainImage" src="{{ $mainImg }}" class="w-[85%] h-auto object-contain">
                <div id="zoomLens" class="hidden absolute w-[130px] h-[130px] border-2 border-amber-500/60 bg-amber-500/15 rounded-lg pointer-events-none"></div>
            </div>
            <div id="zoomResult"
                class="hidden absolute top-0 left-full ml-4 w-[420px] h-[420px] border rounded-xl bg-white shadow-lg z-50 bg-no-repeat">
            </div>
            <div id="galleryThumbs" class="h-[105px] flex gap-3 mt-4 overflow-x-auto justify-center">
                @if ($gallery->count())
                @foreach ($gallery as $img)
                <img src="{{ asset('storage/' . $img->image_path) }}" onclick="changeImage(this)"
                    class="w-[20%] h-full object-cover rounded-[10px] border cursor-pointer">
                @endforeach
                @else
                <img src="{{ $mainImg }}" onclick="changeImage(this)"
                    class="w-[20%] h-full object-cover rounded-[10px] border cursor-pointer">
                @endif
            </div>
        </div>
        <!-- PRODUCT DETAILS -->
        <div class="w-full lg:w-1/2 lg:max-w-[580px]">
            @if ($isBulk)
            <!-- ================= BULK PRODUCT ================= -->
            <h1 class="text-xl md:text-2xl w-[95%] font-semibold">{{ $product->name }}</h1>
            <div class="mt-3 text-2xl font-bold">
                ₹{{ number_format($product->per_piece_price ?? 0, 0) }}
                <span class="text-sm text-gray-500">/ Per Piece</span>
            </div>
            <!-- Quantity / Price tier table -->
            <div class="border rounded-lg mt-6 w-full sm:w-[60%] overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-3 text-left">Quantity</th>
                            <th class="p-3 text-left">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bulkTiers as $tier)
                        <tr class="border-t">
                            <td class="p-3">
                                {{ $tier->minimum }}{{ $tier->maximum ? '-' . $tier->maximum : '+' }}
                            </td>
                            <td class="p-3">
                                ₹{{ number_format($tier->sale_price ?? ($tier->regular_price ?? 0), 0) }}
                                <span
                                    class="text-gray-400 line-through text-sm">₹{{ number_format($tier->regular_price ?? 0, 0) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Bulk Order Matrix -->
            <div class="mt-6 border rounded-lg">
                <div class="p-6 rounded-lg max-w-3xl">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">Bulk Order Matrix</h2>
                            <p class="text-sm text-gray-500">
                                Select colors to show columns. Mix sizes and colors for better pricing.
                            </p>
                        </div>
                        <div>
                            <button type="button" onclick="clearMatrix()"
                                class="text-gray-400 text-sm hover:text-gray-600">✕ Clear All</button>
                        </div>
                    </div>
                    <!-- Color Pills -->
                    <div class="flex flex-wrap gap-3">
                        @foreach ($colorList as $color)
                        <span class="flex items-center gap-2 border rounded-full px-4 py-2 bg-white shadow-sm">
                            <span class="w-4 h-4 rounded-full border"
                                style="background-color: {{ \App\Support\ColorSwatch::css($color['value']) }};"></span>
                            <span class="text-sm font-medium">{{ $color['name'] }}</span> ✓
                        </span>
                        @endforeach
                    </div>
                </div>
                <!-- SIZE MATRIX -->
                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-[600px] w-full border text-sm text-center">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="p-2">Size</th>
                                @foreach ($colorList as $color)
                                <th>{{ $color['name'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sizeList as $size)
                            <tr class="border-t">
                                <td class="p-2">{{ $size['value'] }}</td>
                                @foreach ($colorList as $color)
                                <td>
                                    <input type="number" min="0"
                                        class="w-14 border rounded matrix-input"
                                        data-size="{{ $size['value'] }}"
                                        data-color="{{ $color['name'] }}">
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-100">
                            <tr>
                                <th class="p-2">Total</th>
                                @foreach ($colorList as $color)
                                <th class="col-total" data-color="{{ $color['name'] }}">0</th>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <!-- TOTAL + BUTTONS -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 gap-4">
                    <div class="flex items-center gap-8 justify-between sm:justify-start w-full sm:w-auto">
                        <div>
                            <p class="text-gray-400 text-xs font-semibold uppercase">Total Units</p>
                            <p id="totalUnits" class="text-lg font-bold text-gray-900">0</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs font-semibold uppercase">Total Cost</p>
                            <p class="text-lg font-bold text-gray-900">
                                ₹<span id="totalCost">0</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-3 w-full sm:w-auto">
                        <button type="button" onclick="addtoCart()"
                            class="flex-1 sm:flex-initial whitespace-nowrap border border-slate-300 text-slate-800 px-5 sm:px-8 py-3 rounded-xl text-xs sm:text-sm font-semibold hover:bg-slate-100 transition inline-block text-center">
                            Add to Cart
                        </button>
                        <button type="button" onclick="buyNowBulk()"
                            class="flex-1 sm:flex-initial whitespace-nowrap bg-[#0f172a] text-white px-5 sm:px-8 py-3 rounded-xl text-xs sm:text-sm font-semibold hover:bg-amber-500 hover:text-black transition-all duration-200 shadow-md inline-block text-center">
                            Buy now
                        </button>
                    </div>
                </div>
            </div>
            @else
            <!-- ================= SINGLE / VARIANT PRODUCT ================= -->
            <h1 class="text-xl md:text-2xl w-[95%] font-semibold">{{ $product->name }}</h1>
            <div class="flex items-center gap-3 mt-2">
                <span id="priceNow" class="text-xl font-bold">₹{{ number_format($formatted['price'], 0) }}</span>
                <span id="priceOld" class="line-through text-gray-400"
                    style="{{ $formatted['oldPrice'] > $formatted['price'] ? '' : 'display:none' }}">₹{{ number_format($formatted['oldPrice'], 0) }}</span>
                @if ($formatted['rating'] > 0)
                <span class="flex items-center text-yellow-500 text-sm">
                    <i class="fa-solid fa-star mr-1"></i>{{ number_format($formatted['rating'], 1) }}
                </span>
                @endif
            </div>
            <!-- Sizes -->
            @if ($sizeList->count())
            <p class="mt-4 text-gray-600 text-sm">Select Size</p>
            <div class="flex gap-2 mt-2">
                @foreach ($sizeList as $size)
                <button type="button" data-attr-id="{{ $size['attr_value_id'] }}"
                    class="w-10 h-10 border border-slate-300 rounded-full hover:border-amber-500 hover:text-amber-600 transition text-sm font-semibold size-btn">
                    {{ $size['value'] }}
                </button>
                @endforeach
            </div>
            @endif
            <!-- Colors -->
            @if ($colorList->count())
            <p class="mt-4 text-gray-600 text-sm">Select Color</p>
            <div class="flex gap-3 mt-2">
                @foreach ($colorList as $color)
                <span data-color-id="{{ $color['attr_value_id'] }}"
                    class="w-8 h-8 rounded-full cursor-pointer border color-btn"
                    style="background-color: {{ \App\Support\ColorSwatch::css($color['value']) }};"
                    title="{{ $color['name'] }}"></span>
                @endforeach
            </div>
            @endif
            <!-- All-in-One Single Line: Compact Qty + Add to Cart + Buy now -->
            <div class="flex items-center gap-2 sm:gap-3.5 mt-6 w-full">
                <!-- Compact Quantity Pill -->
                <div class="flex items-center bg-slate-100 rounded-xl px-1.5 sm:px-2 py-1.5 shrink-0 border border-slate-200 shadow-xs">
                    <button type="button" onclick="changeQty(-1)"
                        class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center text-slate-700 hover:bg-white hover:shadow-xs rounded-lg transition"
                        aria-label="Decrease quantity">
                        <i class="fa-solid fa-minus text-[10px] sm:text-xs"></i>
                    </button>
                    <span id="qty" class="px-2 sm:px-3 font-bold text-slate-900 text-xs sm:text-sm select-none">1</span>
                    <button type="button" onclick="changeQty(1)"
                        class="w-8 h-8 flex items-center justify-center text-slate-700 hover:bg-white hover:shadow-xs rounded-lg transition"
                        aria-label="Increase quantity">
                        <i class="fa-solid fa-plus text-[10px] sm:text-xs"></i>
                    </button>
                </div>

                <!-- Add to Cart Button -->
                <button type="button" onclick="addToCartSingle()"
                    class="flex-1 sm:flex-initial whitespace-nowrap min-w-0 flex items-center justify-center gap-1.5 sm:gap-2 border border-slate-300 rounded-xl px-2.5 sm:px-6 py-2.5 sm:py-3 text-xs sm:text-sm font-semibold text-slate-800 hover:bg-slate-100 transition shadow-xs">
                    <i class="fa-solid fa-cart-plus text-xs sm:text-sm shrink-0"></i>
                    <span>Add to Cart</span>
                </button>

                <!-- Buy Now Button -->
                <button type="button" onclick="buyNow()"
                    class="flex-1 sm:flex-initial whitespace-nowrap min-w-0 flex items-center justify-center gap-1.5 sm:gap-2 bg-[#0f172a] text-white px-3 sm:px-8 py-2.5 sm:py-3 rounded-xl text-xs sm:text-sm font-bold hover:bg-amber-500 hover:text-black transition-all duration-200 shadow-md">
                    <span>Buy now</span>
                </button>
            </div>
            @endif
            <!-- DESCRIPTION -->
            <div class="max-w-xl mt-4">
                <div class="border rounded-xl p-4 md:p-6 mb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Description</h2>
                    <p class="text-gray-600 text-sm mb-4">
                        {{ $product->description ?: 'No description available.' }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <div class="flex items-center gap-2 border rounded-full px-4 py-2 bg-white text-sm">
                        <i class="fa-solid fa-shield-halved text-gray-600"></i> Secure Payment
                    </div>
                    <div class="flex items-center gap-2 border rounded-full px-4 py-2 bg-white text-sm">
                        <i class="fa-solid fa-arrows-rotate text-gray-600"></i> Free Changes & Return
                    </div>
                    <div class="flex items-center gap-2 border rounded-full px-4 py-2 bg-white text-sm">
                        <i class="fa-solid fa-ruler text-gray-600"></i> Size & Fit
                    </div>
                    <div class="flex items-center gap-2 border rounded-full px-4 py-2 bg-white text-sm">
                        <i class="fa-solid fa-truck text-gray-600"></i> Free Shipping
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('frontend.productdetails.customerreviews')
@include('frontend.home.gearup')
<script>
    const PRODUCT_ID = {{ $product->id }};
    const API_ADD_CART = "{{ route('add_toCart') }}";
    const IS_LOGGED_IN = @json(auth()->check());
    const VARIANT_MATRIX = @json($variantMatrix ?? []);
    const COLOR_GALLERY = @json($colorGalleryMap ?? []);
    const COLOR_COVER = @json($colorCoverMap ?? []);
    const DEFAULT_GALLERY = @json($gallery->map(fn($img) => asset('storage/'.$img->image_path))->values());
    const DEFAULT_MAIN_IMAGE = @json($mainImg);
    let selectedSizeId = null;
    let selectedColorId = null;

    // A variant row is one fixed size+color SKU. Resolve the exact
    // variant_id for whatever size/color the shopper has picked so far
    // instead of sending size and color as independent, possibly
    // mismatched selections.
    function resolveVariant() {
        if (!VARIANT_MATRIX.length) return null;
        return VARIANT_MATRIX.find(v =>
            (!selectedSizeId || String(v.size_id) === String(selectedSizeId)) &&
            (!selectedColorId || String(v.color_id) === String(selectedColorId))
        ) || null;
    }

    const DEFAULT_PRICE = {{ (float) $formatted['price'] }};
    const DEFAULT_OLD_PRICE = {{ (float) $formatted['oldPrice'] }};

    // Price genuinely differs by color/size (each variant row has its own
    // regular/sale price) — refresh the displayed price on every pick
    // instead of leaving the initial server-rendered price static.
    function updatePriceDisplay() {
        const priceEl = document.getElementById('priceNow');
        const oldPriceEl = document.getElementById('priceOld');
        if (!priceEl || !oldPriceEl) return;

        const variant = resolveVariant();
        const price = variant ? variant.price : DEFAULT_PRICE;
        const oldPrice = variant ? variant.old_price : DEFAULT_OLD_PRICE;

        priceEl.textContent = '₹' + Math.round(price).toLocaleString('en-IN');
        if (oldPrice > price) {
            oldPriceEl.textContent = '₹' + Math.round(oldPrice).toLocaleString('en-IN');
            oldPriceEl.style.display = '';
        } else {
            oldPriceEl.style.display = 'none';
        }
    }

    function changeImage(el) {
        document.getElementById('mainImage').src = el.src;
    }

    // Show only the gallery images tied to the picked color; fall back to
    // the product's full gallery when that color has none of its own
    // (e.g. size-only products, or a color with no uploads). The color's
    // cover/main photo is kept out of the thumbnail strip — it's only
    // used to set the big hero image — so it doesn't show up twice.
    function renderGallery(images, coverImage) {
        const container = document.getElementById('galleryThumbs');
        const mainImg = document.getElementById('mainImage');
        if (!container) return;

        const thumbs = images && images.length ? images : DEFAULT_GALLERY;
        const hero = coverImage || (thumbs.length ? thumbs[0] : DEFAULT_MAIN_IMAGE);

        container.innerHTML = '';
        (thumbs.length ? thumbs : [DEFAULT_MAIN_IMAGE]).forEach(src => {
            const img = document.createElement('img');
            img.src = src;
            img.className = 'w-[20%] h-full object-cover rounded-[10px] border cursor-pointer';
            img.addEventListener('click', () => changeImage(img));
            container.appendChild(img);
        });

        if (mainImg) {
            mainImg.src = hero;
        }
    }

    // Hide size buttons that aren't offered in the selected color, and
    // clear the size selection if it's no longer valid for the new color.
    function updateSizeAvailability() {
        const sizeBtns = document.querySelectorAll('.size-btn');
        if (!selectedColorId || !VARIANT_MATRIX.length) {
            sizeBtns.forEach(b => b.classList.remove('hidden'));
            return;
        }

        const validSizeIds = new Set(
            VARIANT_MATRIX
            .filter(v => String(v.color_id) === String(selectedColorId))
            .map(v => String(v.size_id))
        );

        sizeBtns.forEach(b => {
            const isValid = !b.dataset.attrId || validSizeIds.has(String(b.dataset.attrId));
            b.classList.toggle('hidden', !isValid);
            if (!isValid && b.classList.contains('selected')) {
                b.classList.remove('selected', 'bg-black', 'text-white');
                if (String(selectedSizeId) === String(b.dataset.attrId)) {
                    selectedSizeId = null;
                }
            }
        });
    }

    function initMagnifier() {
        const img = document.getElementById('mainImage');
        const lens = document.getElementById('zoomLens');
        const result = document.getElementById('zoomResult');
        if (!img || !lens || !result) return;

        const zoomFactor = 2.5;

        function moveLens(e) {
            const rect = img.getBoundingClientRect();
            let x = e.clientX - rect.left;
            let y = e.clientY - rect.top;
            const lensW = lens.offsetWidth / 2;
            const lensH = lens.offsetHeight / 2;

            x = Math.max(lensW, Math.min(x, rect.width - lensW));
            y = Math.max(lensH, Math.min(y, rect.height - lensH));

            lens.style.left = (x - lensW) + 'px';
            lens.style.top = (y - lensH) + 'px';

            result.style.backgroundImage = `url('${img.src}')`;
            result.style.backgroundSize = `${rect.width * zoomFactor}px ${rect.height * zoomFactor}px`;
            result.style.backgroundPosition = `-${(x - lensW) * zoomFactor}px -${(y - lensH) * zoomFactor}px`;
        }

        function show() {
            if (window.innerWidth < 1024) return; // skip magnifier on mobile/tablet
            lens.classList.remove('hidden');
            result.classList.remove('hidden');
        }

        function hide() {
            lens.classList.add('hidden');
            result.classList.add('hidden');
        }

        img.addEventListener('mouseenter', show);
        img.addEventListener('mousemove', moveLens);
        img.addEventListener('mouseleave', hide);
    }
    document.addEventListener('DOMContentLoaded', initMagnifier);

    function changeQty(step) {
        const el = document.getElementById('qty');
        if (!el) return;
        let val = parseInt(el.innerText) + step;
        if (val < 1) val = 1;
        el.innerText = val;
    }
    document.querySelectorAll('.size-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('selected',
                'bg-black', 'text-white'));
            btn.classList.add('selected', 'bg-black', 'text-white');
            selectedSizeId = btn.dataset.attrId || null;
            updatePriceDisplay();
        });
    });
    document.querySelectorAll('.color-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.color-btn').forEach(b => b.classList.remove('selected',
                'ring-2', 'ring-black'));
            btn.classList.add('selected', 'ring-2', 'ring-black');
            selectedColorId = btn.dataset.colorId || null;
            updatePriceDisplay();
            updateSizeAvailability();
            renderGallery(COLOR_GALLERY[selectedColorId] || [], COLOR_COVER[selectedColorId] || null);
        });
    });
    /* ---------- shared size/color selection gate ---------- */
    const HAS_SIZES = document.querySelectorAll('.size-btn').length > 1;
    const HAS_COLORS = document.querySelectorAll('.color-btn').length > 1;

    function requireVariantSelection() {
        if (HAS_COLORS && !selectedColorId) {
            showToast('Please select a color', 'error');
            return false;
        }
        if (HAS_SIZES && !selectedSizeId) {
            showToast('Please select a size', 'error');
            return false;
        }
        return true;
    }
    /* ---------- shared auth gate ---------- */
    function requireLogin() {
        if (!IS_LOGGED_IN) {
            showToast('Please log in to continue...', 'error');
            const loginBtn = document.getElementById('LoginBtn') || document.getElementById('mobileLoginBtn');
            if (loginBtn) loginBtn.click();
            return false;
        }
        return true;
    }
    /* ---------- collect bulk matrix (only non-zero cells) ---------- */
    function collectMatrix() {
        const cells = [];
        document.querySelectorAll('.matrix-input').forEach(input => {
            const q = parseInt(input.value) || 0;
            if (q > 0) {
                cells.push({
                    size: input.dataset.size,
                    color: input.dataset.color,
                    qty: q
                });
            }
        });
        return cells;
    }
    /* ---------- BUY NOW ---------- */
    async function sendBuyNow(payload) {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const res = await fetch(API_ADD_CART, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (res.status === 401 || data.status === 401) {
                showToast('Session expired. Please login again.', 'error');
                const loginBtn = document.getElementById('LoginBtn') || document.getElementById('mobileLoginBtn');
                if (loginBtn) loginBtn.click();
                return;
            }

            if (data.status === 200 && data.checkout_id) {
                window.location.href = `/shop/delivery?is_buy_now=1&checkout_id=${data.checkout_id}`;
            } else {
                showToast(data.message || 'Something went wrong', 'error');
            }
        } catch (e) {
            showToast('Request failed: ' + e.message, 'error');
        }
    }

    function buyNow() {
        if (!requireLogin()) return;
        if (!requireVariantSelection()) return;
        const variant = resolveVariant();
        if (VARIANT_MATRIX.length && !variant) {
            showToast('Please select a valid size/color combination', 'error');
            return;
        }
        const qty = parseInt(document.getElementById('qty')?.innerText || '1');
        sendBuyNow({
            product_id: PRODUCT_ID,
            variant_id: variant ? variant.variant_id : null,
            color_variant_id: variant ? variant.color_id : null,
            quantity: qty,
            is_buy_now: true
        });
    }

    function buyNowBulk() {
        if (!requireLogin()) return;
        const qty = parseInt(document.getElementById('totalUnits')?.innerText || '0');
        if (qty <= 0) {
            showToast('Select at least one item.', 'error');
            return;
        }
        sendBuyNow({
            product_id: PRODUCT_ID,
            quantity: qty,
            matrix: collectMatrix(),
            is_buy_now: true
        });
    }
    /* ---------- ADD TO CART ---------- */
    async function sendAddToCart(payload) {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const res = await fetch(API_ADD_CART, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (res.status === 401 || data.status === 401) {
                showToast('Please log in to continue...', 'error');
                const loginBtn = document.getElementById('LoginBtn') || document.getElementById('mobileLoginBtn');
                if (loginBtn) loginBtn.click();
                return;
            }

            if (data.status === 200) {
                showToast(data.message || 'Added to cart', 'success');
                window.location.href = '/shop/cart';
            } else {
                showToast(data.message || 'Something went wrong', 'error');
            }
        } catch (e) {
            showToast('Request failed: ' + e.message, 'error');
        }
    }
    // single / variant add to cart
    function addToCartSingle() {
        if (!requireLogin()) return;
        if (!requireVariantSelection()) return;
        const variant = resolveVariant();
        if (VARIANT_MATRIX.length && !variant) {
            showToast('Please select a valid size/color combination', 'error');
            return;
        }
        const qty = parseInt(document.getElementById('qty')?.innerText || '1');
        sendAddToCart({
            product_id: PRODUCT_ID,
            variant_id: variant ? variant.variant_id : null,
            color_variant_id: variant ? variant.color_id : null,
            quantity: qty,
            is_buy_now: false
        });
    }
    // bulk add to cart
    function addtoCart() {
        if (!requireLogin()) return;
        const qty = parseInt(document.getElementById('totalUnits')?.innerText || '0');
        if (qty <= 0) {
            showToast('Select at least one item.', 'error');
            return;
        }
        sendAddToCart({
            product_id: PRODUCT_ID,
            quantity: qty,
            matrix: collectMatrix(),
            is_buy_now: false
        });
    }
    @if($isBulk)
    const tiers = [
        @foreach($bulkTiers as $tier)
        {
            min: {{ $tier->minimum ?? 0 }},
            max: {{ $tier->maximum ?? 'null' }},
            price: {{ $tier->sale_price ?? ($tier->regular_price ?? 0) }}
        },
        @endforeach
    ];
    const perPiecePrice = {{ $product->per_piece_price ?? 0 }};

    function calcCost(totalUnits) {
        if (totalUnits <= 0) {
            return {
                totalCost: 0
            };
        }
        for (const t of tiers) {
            const min = t.min;
            const max = (t.max === null) ? Infinity : t.max;
            if (totalUnits >= min && totalUnits <= max) {
                return {
                    totalCost: t.price
                };
            }
        }
        const ranged = tiers.filter(t => t.max !== null);
        const topTier = ranged.length ? ranged[ranged.length - 1] : null;
        if (topTier && totalUnits > topTier.max) {
            const extraUnits = totalUnits - topTier.max;
            return {
                totalCost: topTier.price + (extraUnits * perPiecePrice)
            };
        }
        return {
            totalCost: totalUnits * perPiecePrice
        };
    }

    function recalcMatrix() {
        let totalUnits = 0;
        const colTotals = {};

        document.querySelectorAll('.matrix-input').forEach(input => {
            const qty = parseInt(input.value) || 0;
            totalUnits += qty;
            const color = input.dataset.color;
            colTotals[color] = (colTotals[color] || 0) + qty;
        });

        document.querySelectorAll('.col-total').forEach(th => {
            th.innerText = colTotals[th.dataset.color] || 0;
        });

        const result = calcCost(totalUnits) || {
            totalCost: 0
        };
        const totalCost = Number(result.totalCost) || 0;

        const tuEl = document.getElementById('totalUnits');
        const tcEl = document.getElementById('totalCost');

        if (tuEl) tuEl.innerText = totalUnits;
        if (tcEl) tcEl.innerText = totalCost.toLocaleString('en-IN');
    }

    function clearMatrix() {
        document.querySelectorAll('.matrix-input').forEach(i => i.value = 0);
        recalcMatrix();
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.matrix-input').forEach(i => {
            i.addEventListener('input', recalcMatrix);
            i.addEventListener('change', recalcMatrix);
        });
        recalcMatrix();
    });
    @endif
</script>
@endsection