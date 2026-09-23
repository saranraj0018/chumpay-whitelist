@php
use Illuminate\Support\Facades\Auth;
$status = false;
$isTransparent = request()->routeIs('home') || request()->is('/') || request()->is('about-us*');
@endphp

<!-- ================= MAIN NAVBAR HEADER (TAILWIND GLASSY & SCROLL ANIMATION) ================= -->
<header id="themeNavbarWrapper" class="fixed top-0 left-0 right-0 w-full z-[80] transition-all duration-500 ease-out pointer-events-none">
    <nav id="themeNavbarCard" class="w-full pointer-events-auto text-white transition-all duration-500 ease-out {{ $isTransparent ? 'bg-gradient-to-b from-black/60 via-black/25 to-transparent border-b border-white/5 shadow-none' : 'bg-black/80 backdrop-blur-xl border-b border-white/10 shadow-lg' }}">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div id="navbarInnerBar" class="flex items-center justify-between h-14 sm:h-16 lg:h-20 transition-all duration-500">

                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="inline-block transition-transform hover:scale-105 duration-300">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="RYH Textiles Logo"
                            class="h-5 sm:h-6 md:h-8 lg:h-10 w-auto object-contain">
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden lg:flex space-x-6 xl:space-x-8 text-sm lg:text-base font-medium">
                    <a href="{{ route('home') }}" class="text-white/90 hover:text-amber-500 transition-colors duration-200">Home</a>
                    <a href="/shop" class="text-white/90 hover:text-amber-500 transition-colors duration-200">Shop</a>
                    <a href="/about-us" class="text-white/90 hover:text-amber-500 transition-colors duration-200">About us</a>
                    <a href="/contact-us" class="text-white/90 hover:text-amber-500 transition-colors duration-200">Contact Us</a>
                </div>

                <!-- Right Section -->
                @if (Auth::check())
                <!-- LOGGED IN USER CONTROLS -->
                <div class="flex items-center gap-[12px] sm:gap-3">
                    <!-- Search -->
                    <form action="{{ route('shop') }}" method="GET" class="relative hidden sm:block nav-search-form"
                        id="navSearchForm">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                            autocomplete="off" id="navSearchInput"
                            class="bg-black/85 hover:bg-black focus:bg-black border border-white/30 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/40 text-white placeholder-gray-300 text-xs sm:text-sm w-32 sm:w-44 md:w-56 lg:w-64 rounded-full pl-9 pr-4 py-2 focus:outline-none shadow-lg backdrop-blur-md transition-all duration-300">
                        <button type="submit"
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-300 hover:text-amber-400 text-xs sm:text-sm bg-transparent border-0 p-0 cursor-pointer transition">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </form>

                    <!-- Profile -->
                    <a href="{{ url('/profile') }}"
                        class="w-9 h-9 flex items-center justify-center rounded-full bg-black/85 hover:bg-amber-500 hover:text-black border border-white/30 hover:border-amber-500 text-white backdrop-blur-md transition-all duration-300 shadow-md hover:shadow-lg hover:shadow-amber-500/30 hover:-translate-y-0.5"
                        title="Profile">
                        <i class="fa-regular fa-user text-xs sm:text-sm"></i>
                    </a>

                    <!-- Wishlist -->
                    <a href="{{ route('whishlist') }}"
                        class="w-9 h-9 flex items-center justify-center rounded-full bg-black/85 hover:bg-amber-500 hover:text-black border border-white/30 hover:border-amber-500 text-white backdrop-blur-md transition-all duration-300 shadow-md hover:shadow-lg hover:shadow-amber-500/30 hover:-translate-y-0.5"
                        title="Wishlist">
                        <i class="fa-regular fa-heart text-xs sm:text-sm"></i>
                    </a>

                    <!-- Cart -->
                    <div class="relative">
                        <a href="{{ url('/shop/cart') }}"
                            class="w-9 h-9 flex items-center justify-center rounded-full bg-black/85 hover:bg-amber-500 hover:text-black border border-white/30 hover:border-amber-500 text-white backdrop-blur-md transition-all duration-300 shadow-md hover:shadow-lg hover:shadow-amber-500/30 hover:-translate-y-0.5"
                            title="Cart">
                            <i class="fa-solid fa-cart-shopping text-xs sm:text-sm"></i>
                        </a>
                        <span id="navbarCartCount"
                            class="absolute -top-1.5 -right-1.5 bg-amber-500 text-black font-extrabold text-[10px] px-1.5 py-0.5 rounded-full leading-none shadow-md animate-pulse {{ $cartCount > 0 ? '' : 'hidden' }}">
                            {{ $cartCount > 99 ? '99+' : $cartCount }}
                        </span>
                    </div>

                    <!-- Mobile Hamburger Button -->
                    <div class="lg:hidden">
                        <button id="menu-btn"
                            class="w-9 h-9 flex items-center justify-center rounded-full bg-black/85 hover:bg-amber-500 hover:text-black border border-white/30 text-white transition duration-300 focus:outline-none shadow-md"
                            aria-label="Toggle Navigation Menu">
                            <i class="fa-solid fa-bars text-sm sm:text-base"></i>
                        </button>
                    </div>
                </div>
                @else
                <!-- GUEST CONTROLS -->
                <div class="flex items-center space-x-3 sm:space-x-4">
                    <!-- Search -->
                    <form action="{{ route('shop') }}" method="GET" class="relative hidden sm:block nav-search-form"
                        id="navSearchForm">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                            autocomplete="off" id="navSearchInput"
                            class="bg-black/85 hover:bg-black focus:bg-black border border-white/30 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/40 text-white placeholder-gray-300 text-xs sm:text-sm w-32 sm:w-44 md:w-56 lg:w-64 rounded-full pl-9 pr-4 py-2 focus:outline-none shadow-lg backdrop-blur-md transition-all duration-300">
                        <button type="submit"
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-300 hover:text-amber-400 text-xs sm:text-sm bg-transparent border-0 p-0 cursor-pointer transition">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </form>

                    <!-- Login Button -->
                    <a href="javascript:void(0)" id="LoginBtn"
                        class="bg-white text-black text-[10px] sm:text-xs md:text-sm font-semibold px-4 sm:px-6 md:px-8 py-2 rounded-full hover:bg-amber-500 hover:text-black hover:shadow-lg hover:shadow-amber-500/30 hover:-translate-y-0.5 transition-all duration-300 shadow-md">
                        Login
                    </a>

                    <!-- Mobile Hamburger Button -->
                    <div class="lg:hidden">
                        <button id="menu-btn"
                            class="w-9 h-9 flex items-center justify-center rounded-full bg-black/85 hover:bg-amber-500 hover:text-black border border-white/30 text-white transition duration-300 focus:outline-none shadow-md"
                            aria-label="Toggle Navigation Menu">
                            <i class="fa-solid fa-bars text-sm sm:text-base"></i>
                        </button>
                    </div>
                </div>
                @endif

            </div>
        </div>

        <!-- Mobile Menu Drawer (Theme Glassy) -->
        <div id="mobile-menu"
            class="hidden lg:hidden bg-black/90 backdrop-blur-2xl px-5 py-4 space-y-3 text-sm sm:text-base font-medium border-t border-white/10 rounded-b-2xl shadow-xl transition-all">
            <a href="{{ route('home') }}" class="block hover:text-amber-500 py-1 border-b border-white/5 transition">Home</a>
            <a href="/shop" class="block hover:text-amber-500 py-1 border-b border-white/5 transition">Shop</a>
            <a href="/about-us" class="block hover:text-amber-500 py-1 border-b border-white/5 transition">About us</a>
            <a href="/contact-us" class="block hover:text-amber-500 py-1 border-b border-white/5 transition">Contact Us</a>

            @if (!Auth::check())
            <div class="pt-2">
                <a href="javascript:void(0)" id="mobileLoginBtn"
                    class="w-full inline-flex items-center justify-center bg-amber-500 text-black font-semibold py-2 px-4 rounded-full hover:bg-amber-400 transition shadow-md shadow-amber-500/20 text-sm">
                    <i class="fa-solid fa-arrow-right-to-bracket mr-2"></i> Login to Account
                </a>
            </div>
            @endif
        </div>
    </nav>
