<!-- ================= THEME-BASED FOOTER COMPONENT ================= -->
<footer class="theme-footer">
    {{-- ── Top Subtle Amber Accent Glow Line ── --}}
    <div class="theme-footer-glow-line"></div>

    <div class="max-w-7xl mx-auto px-6 py-12 lg:py-16">
        {{-- ── Grid Layout (2 cols on mobile = 2x2 grid, 6 cols on desktop) ── --}}
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-x-6 gap-y-10 lg:gap-12">

            {{-- ── Brand Column ── --}}
            <div class="col-span-2 lg:col-span-2 text-center lg:text-left">
                <div class="flex justify-center lg:justify-start items-center gap-2">
                    <img src="{{ asset('assets/images/logo.png') }}" class="h-9 md:h-10 w-auto object-contain brightness-110" alt="RYH Textiles Logo" />
                </div>
                <p class="mt-4 text-xs sm:text-sm text-slate-400 max-w-sm mx-auto lg:mx-0 leading-relaxed">
                    Premium men's fashion from Tiruppur's finest textile mills.
                    Factory-to-customer quality you can trust.
                </p>

                {{-- Social Icons with theme styling from common.css --}}
                <div class="flex justify-center lg:justify-start gap-3 mt-6">
                    <a href="#" aria-label="YouTube" class="theme-footer-social-btn">
                        <i class="fa-brands fa-youtube text-sm"></i>
                    </a>
                    <a href="#" aria-label="Facebook" class="theme-footer-social-btn">
                        <i class="fa-brands fa-facebook text-sm"></i>
                    </a>
                    <a href="#" aria-label="Instagram" class="theme-footer-social-btn">
                        <i class="fa-brands fa-instagram text-sm"></i>
                    </a>
                    <a href="#" aria-label="LinkedIn" class="theme-footer-social-btn">
                        <i class="fa-brands fa-linkedin text-sm"></i>
                    </a>
                    <a href="#" aria-label="Twitter" class="theme-footer-social-btn">
                        <i class="fa-brands fa-x-twitter text-sm"></i>
                    </a>
                </div>
            </div>

            {{-- ── 1. Shop Column ── --}}
            <div class="col-span-1">
                <h3 class="theme-footer-heading mb-4 flex items-center justify-center lg:justify-start gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    <span>Shop</span>
                </h3>
                <ul class="space-y-2.5">
                    <li><a href="/shop" class="theme-footer-link">Shirts</a></li>
                    <li><a href="/shop" class="theme-footer-link">T-Shirts</a></li>
                    <li><a href="/shop" class="theme-footer-link">Pants</a></li>
                    <li><a href="/shop" class="theme-footer-link">Trousers</a></li>
                    <li><a href="/shop" class="theme-footer-link">Innerwear</a></li>
                    <li><a href="/shop" class="theme-footer-link">Fabrics</a></li>
                </ul>
            </div>

            {{-- ── 2. Help Column ── --}}
            <div class="col-span-1">
                <h3 class="theme-footer-heading mb-4 flex items-center justify-center lg:justify-start gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    <span>Help</span>
                </h3>
                <ul class="space-y-2.5">
                    <li><a href="/about-us" class="theme-footer-link">About Us</a></li>
                    <li><a href="#" class="theme-footer-link">Fabric Guide</a></li>
                    <li><a href="#" class="theme-footer-link">Size Guide</a></li>
                    <li><a href="/profile/support-help" class="theme-footer-link">Contact & Support</a></li>
                    <li><a href="#" class="theme-footer-link">Shipping & Delivery</a></li>
                    <li><a href="#" class="theme-footer-link">Returns & Exchange</a></li>
                </ul>
            </div>

            {{-- ── 3. Contact Column ── --}}
            <div class="col-span-1">
                <h3 class="theme-footer-heading mb-4 flex items-center justify-center lg:justify-start gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    <span>Contact</span>
                </h3>
                <ul class="space-y-3.5 text-xs sm:text-sm text-slate-400">
                    <li class="flex items-start gap-2.5">
                        <i class="fa-solid fa-location-dot mt-1 text-amber-500 flex-shrink-0 text-xs"></i>
                        <span class="leading-relaxed">Textile Complex, Tiruppur Tamil Nadu 641601</span>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <i class="fa-solid fa-phone text-amber-500 flex-shrink-0 text-xs"></i>
                        <a href="tel:+917449078888" class="hover:text-amber-500 transition whitespace-nowrap">+91 74490 78888</a>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <i class="fa-solid fa-envelope text-amber-500 flex-shrink-0 text-xs"></i>
                        <a href="mailto:support@ryhtextiles.com" class="hover:text-amber-500 transition truncate">support@ryhtextiles.com</a>
                    </li>
                </ul>
            </div>

            {{-- ── 4. Available in Column ── --}}
            <div class="col-span-1">
                <h3 class="theme-footer-heading mb-4 flex items-center justify-center lg:justify-start gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    <span>Available in</span>
                </h3>
                <div class="space-y-3 flex flex-col items-center lg:items-start">
                    <a href="#" class="theme-footer-badge">
                        <img src="{{ asset('assets/images/footerimages/footerplaystorelogo.png') }}"
                            class="h-9 sm:h-10 w-auto rounded-lg" alt="Get it on Google Play" />
                    </a>
                    <a href="#" class="theme-footer-badge">
                        <img src="{{ asset('assets/images/footerimages/footerapplelogo.png') }}"
                            class="h-9 sm:h-10 w-auto rounded-lg" alt="Download on App Store" />
                    </a>
                </div>
            </div>

        </div>

        {{-- ── Bottom Copyright Strip ── --}}
        <div class="border-t mt-[30px] border-white/10 pt-6 text-center text-xs text-slate-500 leading-relaxed w-full flex flex-col sm:flex-row items-center justify-between gap-3">
            <p>© {{ date('Y') }} RYH Textiles. All rights reserved.</p>
            <p class="flex items-center gap-1.5 text-slate-400">
                <span>Crafted with</span>
                <i class="fa-solid fa-heart text-amber-500 text-2xs"></i>
                <span>for a greener tomorrow.</span>
            </p>
        </div>
    </div>
</footer>
