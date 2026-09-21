@php
    $id = $product['id'] ?? 0;
    $name = $product['name'] ?? '';
    $category = $product['category'] ?? ($product['get_category']['name'] ?? '');
    $catId = $product['category_id'] ?? ($product['category'] ?? '');
    $fabric = $product['fabric'] ?? '';
    $priceMin = (int) round($product['priceMin'] ?? ($product['price'] ?? 0));
    $priceMax = (int) round($product['priceMax'] ?? $priceMin);
    $oldPrice = (int) round($product['oldPrice'] ?? 0);
    $rating = (float) ($product['rating'] ?? 0);
    $ratingCount = (int) ($product['rating_count'] ?? 0);
    $discount = (int) ($product['discount'] ?? 0);
    $stock = $product['stock'] ?? 'out';
    $image = $product['image'] ?? null;
    $colors = $product['colors'] ?? collect();
    $hasRange = $priceMax > $priceMin;
    $isWishlisted = $product['wishlisted'] ?? false;
    $isBulk = ($product['productType'] ?? '') === 'bulk';
    $perPiecePrice = (int) round($product['perPiecePrice'] ?? 0);
@endphp

<a href="{{ url('/shop/single-product/' . $id) }}"
    class="product-card group relative flex flex-col bg-white border border-slate-200/90 rounded-[22px] p-3.5 sm:p-4 no-underline text-inherit overflow-hidden shadow-[0_4px_16px_rgba(0,0,0,0.03)] hover:shadow-[0_16px_36px_rgba(0,0,0,0.08)] hover:border-amber-400/80 transition-all duration-300 ease-in-out hover:-translate-y-1"
    data-name="{{ $name }}" data-category="{{ $catId }}" data-fabric="{{ strtolower($fabric) }}"
    data-rating="{{ (int) $rating }}" data-discount="{{ $discount }}" data-price="{{ $priceMin }}"
    data-price-min="{{ $priceMin }}" data-price-max="{{ $priceMax }}" data-stock="{{ $stock }}">

    {{-- Discount badge --}}
    @if ($discount > 0)
        <span class="absolute top-3 left-3 z-10 text-[0.65rem] sm:text-[0.70rem] font-extrabold px-2.5 py-1 rounded-full bg-[#0f172a] text-white shadow-sm tracking-wide">
            {{ $discount }}% OFF
        </span>
    @endif

    {{-- Wishlist heart button --}}
    <button type="button" onclick="toggleHeart(event, this)" data-product-id="{{ $id }}"
        class="absolute top-3 right-3 z-10 w-8 h-8 flex items-center justify-center rounded-full shadow-sm border border-slate-100 cursor-pointer transition-all duration-200 hover:scale-110 {{ $isWishlisted ? 'bg-red-50 text-red-500' : 'bg-white/90 backdrop-blur-sm text-gray-400 hover:text-red-500' }}">
        <i class="fa-solid fa-heart text-xs {{ $isWishlisted ? 'text-red-500' : '' }}"></i>
    </button>

    {{-- Image Container --}}
    <div class="relative w-full aspect-square bg-gradient-to-br from-slate-50 via-sky-50/20 to-amber-50/20 border border-slate-100 rounded-2xl overflow-hidden mt-5 mb-3 flex items-center justify-center p-3">
        <img src="{{ $image ? asset('storage/' . $image) : asset('assets/images/productcards/productcards.svg') }}"
            alt="{{ $name }}" class="w-full h-full object-contain transition-transform duration-500 ease-out group-hover:scale-105">
    </div>

    {{-- Category Tag --}}
    @if(!empty($category))
        <p class="text-[0.68rem] uppercase tracking-wider text-slate-400 font-semibold mb-1 truncate">
            <span class="text-amber-500 font-bold">//</span> {{ $category }}
        </p>
    @endif

    {{-- Product Name --}}
    <h3 class="text-[0.88rem] sm:text-[0.95rem] font-bold text-slate-900 leading-snug line-clamp-1 mb-2 group-hover:text-amber-600 transition-colors" title="{{ $name }}">
        {{ $name }}
    </h3>

    {{-- Price & Rating Row --}}
    <div class="flex items-center justify-between gap-2 mt-auto pt-1">
        <div class="flex items-baseline flex-wrap gap-1.5 min-w-0">
            @if ($hasRange)
                <span class="text-[0.95rem] sm:text-[1.05rem] font-extrabold text-slate-900 whitespace-nowrap">
                    ₹{{ number_format($priceMin, 0) }} – ₹{{ number_format($priceMax, 0) }}
                </span>
            @else
                <span class="text-[0.95rem] sm:text-[1.05rem] font-extrabold text-slate-900 whitespace-nowrap">
                    ₹{{ number_format($priceMin, 0) }}
                </span>
                @if ($oldPrice > $priceMin)
                    <span class="text-[0.75rem] text-slate-400 line-through whitespace-nowrap">
                        ₹{{ number_format($oldPrice, 0) }}
                    </span>
                @endif
            @endif
            @if ($isBulk && $perPiecePrice > 0)
                <span class="text-[0.60rem] font-bold bg-blue-600 text-white px-1.5 py-0.5 rounded whitespace-nowrap">Bulk</span>
            @endif
        </div>

        <div class="flex items-center gap-1 flex-shrink-0 bg-slate-50 px-2 py-1 rounded-lg border border-slate-100">
            <svg class="w-3.5 h-3.5 text-amber-400 fill-current flex-shrink-0" viewBox="0 0 24 24">
                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
            </svg>
            <span class="text-xs font-bold text-slate-800">{{ number_format($rating, 1) }}</span>
            @if ($ratingCount > 0)
                <span class="text-[10px] text-slate-400">({{ $ratingCount }})</span>
            @endif
        </div>
    </div>

    {{-- Colors (if present) --}}
    @if ($colors->count())
        <div class="flex items-center gap-1 mt-2.5 pt-2 border-t border-slate-100">
            @foreach ($colors as $color)
                <span class="inline-block w-3.5 h-3.5 rounded-full border border-slate-200 shadow-2xs"
                    style="background-color: {{ \App\Support\ColorSwatch::css($color) }};" title="{{ $color }}"></span>
            @endforeach
        </div>
    @endif
</a>