</header>

@if (!$isTransparent)
<!-- Navbar Spacing for non-transparent pages (prevents content overlap) -->
<div class="pt-14 sm:pt-16 lg:pt-20"></div>
@endif

<!-- ================= LOGIN POPUP ================= -->
@include('frontend.components.loginpopup')

<!-- ================= JAVASCRIPT ================= -->
<script>
    // 1. Theme-Based Scroll Animation (Default top-0, animates down on scroll)
    (function() {
        const wrapper = document.getElementById("themeNavbarWrapper");
        const card = document.getElementById("themeNavbarCard");
        const innerBar = document.getElementById("navbarInnerBar");
        if (!wrapper || !card) return;

        const isTransparent = {{ $isTransparent ? 'true' : 'false' }};

        const defaultWrapperClasses = ["top-0"];
        const scrolledWrapperClasses = ["top-6", "px-4", "sm:px-6", "lg:px-8"];

        const defaultCardClasses = isTransparent
            ? ["w-full", "bg-gradient-to-b", "from-black/60", "via-black/25", "to-transparent", "border-b", "border-white/5", "shadow-none"]
            : ["w-full", "bg-black/80", "backdrop-blur-xl", "border-b", "border-white/10", "shadow-lg"];

        const scrolledCardClasses = ["max-w-7xl", "mx-auto", "rounded-2xl", "bg-black/85", "backdrop-blur-xl", "border", "border-amber-500/30", "shadow-2xl", "shadow-black/80"];

        let isScrolled = false;
        let ticking = false;


        function updateNavbar() {
            const shouldScroll = (window.pageYOffset || document.documentElement.scrollTop) > 40;
            if (shouldScroll === isScrolled) {
                ticking = false;
                return;
            }
            isScrolled = shouldScroll;

            if (isScrolled) {
                wrapper.classList.remove(...defaultWrapperClasses);
                wrapper.classList.add(...scrolledWrapperClasses);

                card.classList.remove(...defaultCardClasses);
                card.classList.add(...scrolledCardClasses);

                if (innerBar) {
                    innerBar.classList.remove("lg:h-20");
                    innerBar.classList.add("lg:h-16");
                }
            } else {
                wrapper.classList.remove(...scrolledWrapperClasses);
                wrapper.classList.add(...defaultWrapperClasses);

                card.classList.remove(...scrolledCardClasses);
                card.classList.add(...defaultCardClasses);

                if (innerBar) {
                    innerBar.classList.remove("lg:h-16");
                    innerBar.classList.add("lg:h-20");
                }
            }
            ticking = false;
        }

        window.addEventListener("scroll", function() {
            if (!ticking) {
                window.requestAnimationFrame(updateNavbar);
                ticking = true;
            }
        }, {
            passive: true
        });

        // Initial check on load
        updateNavbar();
    })();

    // 2. Login Popup Handling
    document.addEventListener("DOMContentLoaded", function() {
        const btn = document.getElementById("LoginBtn");
        const mobileBtn = document.getElementById("mobileLoginBtn");
        const popup = document.getElementById("LoginPopup");
        const panel = document.getElementById("LoginPanel");
        const closeBtn = document.querySelector(".closeLogin");

        function openPopup() {
            if (!popup || !panel) return;
            popup.classList.remove("hidden");
            setTimeout(() => {
                panel.classList.remove("scale-90", "opacity-0");
                panel.classList.add("scale-100", "opacity-100");
            }, 10);
        }

        function closePopup() {
            if (!popup || !panel) return;
            panel.classList.remove("scale-100", "opacity-100");
            panel.classList.add("scale-90", "opacity-0");
            setTimeout(() => {
                popup.classList.add("hidden");
            }, 300);
        }

        if (btn) btn.addEventListener("click", openPopup);
        if (mobileBtn) mobileBtn.addEventListener("click", openPopup);
        if (closeBtn) closeBtn.addEventListener("click", closePopup);

        if (popup && panel) {
            popup.addEventListener("click", function(e) {
                if (!panel.contains(e.target)) {
                    closePopup();
                }
            });
        }
    });

    // 3. Mobile Menu Toggle
    (function() {
        const menuBtn = document.getElementById('menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');

        if (menuBtn && mobileMenu) {
            menuBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                mobileMenu.classList.toggle('hidden');
            });
        }
    })();

    // 4. Reliable Search Form Submit
    (function() {
        const navSearchForms = document.querySelectorAll('.nav-search-form');
        navSearchForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const input = this.querySelector('input[name="search"]');
                const q = (input ? input.value : '').trim();
                if (!q) {
                    e.preventDefault();
                    window.location.href = "{{ route('shop') }}";
                }
            });
        });
    })();
</script>
