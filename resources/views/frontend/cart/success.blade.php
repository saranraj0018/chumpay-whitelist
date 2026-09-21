<section class="w-full m-[60px_0px] flex items-center justify-center px-4">
    <div class="w-full max-w-5xl flex flex-col md:flex-row items-center justify-center gap-8 md:gap-20">

        <!-- Left GIF -->
        <div class="w-full md:w-1/2 flex justify-center">
            <img src="{{ asset('assets/images/Order.gif') }}" alt="Order placed"
                class="w-[250px] sm:w-[300px] md:w-[380px] lg:w-[430px] object-contain">
        </div>

        <!-- Right Content -->
        <div class="w-full md:w-1/2 text-center md:text-left">
            <p class="text-slate-400 text-xs sm:text-sm font-semibold uppercase tracking-wider mb-2">
                We’re getting it ready for you!
            </p>

            <h2 class="text-slate-900 text-2xl sm:text-3xl lg:text-4xl font-extrabold leading-snug mb-5">
                Order placed successfully!
            </h2>

            <a href="/profile/orders" class="inline-block bg-[#0f172a] text-white text-xs sm:text-sm font-bold px-8 py-3 rounded-full hover:bg-amber-500 hover:text-black transition-all duration-200 shadow-md">
                View Orders
            </a>
        </div>
    </div>
</section>