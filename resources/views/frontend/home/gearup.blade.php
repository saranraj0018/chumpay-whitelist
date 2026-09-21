<section class="gu-section relative overflow-hidden py-14 px-4 sm:px-6 lg:px-10 bg-white">
    <div class="max-w-[1220px] mx-auto">

        {{-- ── Header Row ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">

            {{-- Left: title --}}
            <div>
                <p class="text-[0.70rem] uppercase tracking-[0.24em] text-slate-400 mb-1.5 font-bold flex items-center gap-1.5">
                    <span class="text-amber-500 font-extrabold">//</span> OUR COLLECTION
                </p>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 leading-tight tracking-tight">
                    Gear up for
                    <span class="text-amber-500">Clothing</span>
                </h2>
            </div>

            {{-- Right: arrows + VIEW ALL --}}
            <div class="flex items-center gap-3 flex-shrink-0">
                <button id="gu-prev" aria-label="Previous"
                    class="w-10 h-10 flex items-center justify-center rounded-full
                               border border-slate-200 bg-white text-slate-700 shadow-sm
                               transition-all duration-200
                               hover:border-slate-900 hover:text-slate-900 hover:bg-slate-50 active:scale-95
                               disabled:opacity-30 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                </button>
                <button id="gu-next" aria-label="Next"
                    class="w-10 h-10 flex items-center justify-center rounded-full
                               bg-[#0f172a] text-white shadow-sm
                               transition-all duration-200
                               hover:bg-amber-500 hover:text-black active:scale-95
                               disabled:opacity-30 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                </button>
                <a href="/shop"
                    class="hidden sm:inline-flex items-center gap-2 no-underline
                          px-6 py-2.5 rounded-full
                          border-2 border-slate-900 bg-white
                          text-[0.78rem] font-bold uppercase tracking-widest text-slate-900
                          transition-all duration-200
                          hover:bg-slate-900 hover:text-white shadow-sm hover:shadow active:scale-95">
                    VIEW ALL &nbsp;→
                </a>
            </div>
        </div>

        {{-- ── Slider Track ── --}}
        <div id="gu-track"
            class="flex gap-5 overflow-x-auto scroll-smooth snap-x snap-mandatory pb-2 no-scrollbar">

            @forelse ($products as $product)
            @php
            $priceMin = (int) round($product['priceMin'] ?? ($product['price'] ?? 0));
            $priceMax = (int) round($product['priceMax'] ?? $priceMin);
            $oldPrice = (int) round($product['oldPrice'] ?? 0);
            $colors = $product['colors'] ?? collect();
            $hasRange = $priceMax > $priceMin;
            $isWishlisted = $product['wishlisted'] ?? false;
            $isBulk = ($product['productType'] ?? '') === 'bulk';
            $perPiecePrice = (int) round($product['perPiecePrice'] ?? 0);
            $rating = number_format($product['rating'] ?? 0, 1);
            $ratingCount = $product['rating_count'] ?? 0;
            $shortDesc = $product['short_description'] ?? ($product['description'] ?? '');
            $shortDesc = \Illuminate\Support\Str::limit(strip_tags($shortDesc), 30, '...');
            $categoryName = $product['category'] ?? ($product['get_category']['name'] ?? '');
            @endphp

            {{-- ── CARD ── --}}
            <a href="{{ url('/shop/single-product/' . ($product['id'] ?? 0)) }}"
                data-card
                class="group relative flex flex-col flex-shrink-0 snap-start
                       bg-white border border-slate-200/90 rounded-[24px] p-3.5 sm:p-4
                       no-underline text-inherit overflow-hidden
                       w-[72vw] sm:w-[42vw] md:w-[30vw] lg:w-[22vw] max-w-[280px]
                       shadow-[0_4px_20px_rgba(0,0,0,0.04)]
                       hover:shadow-[0_16px_36px_rgba(0,0,0,0.08)] hover:border-amber-400/80
                       transition-all duration-300 ease-in-out
                       hover:-translate-y-1.5">

                {{-- "Loved by All" badge --}}
                <div class="absolute top-3 left-3 z-10 flex items-center gap-1.5
                                bg-white/95 backdrop-blur-sm border border-slate-100 rounded-full
                                px-2.5 py-0.5 shadow-sm">
                    <span class="text-[0.62rem] sm:text-[0.68rem] font-bold text-slate-700 whitespace-nowrap">Loved by All</span>
                    <svg class="w-3 h-3 text-red-500 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5
                                     2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09
                                     C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5
                                     c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                    </svg>
                </div>

                {{-- Wishlist button --}}
                <button type="button"
                    onclick="toggleHeart(event, this)"
                    data-product-id="{{ $product['id'] ?? 0 }}"
                    class="absolute top-3 right-3 z-10 w-8 h-8 flex items-center justify-center
                           rounded-full shadow-sm border border-slate-100 cursor-pointer
                           transition-all duration-200 hover:scale-110
                           {{ $isWishlisted ? 'bg-red-50 text-red-500' : 'bg-white/90 backdrop-blur-sm text-gray-400 hover:text-red-500' }}">
                    <i class="fa-solid fa-heart text-xs {{ $isWishlisted ? 'text-red-500 active' : '' }}"></i>
                </button>

                {{-- Image container --}}
                <div class="relative w-full aspect-square bg-gradient-to-br from-slate-50 via-sky-50/20 to-amber-50/20 border border-slate-100 rounded-2xl overflow-hidden mt-7 mb-3.5 flex items-center justify-center p-3">
                    <img src="{{ ($product['image'] ?? null)
                                ? asset('storage/' . $product['image'])
                                : asset('assets/images/gearupimages/gearupcards.svg') }}"
                        alt="{{ $product['name'] ?? '' }}"
                        class="w-full h-full object-contain transition-transform duration-500 ease-out
                                    group-hover:scale-105">

                    {{-- Add to Cart slide-up overlay --}}
                    <div class="gc-overlay absolute inset-x-0 bottom-0 h-14 flex items-center justify-center
                                    bg-gradient-to-t from-black/50 to-transparent rounded-b-2xl z-10
                                    cursor-pointer"
                        onclick="addToCartFromCard(event, {{ $product['id'] ?? 0 }})">
                        <span class="gc-cart-pill flex items-center gap-1.5
                                         bg-[#0f172a] text-white
                                         text-[0.74rem] sm:text-[0.80rem] font-bold
                                         px-4 py-2 rounded-full shadow-lg
                                         whitespace-nowrap select-none
                                         transition-all duration-200 hover:bg-amber-500 hover:text-black">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                class="w-3.5 h-3.5 flex-shrink-0">
                                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
                                <line x1="3" y1="6" x2="21" y2="6" />
                                <path d="M16 10a4 4 0 01-8 0" />
                            </svg>
                            Add to cart
                        </span>
                    </div>
                </div>

                {{-- 1. Category Tag --}}
                @if(!empty($categoryName))
                <p class="text-[0.68rem] sm:text-[0.72rem] uppercase tracking-wider text-slate-400 font-semibold mb-1 truncate">
                    <span class="text-amber-500 font-bold">//</span> {{ $categoryName }}
                </p>
                @endif

                {{-- 2. Product Name --}}
                <h3 class="text-[0.88rem] sm:text-[0.95rem] font-extrabold text-slate-900 leading-snug line-clamp-1 mb-2 group-hover:text-amber-600 transition-colors"
                    title="{{ $product['name'] ?? '' }}">
                    {{ $product['name'] ?? '' }}
                </h3>

                {{-- 3. Price (Left) & Rating (Right) --}}
                <div class="flex items-center justify-between gap-2 mt-auto pt-1">
                    {{-- Price --}}
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
                        <span class="text-[0.62rem] font-bold bg-blue-600 text-white px-1.5 py-0.5 rounded whitespace-nowrap">Bulk</span>
                        @endif
                    </div>

                    {{-- Rating --}}
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-amber-400 fill-current flex-shrink-0" viewBox="0 0 24 24">
                            <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                        </svg>
                        <span class="text-[0.78rem] font-bold text-slate-800">{{ $rating }}</span>
                        @if ($ratingCount > 0)
                        <span class="text-[0.68rem] text-slate-400 hidden sm:inline">({{ $ratingCount }})</span>
                        @endif
                    </div>
                </div>

                {{-- Color swatches --}}
                @if ($colors->count())
                <div class="flex flex-wrap gap-1 mt-2">
                    @foreach ($colors as $color)
                    <span class="inline-block w-3 h-3 rounded-full border border-gray-300"
                        style="background-color:{{ \App\Support\ColorSwatch::css($color) }};"
                        title="{{ $color }}"></span>
                    @endforeach
                </div>
                @endif

            </a>
            {{-- ── END CARD ── --}}

            @empty
            <p class="text-gray-500 text-center py-10 w-full">No products found.</p>
            @endforelse

        </div>{{-- /gu-track --}}

        {{-- Timer bar only (hidden, drives auto-advance JS) --}}
        <div class="hidden">
            <span id="gu-pulse"></span>
            <div>
                <div id="gu-timer-bar" style="width:0%"></div>
            </div>
        </div>

        {{-- Mobile View All --}}
        <div class="text-center mt-8 sm:hidden">
            <a href="/shop"
                class="inline-flex items-center gap-2 no-underline
                      px-7 py-2.5 rounded-full border-2 border-slate-900 bg-white
                      text-[0.80rem] font-bold uppercase tracking-widest text-slate-900
                      transition-all duration-200 hover:bg-slate-900 hover:text-white shadow-sm">
                VIEW ALL &nbsp;→
            </a>
        </div>

    </div>
</section>



<script>
    (function() {
        const INTERVAL = 5000;
        const track = document.getElementById('gu-track');
        const btnPrev = document.getElementById('gu-prev');
        const btnNext = document.getElementById('gu-next');
        const timerBar = document.getElementById('gu-timer-bar');
        const pulse = document.getElementById('gu-pulse');
        const cards = Array.from(track ? track.querySelectorAll('[data-card]') : []);
        if (!track || !cards.length) return;

        /* ── Entrance animation ── */
        const io = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('gu-in');
                    io.unobserve(e.target);
                }
            });
        }, {
            threshold: 0.1
        });
        cards.forEach((c, i) => {
            c.style.animationDelay = i * 80 + 'ms';
            io.observe(c);
        });

        /* ── Helpers: ONE card at a time ── */
        const GAP = 20; // matches gap-5 (20px)
        const cardW = () => (cards[0]?.offsetWidth ?? 220) + GAP;
        const total = cards.length;

        // Which card index is currently "active" (leftmost fully visible)
        const curIdx = () => Math.round(track.scrollLeft / cardW());

        // Scroll to exact card index
        function scrollToCard(idx) {
            const clamped = Math.max(0, Math.min(idx, total - 1));
            track.scrollTo({
                left: clamped * cardW(),
                behavior: 'smooth'
            });
        }

        /* ── Sync UI on scroll ── */
        function syncUI() {
            const max = track.scrollWidth - track.clientWidth;
            btnPrev.disabled = track.scrollLeft <= 2;
            btnNext.disabled = track.scrollLeft >= max - 2;
        }
        track.addEventListener('scroll', syncUI, {
            passive: true
        });
        syncUI();

        /* ── Arrow buttons: one card at a time ── */
        btnPrev.addEventListener('click', () => scrollToCard(curIdx() - 1));
        btnNext.addEventListener('click', () => scrollToCard(curIdx() + 1));

        /* ── Auto-advance: one card at a time ── */
        let paused = false,
            rafId = null,
            startTs = null,
            elapsed = 0;

        function tick(now) {
            if (paused) return;
            if (!startTs) startTs = now;
            elapsed = now - startTs;
            timerBar.style.width = Math.min((elapsed / INTERVAL) * 100, 100) + '%';
            if (elapsed >= INTERVAL) {
                const next = (curIdx() + 1) % total; // wrap around
                scrollToCard(next);
                resetTimer();
                return;
            }
            rafId = requestAnimationFrame(tick);
        }

        function resetTimer() {
            cancelAnimationFrame(rafId);
            startTs = null;
            elapsed = 0;
            timerBar.style.transition = 'none';
            timerBar.style.width = '0%';
            requestAnimationFrame(() => {
                timerBar.style.transition = '';
                rafId = requestAnimationFrame(tick);
            });
        }

        function pause() {
            if (paused) return;
            paused = true;
            cancelAnimationFrame(rafId);
            pulse.style.background = '#6b7280';
            pulse.style.boxShadow = 'none';
        }

        function resume() {
            if (!paused) return;
            paused = false;
            pulse.style.background = '';
            pulse.style.boxShadow = '';
            startTs = null;
            rafId = requestAnimationFrame(tick);
        }

        track.addEventListener('mouseenter', pause);
        track.addEventListener('mouseleave', resume);

        rafId = requestAnimationFrame(tick);

        /* ── Drag-to-scroll ── */
        let isDown = false,
            dragStartX = 0,
            dragScrollLeft = 0;
        track.addEventListener('mousedown', e => {
            isDown = true;
            dragStartX = e.pageX - track.offsetLeft;
            dragScrollLeft = track.scrollLeft;
            track.style.cursor = 'grabbing';
            pause();
        });
        track.addEventListener('mouseleave', () => {
            isDown = false;
            track.style.cursor = '';
        });
        track.addEventListener('mouseup', () => {
            isDown = false;
            track.style.cursor = '';
            resume();
        });
        track.addEventListener('mousemove', e => {
            if (!isDown) return;
            e.preventDefault();
            track.scrollLeft = dragScrollLeft - (e.pageX - track.offsetLeft - dragStartX);
        });
    })();


    /* ── Add to Cart ── */
    function addToCartFromCard(e, productId) {
        e.preventDefault();
        e.stopPropagation();
        const pill = e.currentTarget.querySelector('.gc-cart-pill');
        if (!pill || pill.dataset.adding) return;
        pill.dataset.adding = '1';
        const orig = pill.innerHTML;
        pill.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-3.5 h-3.5 flex-shrink-0"><polyline points="20 6 9 17 4 12"/></svg> Added!`;
        pill.style.background = '#16a34a';
        pill.style.color = '#fff';
        setTimeout(() => {
            pill.innerHTML = orig;
            pill.style.background = '';
            pill.style.color = '';
            delete pill.dataset.adding;
        }, 1600);
        // fetch('/cart/add', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}, body: JSON.stringify({product_id: productId, quantity: 1}) });
    }
</script>