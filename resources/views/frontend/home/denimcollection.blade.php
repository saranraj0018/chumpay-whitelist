<section class="py-8 sm:py-14 px-3 sm:px-6 lg:px-10 bg-white">
    <div class="max-w-[1220px] mx-auto">

        {{-- ── Header Row ── --}}
        <div class="flex flex-row items-center justify-between gap-3 sm:gap-4 mb-6 sm:mb-10">

            {{-- Left: title --}}
            <div>
                <p class="text-[0.65rem] sm:text-[0.70rem] uppercase tracking-[0.24em] text-slate-400 mb-1 font-bold flex items-center gap-1.5">
                    <span class="text-amber-500 font-extrabold">//</span> EXPLORE OUR
                </p>
                <h2 class="text-xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 leading-tight tracking-tight">
                    Denim
                    <span class="text-amber-500">Collection</span>
                </h2>
            </div>

            {{-- Right: VIEW ALL --}}
            <div>
                <a href="/shop"
                    class="inline-flex items-center gap-1.5 sm:gap-2 no-underline
                          px-4 py-2 sm:px-6 sm:py-2.5 rounded-full
                          border-2 border-slate-900 bg-white
                          text-[0.70rem] sm:text-[0.78rem] font-bold uppercase tracking-widest text-slate-900
                          transition-all duration-200
                          hover:bg-slate-900 hover:text-white shadow-sm hover:shadow active:scale-95">
                    <span>VIEW ALL</span> &nbsp;→
                </a>
            </div>
        </div>

        @php
        $denimList = collect($products ?? [])->filter(function($p) {
        $cat = strtolower($p['category'] ?? '');
        $name = strtolower($p['name'] ?? '');
        return str_contains($cat, 'denim') || str_contains($name, 'denim');
        });
        $displayList = $denimList->isNotEmpty() ? $denimList : collect($products ?? []);

        // Enforce maximum 4 containers and minimum 4 containers (8 products total)
        if ($displayList->count() > 0 && $displayList->count() < 8) {
            $padded=collect();
            while ($padded->count() < 8) {
                $padded=$padded->concat($displayList);
                }
                $displayList = $padded->take(8);
                }

                $pairedSlides = $displayList->chunk(2)->take(4);
                @endphp

                {{-- ── Stacking Cards Deck (Scroll-driven Bottom-to-Top Stack) ── --}}
                <div class="dc-stack-deck relative pb-6 sm:pb-10">

                    @forelse ($pairedSlides as $index => $slideProducts)
                    {{-- Clean stacking: pins nicely beneath the navbar on both mobile & desktop --}}
                    <div class="dc-stack-layer sticky top-[68px] sm:top-[96px] mb-5 sm:mb-[30px]"
                        style="z-index: {{ 10 + $index }};"
                        data-stack-index="{{ $index }}">

                        {{-- ── 1 Container with 2 Cards (Left & Right) ── --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3.5 sm:gap-6">

                            @foreach ($slideProducts as $product)
                            @php
                            $priceMin = (int) round($product['priceMin'] ?? ($product['price'] ?? 0));
                            $priceMax = (int) round($product['priceMax'] ?? $priceMin);
                            $oldPrice = (int) round($product['oldPrice'] ?? 0);
                            $hasRange = $priceMax > $priceMin;
                            $isWishlisted = $product['wishlisted'] ?? false;
                            $isBulk = ($product['productType'] ?? '') === 'bulk';
                            $rating = number_format($product['rating'] ?? 0, 1);
                            $ratingCount = $product['rating_count'] ?? 0;
                            $categoryName = !empty($product['category']) ? $product['category'] : 'Denim';
                            $description = !empty($product['description'])
                            ? \Illuminate\Support\Str::limit(strip_tags($product['description']), 110, '...')
                            : 'Ultra-clear, premium denim formulation engineered for exceptional all-day comfort, modern styling, and lasting durability.';
                            $discount = $product['discount'] ?? 0;
                            @endphp

                            {{-- ── Split Card (Left Image, Right Content on ALL screens) ── --}}
                            <div class="dc-card group relative bg-white border border-slate-200/90 rounded-2xl sm:rounded-[28px] p-3 sm:p-5 lg:p-6
                                shadow-[0_-4px_20px_rgba(0,0,0,0.05),0_12px_32px_rgba(0,0,0,0.07)]
                                hover:border-amber-400/80 transition-all duration-300
                                flex flex-row items-stretch gap-3 sm:gap-5 overflow-hidden">

                                {{-- Wishlist Button --}}
                                <button type="button"
                                    onclick="toggleHeart(event, this)"
                                    data-product-id="{{ $product['id'] ?? 0 }}"
                                    aria-label="Save to Wishlist"
                                    class="absolute top-2.5 right-2.5 sm:top-4 sm:right-4 z-20 w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center
                                   rounded-full shadow-sm border border-slate-100 cursor-pointer
                                   transition-all duration-200 hover:scale-110
                                   {{ $isWishlisted ? 'bg-red-50 text-red-500' : 'bg-white/90 backdrop-blur-sm text-gray-400 hover:text-red-500' }}">
                                    <i class="fa-solid fa-heart text-[0.65rem] sm:text-xs {{ $isWishlisted ? 'text-red-500' : '' }}"></i>
                                </button>

                                {{-- Left Side: Image Box (Compact on mobile, full size on desktop) --}}
                                <div class="relative w-[110px] xs:w-[125px] sm:w-[170px] md:w-[190px] lg:w-[185px] xl:w-[210px] aspect-square flex-shrink-0
                                    rounded-xl sm:rounded-2xl overflow-hidden bg-gradient-to-br from-slate-50 via-sky-50/20 to-amber-50/30
                                    border border-slate-100 flex items-center justify-center p-2 sm:p-3">

                                    <a href="{{ url('/shop/single-product/' . ($product['id'] ?? 0)) }}"
                                        class="w-full h-full flex items-center justify-center">
                                        <img src="{{ ($product['image'] ?? null)
                                            ? asset('storage/' . $product['image'])
                                            : asset('assets/images/gearupimages/gearupcards.svg') }}"
                                            alt="{{ $product['name'] ?? 'Product Image' }}"
                                            loading="lazy"
                                            class="w-full h-full object-contain transition-transform duration-500 ease-out
                                           group-hover:scale-105">
                                    </a>
                                </div>

                                {{-- Right Side: Content Split --}}
                                <div class="flex flex-col justify-between flex-1 min-w-0 pr-0 sm:pr-2">

                                    {{-- Top Section: Category Tag + Title + Description --}}
                                    <div>
                                        {{-- Category Pill Tag --}}
                                        <div class="mb-1 sm:mb-2">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 sm:px-3 sm:py-1 rounded-full
                                                 border border-slate-300/80 bg-slate-50/70 text-slate-700
                                                 text-[0.60rem] sm:text-[0.72rem] font-semibold tracking-wide">
                                                <span class="text-amber-500 font-bold">//</span> {{ $categoryName }}
                                            </span>
                                        </div>

                                        {{-- Title --}}
                                        <h3 class="text-[0.84rem] sm:text-base lg:text-lg font-extrabold text-slate-900 group-hover:text-amber-600
                                           transition-colors duration-200 leading-snug line-clamp-1 sm:line-clamp-2 mb-1 sm:mb-2">
                                            <a href="{{ url('/shop/single-product/' . ($product['id'] ?? 0)) }}"
                                                class="no-underline text-inherit hover:text-inherit">
                                                {{ $product['name'] ?? '' }}
                                            </a>
                                        </h3>

                                        {{-- Description (1 line on mobile, 2-3 on tablet/desktop) --}}
                                        <p class="text-slate-500 text-[0.68rem] sm:text-xs lg:text-[0.82rem] leading-snug sm:leading-relaxed line-clamp-1 sm:line-clamp-2 md:line-clamp-3 mb-2 sm:mb-3">
                                            {{ $description }}
                                        </p>
                                    </div>

                                    {{-- Bottom Section: Price & Action Split --}}
                                    <div class="pt-2 sm:pt-3 border-t border-slate-100 flex items-center justify-between gap-1.5 sm:gap-2 mt-auto">

                                        {{-- Price & Rating --}}
                                        <div class="flex flex-col min-w-0">
                                            <div class="flex items-baseline gap-1 sm:gap-1.5 flex-wrap">
                                                @if ($hasRange)
                                                <span class="text-xs sm:text-sm lg:text-base font-extrabold text-slate-900 whitespace-nowrap">
                                                    ₹{{ number_format($priceMin, 0) }} – ₹{{ number_format($priceMax, 0) }}
                                                </span>
                                                @else
                                                <span class="text-xs sm:text-sm lg:text-base font-extrabold text-slate-900 whitespace-nowrap">
                                                    ₹{{ number_format($priceMin, 0) }}
                                                </span>
                                                @if ($oldPrice > $priceMin)
                                                <span class="text-[0.65rem] sm:text-xs text-slate-400 line-through whitespace-nowrap">
                                                    ₹{{ number_format($oldPrice, 0) }}
                                                </span>
                                                @endif
                                                @endif
                                            </div>

                                            {{-- Star Rating --}}
                                            <div class="flex items-center gap-1 mt-0.5">
                                                <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-amber-400 fill-current flex-shrink-0" viewBox="0 0 24 24">
                                                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                                                </svg>
                                                <span class="text-[0.65rem] sm:text-[0.70rem] font-bold text-slate-700">{{ $rating }}</span>
                                                @if ($ratingCount > 0)
                                                <span class="text-[0.60rem] sm:text-[0.65rem] text-slate-400">({{ $ratingCount }})</span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Action Buttons --}}
                                        <div class="flex items-center gap-1.5 sm:gap-2 flex-shrink-0">

                                            {{-- Quick Add To Cart --}}
                                            <button type="button"
                                                onclick="addToCartFromCard(event, {{ $product['id'] ?? 0 }})"
                                                title="Add to Cart"
                                                class="dc-cart-btn w-7 h-7 sm:w-9 sm:h-9 flex items-center justify-center rounded-full
                                               border border-slate-200 bg-slate-50 text-slate-700
                                               hover:bg-slate-900 hover:text-white hover:border-slate-900
                                               transition-all duration-200 cursor-pointer shadow-sm">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3 sm:w-3.5 sm:h-3.5">
                                                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
                                                    <line x1="3" y1="6" x2="21" y2="6" />
                                                    <path d="M16 10a4 4 0 01-8 0" />
                                                </svg>
                                            </button>

                                            {{-- View Details ↗ Button --}}
                                            <a href="{{ url('/shop/single-product/' . ($product['id'] ?? 0)) }}"
                                                class="group/btn inline-flex items-center gap-1 sm:gap-1.5 px-3 py-1.5 sm:px-5 sm:py-2.5 rounded-full
                                              bg-[#0f172a] text-white text-[0.68rem] sm:text-[0.80rem] font-bold
                                              tracking-wide no-underline shadow-sm
                                              hover:bg-amber-500 hover:text-black hover:shadow-md hover:shadow-amber-500/20
                                              transition-all duration-200 whitespace-nowrap">
                                                <span>View</span>
                                                <span class="hidden xs:inline">Details</span>
                                                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 transition-transform duration-200 group-hover/btn:translate-x-0.5 group-hover/btn:-translate-y-0.5"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <line x1="7" y1="17" x2="17" y2="7"></line>
                                                    <polyline points="7 7 17 7 17 17"></polyline>
                                                </svg>
                                            </a>

                                        </div>

                                    </div>

                                </div>

                            </div>
                            {{-- ── END INDIVIDUAL CARD ── --}}
                            @endforeach

                            {{-- If single item in pair on large screen, keep layout balanced --}}
                            @if ($slideProducts->count() === 1)
                            <div class="hidden lg:block"></div>
                            @endif

                        </div>

                    </div>
                    @empty
                    <p class="text-gray-500 text-center py-10 w-full">No products found.</p>
                    @endforelse

                </div>

    </div>
</section>



<script>
    /* ── Quick Add to Cart Feedback ── */
    if (typeof window.addToCartFromCard === 'undefined') {
        window.addToCartFromCard = function(e, productId) {
            e.preventDefault();
            e.stopPropagation();
            const btn = e.currentTarget;
            if (!btn || btn.dataset.adding) return;
            btn.dataset.adding = '1';
            const orig = btn.innerHTML;
            btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-3.5 h-3.5"><polyline points="20 6 9 17 4 12"/></svg>`;
            btn.classList.add('!bg-emerald-600', '!text-white', '!border-emerald-600');
            setTimeout(() => {
                btn.innerHTML = orig;
                btn.classList.remove('!bg-emerald-600', '!text-white', '!border-emerald-600');
                delete btn.dataset.adding;
            }, 1500);
        };
    }
</script>