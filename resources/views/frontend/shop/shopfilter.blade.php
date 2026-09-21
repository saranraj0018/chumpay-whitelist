{{-- Filters Sidebar Component --}}
<div class="pb-4 mb-4 border-b border-slate-200 hidden md:flex items-center justify-between">
    <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-1.5">
        <span class="text-amber-500 font-extrabold">//</span> Filters
    </h2>
    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Refine</span>
</div>

<!-- MOBILE FILTER HEADER -->
<div class="md:hidden flex justify-between items-center mb-4 p-3 border-b border-slate-200 bg-slate-50 rounded-xl">
    <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-1.5">
        <span class="text-amber-500 font-extrabold">//</span> Filters
    </h2>
    <button id="closeFilter" class="w-7 h-7 flex items-center justify-center rounded-full bg-slate-200 text-slate-700 hover:bg-slate-900 hover:text-white transition-all text-xs font-bold">
        ✕
    </button>
</div>

<div class="space-y-6">

    <!-- CATEGORIES -->
    <div class="pb-5 border-b border-slate-100">
        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-3 flex items-center justify-between">
            <span>Categories</span>
            @if(isset($categories))
            <span class="text-[10px] text-slate-400 font-normal">({{ $categories->count() }})</span>
            @endif
        </h3>
        <div class="space-y-2 max-h-52 overflow-y-auto pr-1 [scrollbar-width:thin]">
            @forelse($categories as $category)
            <label class="group flex items-center gap-2.5 text-sm text-slate-600 hover:text-slate-900 cursor-pointer select-none py-0.5 transition-colors">
                <input type="checkbox" class="filter-category w-4 h-4 rounded border-slate-300 accent-amber-500 focus:ring-0 cursor-pointer"
                    value="{{ $category->get_category->id }}">
                <span class="group-hover:translate-x-0.5 transition-transform">{{ $category->get_category->name }}</span>
            </label>
            @empty
            <p class="text-xs text-slate-400">No categories</p>
            @endforelse
        </div>
    </div>

    <!-- FABRIC (dynamic) -->
    @if (isset($fabrics) && $fabrics->count())
    <div class="pb-5 border-b border-slate-100">
        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-3">Fabric</h3>
        <div class="space-y-2 max-h-44 overflow-y-auto pr-1 [scrollbar-width:thin]">
            @foreach ($fabrics as $fabric)
            <label class="group flex items-center gap-2.5 text-sm text-slate-600 hover:text-slate-900 cursor-pointer select-none py-0.5 transition-colors">
                <input type="checkbox" class="filter-fabric w-4 h-4 rounded border-slate-300 accent-amber-500 focus:ring-0 cursor-pointer"
                    value="{{ strtolower($fabric) }}">
                <span class="capitalize group-hover:translate-x-0.5 transition-transform">{{ $fabric }}</span>
            </label>
            @endforeach
        </div>
    </div>
    @endif

    <!-- PRICE RANGE -->
    <div class="pb-5 border-b border-slate-100 w-[85%]">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-700">Price</h3>
            <button type="button" id="resetPriceBtn" class="text-[10px] font-bold text-amber-600 hover:text-amber-700 hover:underline">
                Reset
            </button>
        </div>

        <!-- Perfectly Centered Slider Bar -->
        <div class="relative h-6 flex items-center my-3 select-none">
            <!-- Background base track -->
            <div class="absolute inset-x-0 h-1.5 bg-slate-200 rounded-full"></div>

            <!-- Active glowing amber track -->
            <div id="rangeTrack" class="absolute h-1.5 bg-gradient-to-r from-amber-500 to-amber-600 rounded-full shadow-[0_1px_6px_rgba(245,158,11,0.45)] pointer-events-none"></div>

            <!-- Min Input -->
            <input type="range" id="minRange" min="{{ $priceMin ?? 100 }}" max="{{ $priceMax ?? 5000 }}"
                value="{{ $priceMin ?? 100 }}" step="25"
                class="range-slider">

            <!-- Max Input -->
            <input type="range" id="maxRange" min="{{ $priceMin ?? 100 }}" max="{{ $priceMax ?? 5000 }}"
                value="{{ $priceMax ?? 5000 }}" step="25"
                class="range-slider">
        </div>

        <!-- Default Price Badge Box -->
        <div class="flex items-center justify-between text-xs font-bold text-slate-800 bg-slate-50 px-3 py-2 rounded-xl border border-slate-100">
            <div class="flex flex-col">
                <span class="text-[10px] uppercase font-semibold text-slate-400">Min</span>
                <span id="minPrice" class="text-slate-900 font-extrabold">₹{{ $priceMin ?? 100 }}</span>
            </div>
            <span class="text-slate-300 font-light">—</span>
            <div class="flex flex-col items-end">
                <span class="text-[10px] uppercase font-semibold text-slate-400">Max</span>
                <span id="maxPrice" class="text-slate-900 font-extrabold">₹{{ $priceMax ?? 5000 }}</span>
            </div>
        </div>
    </div>

    <!-- REVIEWS -->
    <div class="pb-5 border-b border-slate-100">
        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-3">Customer Rating</h3>
        <div class="space-y-2">
            @for ($i = 5; $i >= 1; $i--)
            <label class="group flex items-center gap-2.5 text-sm text-slate-600 hover:text-slate-900 cursor-pointer select-none py-0.5 transition-colors">
                <input type="checkbox" class="filter-rating w-4 h-4 rounded border-slate-300 accent-amber-500 focus:ring-0 cursor-pointer"
                    value="{{ $i }}">
                <span class="flex items-center gap-1">
                    <span class="text-amber-400 text-sm tracking-wide">
                        @for ($s = 1; $s <= 5; $s++)
                            {{ $s <= $i ? '★' : '☆' }}
                            @endfor
                            </span>
                            <span class="text-xs text-slate-500 font-semibold ml-1">{{ $i }}.0{{ $i < 5 ? ' & up' : '' }}</span>
                    </span>
            </label>
            @endfor
        </div>
    </div>

    <!-- DISCOUNTS -->
    @if(isset($discountTiers) && $discountTiers->count())
    <div class="pb-5">
        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-3">Discounts</h3>
        <div class="space-y-2">
            @foreach($discountTiers as $tier)
            <label class="group flex items-center gap-2.5 text-sm text-slate-600 hover:text-slate-900 cursor-pointer select-none py-0.5 transition-colors">
                <input type="checkbox" class="filter-discount w-4 h-4 rounded border-slate-300 accent-amber-500 focus:ring-0 cursor-pointer"
                    value="{{ $tier['min'] }}-{{ $tier['max'] }}">
                <span class="group-hover:translate-x-0.5 transition-transform">{{ $tier['label'] }} Off</span>
            </label>
            @endforeach
        </div>
    </div>
    @endif

