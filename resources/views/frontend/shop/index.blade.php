<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex flex-col md:flex-row gap-8 items-start">

        <!-- MOBILE FILTER TRIGGER -->
        <div class="md:hidden w-full">
            <button id="openFilter"
                class="w-full border border-slate-200 bg-white text-slate-800 rounded-xl py-3 font-bold text-sm flex justify-center items-center gap-2 shadow-sm hover:border-slate-900 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2l-7 7v5l-4 2v-7L3 6V4z" />
                </svg>
                Filters
            </button>
        </div>

        <!-- LEFT FILTER SIDEBAR -->
        <div id="filterSidebar"
            class="fixed md:sticky md:top-24 left-0 h-full md:h-auto w-full md:w-64 lg:w-60 bg-white z-50 md:z-auto flex-shrink-0
                   transform -translate-x-full md:translate-x-0 transition-transform duration-300 overflow-y-auto p-5 md:p-0 border-r md:border-r-0 border-slate-200 md:border-transparent">
            @include('frontend.shop.shopfilter')
        </div>

        <!-- RIGHT PRODUCT SECTION -->
        <div class="flex-1 min-w-0 w-full relative">

            <!-- Title & Search Bar -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight flex items-center gap-2">
                    Showing <span id="resultCount" class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-amber-100 text-amber-900 border border-amber-200">0</span> Results
                </h3>

                <!-- Search Box -->
                <div class="flex items-center bg-slate-50 border border-slate-200 rounded-full pl-4 pr-1.5 py-1 shadow-sm w-full sm:w-80 md:w-96 focus-within:border-amber-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-amber-500/20 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 mr-2.5 flex-shrink-0" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35m2.1-5.4a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z" />
                    </svg>
                    <input type="text" id="searchInput" placeholder="Search clothing..."
                        value="{{ request('search') }}"
                        class="flex-1 bg-transparent outline-none text-slate-800 placeholder-slate-400 text-sm font-medium">
                    <button type="button" id="searchBtn"
                        class="bg-[#0f172a] text-white text-xs sm:text-sm font-bold px-4 sm:px-5 py-2 rounded-full hover:bg-amber-500 hover:text-black transition-all duration-200 shadow-sm active:scale-95">
                        Search
                    </button>
                </div>
            </div>

            <!-- Active Filters Row -->
            <div id="activeFiltersContainer" class="flex items-center flex-wrap gap-2 mb-6 hidden">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 mr-1">Active:</span>
                <div id="activeFilters" class="flex items-center flex-wrap gap-2"></div>
                <button id="clearAllFilters" class="text-xs font-bold text-slate-400 hover:text-amber-600 underline ml-2 uppercase tracking-wider transition-colors">
                    Clear All
                </button>
            </div>

            <!-- NO PRODUCTS MESSAGE -->
            <div id="noProductsMessage" class="hidden flex items-center justify-center px-4 py-12">
                <div class="max-w-md w-full rounded-3xl bg-slate-50 border border-slate-200/80 p-8 sm:p-10 text-center">
                    <div class="flex justify-center mb-5">
                        <img src="{{ asset('assets/images/searchresult.svg') }}" class="w-48 h-auto object-contain" alt="No products">
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 mb-2">
                            No matching results
                        </h2>
                        <p class="text-sm text-slate-500 mb-6">
                            Try adjusting your filters or search keywords to find what you're looking for.
                        </p>
                        <button id="clearAllFiltersBtn2"
                            class="inline-flex items-center justify-center bg-[#0f172a] text-white text-xs sm:text-sm font-bold px-6 py-2.5 rounded-full hover:bg-amber-500 hover:text-black transition-all duration-200 shadow-sm active:scale-95">
                            Clear All Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- LOADER -->
            <div id="productLoader"
                class="hidden absolute inset-0 bg-white/70 backdrop-blur-xs flex items-center justify-center z-40 rounded-2xl">
                <div class="w-10 h-10 border-4 border-slate-200 border-t-amber-500 rounded-full animate-spin"></div>
            </div>

            <!-- PRODUCT GRID -->
            <div id="productGrid"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 overflow-auto pr-[2px]">

                @forelse($products as $product)
                @include('frontend.components.productcard', ['product' => $product])
                @empty
                <p class="col-span-full text-center text-slate-400 py-12">No products found.</p>
                @endforelse

            </div>

            <!-- PAGINATION -->
            <div id="pagination" class="flex justify-center items-center mt-10 gap-2 flex-wrap"></div>

        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        const products = document.querySelectorAll("#productGrid .product-card");

        const searchInput = document.getElementById("searchInput");
        const searchBtn = document.getElementById("searchBtn");

        const categoryFilters = document.querySelectorAll(".filter-category");
        const fabricFilters = document.querySelectorAll(".filter-fabric");
        const stockFilters = document.querySelectorAll(".filter-stock");
        const discountFilters = document.querySelectorAll(".filter-discount");
        const ratingFilters = document.querySelectorAll(".filter-rating");

        const resultCount = document.getElementById("resultCount");
        const noProductsMessage = document.getElementById("noProductsMessage");
        const productGrid = document.getElementById("productGrid");
        const pagination = document.getElementById("pagination");
        const loader = document.getElementById("productLoader");

        const activeFiltersDiv = document.getElementById("activeFilters");
        const activeFiltersContainer = document.getElementById("activeFiltersContainer");
        const clearAllFilters = document.getElementById("clearAllFilters");
        const clearAllFiltersBtn2 = document.getElementById("clearAllFiltersBtn2");

        const minRange = document.getElementById("minRange");
        const maxRange = document.getElementById("maxRange");

        const itemsPerPage = 9;
        let currentPage = 1;
        let totalPages = 1;

        // Strips currency symbols/commas/spaces, returns a clean float
        function parseNumber(val) {
            if (val === null || val === undefined || val === "") return 0;
            const cleaned = String(val).replace(/[^0-9.-]/g, "");
            const num = parseFloat(cleaned);
            return isNaN(num) ? 0 : num;
        }

        function getCheckedValues(filters) {
            return Array.from(filters)
                .filter(el => el.checked)
                .map(el => el.value.toLowerCase());
        }

        function filterProducts() {
            if (loader) loader.classList.remove("hidden");

            setTimeout(() => {
                try {
                    const searchValue = searchInput.value.toLowerCase();

                    const selectedCategories = getCheckedValues(categoryFilters);
                    const selectedFabric = getCheckedValues(fabricFilters);
                    const selectedStock = getCheckedValues(stockFilters);
                    const selectedDiscount = getCheckedValues(discountFilters);
                    const selectedRatings = getCheckedValues(ratingFilters);

                    const minPrice = minRange ? parseNumber(minRange.value) : 0;
                    const maxPrice = maxRange ? parseNumber(maxRange.value) : Infinity;

                    let visibleProducts = [];

                    products.forEach(product => {
                        const name = (product.dataset.name || "").toLowerCase();
                        const category = (product.dataset.category || "").toLowerCase();
                        const fabric = product.dataset.fabric || "";
                        const productMin = parseNumber(product.dataset.priceMin);
                        const productMax = parseNumber(product.dataset.priceMax || product.dataset.priceMin);
                        const rating = parseNumber(product.dataset.rating);
                        const discount = parseNumber(product.dataset.discount);
                        const stock = product.dataset.stock || "";

                        let show = true;

                        if (searchValue && !name.includes(searchValue) && !category.includes(searchValue)) {
                            show = false;
                        }
                        if (selectedCategories.length && !selectedCategories.includes(category))
                            show = false;
                        if (selectedFabric.length && !selectedFabric.includes(fabric))
                            show = false;
                        if (selectedStock.length && !selectedStock.includes(stock))
                            show = false;

                        if (selectedDiscount.length) {
                            const passesDiscount = selectedDiscount.some(range => {
                                const [lo, hi] = range.split('-').map(Number);
                                return discount >= lo && discount <= hi;
                            });
                            if (!passesDiscount) show = false;
                        }

                        if (selectedRatings.length) {
                            const passesRating = selectedRatings.some(r => Math.floor(rating) === parseInt(r));
                            if (!passesRating) show = false;
                        }

                        // RANGE OVERLAP: product qualifies if its [min,max] intersects the selected [minPrice,maxPrice]
                        if (productMax < minPrice || productMin > maxPrice) show = false;

                        if (show) visibleProducts.push(product);
                        product.style.display = "none";
                    });

                    if (resultCount) resultCount.innerText = visibleProducts.length;

                    if (visibleProducts.length === 0) {
                        if (noProductsMessage) noProductsMessage.classList.remove("hidden");
                        if (productGrid) productGrid.classList.add("hidden");
                        if (pagination) pagination.innerHTML = "";
                    } else {
                        if (noProductsMessage) noProductsMessage.classList.add("hidden");
                        if (productGrid) productGrid.classList.remove("hidden");
                    }

                    renderPagination(visibleProducts);
                    updateActiveFilters();

                } finally {
                    if (loader) loader.classList.add("hidden");
                }
            }, 300);
        }

        function updateActiveFilters() {
            if (!activeFiltersDiv) return;
            activeFiltersDiv.innerHTML = "";

            const allFilters = document.querySelectorAll(
                ".filter-category, .filter-fabric, .filter-stock, .filter-discount, .filter-rating"
            );

            let hasFilters = false;

            allFilters.forEach(filter => {
                if (filter.checked) {
                    hasFilters = true;
                    const chip = document.createElement("div");
                    chip.className =
                        "inline-flex items-center gap-1.5 bg-[#0f172a] text-white pl-3 pr-2 py-1 rounded-full text-xs font-semibold shadow-sm transition-all hover:bg-slate-800";
                    chip.innerHTML =
                        `${filter.parentElement.textContent.trim()} <span class="text-amber-400 hover:text-white cursor-pointer font-bold ml-1">✕</span>`;
                    chip.querySelector("span").addEventListener("click", () => {
                        filter.checked = false;
                        currentPage = 1;
                        filterProducts();
                    });
                    activeFiltersDiv.appendChild(chip);
                }
            });

            if (activeFiltersContainer) {
                activeFiltersContainer.classList.toggle("hidden", !hasFilters);
            }
        }

        function renderPagination(list) {
            if (!pagination) return;
            pagination.innerHTML = "";

            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;

            list.forEach((product, index) => {
                product.style.display = (index >= start && index < end) ? "" : "none";
            });

            totalPages = Math.ceil(list.length / itemsPerPage);

            function createButton(i) {
                const btn = document.createElement("button");
                btn.innerText = i;
                btn.className =
                    `px-3.5 py-1.5 text-xs sm:text-sm font-bold rounded-xl border transition-all duration-200 ${
                        i === currentPage
                            ? 'bg-[#0f172a] text-white border-[#0f172a] shadow-sm'
                            : 'bg-white text-slate-700 border-slate-200 hover:border-amber-500 hover:text-amber-600 hover:bg-amber-50/40'
                    }`;
                btn.addEventListener("click", () => {
                    currentPage = i;
                    renderPagination(list);
                    if (productGrid) {
                        window.scrollTo({
                            top: productGrid.offsetTop - 100,
                            behavior: 'smooth'
                        });
                    }
                });
                pagination.appendChild(btn);
            }

            function createDots() {
                const dots = document.createElement("span");
                dots.innerText = "...";
                dots.className = "px-2 text-slate-400 font-bold self-center";
                pagination.appendChild(dots);
            }

            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    createButton(i);
                } else if (i === currentPage - 2 || i === currentPage + 2) {
                    createDots();
                }
            }
        }

        document.querySelectorAll("input").forEach(input => {
            input.addEventListener("change", () => {
                currentPage = 1;
                filterProducts();
            });
        });

        if (minRange) minRange.addEventListener("input", () => {
            currentPage = 1;
            filterProducts();
        });
        if (maxRange) maxRange.addEventListener("input", () => {
            currentPage = 1;
            filterProducts();
        });

        if (searchBtn) {
            searchBtn.addEventListener("click", () => {
                currentPage = 1;
                filterProducts();
            });
        }
        if (searchInput) {
            searchInput.addEventListener("keyup", (e) => {
                if (e.key === "Enter") {
                    currentPage = 1;
                    filterProducts();
                }
            });
        }

        function clearAll() {
            document.querySelectorAll(
                    ".filter-category, .filter-fabric, .filter-stock, .filter-discount, .filter-rating")
                .forEach(filter => filter.checked = false);
            if (searchInput) searchInput.value = "";
            currentPage = 1;
            filterProducts();
        }

        if (clearAllFilters) clearAllFilters.addEventListener("click", clearAll);
        if (clearAllFiltersBtn2) clearAllFiltersBtn2.addEventListener("click", clearAll);

        filterProducts();
    });
</script>

<script>
    /* mobile filter drawer */
    const openFilter = document.getElementById("openFilter");
    const closeFilter = document.getElementById("closeFilter");
    const filterSidebar = document.getElementById("filterSidebar");

    if (openFilter && filterSidebar) {
        openFilter.addEventListener("click", () => {
            filterSidebar.classList.remove("-translate-x-full");
        });
    }
    if (closeFilter && filterSidebar) {
        closeFilter.addEventListener("click", () => {
            filterSidebar.classList.add("-translate-x-full");
        });
    }
</script>