</div>



<script>
    (function() {
        const minRange = document.getElementById("minRange");
        const maxRange = document.getElementById("maxRange");
        const minPriceLbl = document.getElementById("minPrice");
        const maxPriceLbl = document.getElementById("maxPrice");
        const rangeTrack = document.getElementById("rangeTrack");
        const resetBtn = document.getElementById("resetPriceBtn");

        if (!minRange || !maxRange) return;

        const baseMin = parseInt(minRange.min) || 0;
        const baseMax = parseInt(maxRange.max) || 5000;
        const gap = 50;

        function updateSlider() {
            let minVal = parseInt(minRange.value);
            let maxVal = parseInt(maxRange.value);

            if (minVal > maxVal - gap) {
                minVal = maxVal - gap;
                minRange.value = minVal;
            }
            if (maxVal < minVal + gap) {
                maxVal = minVal + gap;
                maxRange.value = maxVal;
            }

            // Adjust z-index to avoid thumbs blocking each other
            if (minVal > (baseMax - baseMin) * 0.6) {
                minRange.style.zIndex = "25";
                maxRange.style.zIndex = "20";
            } else {
                minRange.style.zIndex = "20";
                maxRange.style.zIndex = "25";
            }

            const denom = (baseMax - baseMin) || 1;
            const percent1 = Math.max(0, Math.min(100, ((minVal - baseMin) / denom) * 100));
            const percent2 = Math.max(0, Math.min(100, ((maxVal - baseMin) / denom) * 100));

            if (rangeTrack) {
                rangeTrack.style.left = percent1 + "%";
                rangeTrack.style.width = (percent2 - percent1) + "%";
            }

            if (minPriceLbl) minPriceLbl.textContent = "₹" + minVal.toLocaleString('en-IN');
            if (maxPriceLbl) maxPriceLbl.textContent = "₹" + maxVal.toLocaleString('en-IN');
        }

        function triggerFilter() {
            minRange.dispatchEvent(new Event("input", {
                bubbles: true
            }));
            minRange.dispatchEvent(new Event("change", {
                bubbles: true
            }));
        }

        minRange.addEventListener("input", updateSlider);
        maxRange.addEventListener("input", updateSlider);

        // Reset button
        if (resetBtn) {
            resetBtn.addEventListener("click", () => {
                minRange.value = baseMin;
                maxRange.value = baseMax;
                updateSlider();
                triggerFilter();
            });
        }

        updateSlider();
    })();
</script>