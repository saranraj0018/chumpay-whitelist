@extends('frontend.app')

@section('content')
    <section class="w-full">


        <!-- Mobile Banner -->
        <div class="relative block md:hidden w-full overflow-hidden">
            <img src="{{ asset('assets/images/contact-banner-mobile.png') }}" alt="Mobile Banner"
                class="w-full h-[220px] object-cover">
        </div>

        <!-- Main Section -->
        <div class="bg-slate-50 py-12 md:py-16 px-4 sm:px-6 lg:px-8">

            <div class="max-w-[1200px] mx-auto">

                <!-- FLEX CONTAINER -->
                <div class="flex flex-col lg:flex-row gap-8 lg:gap-12 items-stretch">

                    <!-- LEFT SIDE (60%) -->
                    <div class="w-full lg:w-[60%] bg-white p-6 sm:p-8 lg:p-10 rounded-3xl border border-slate-200/80 shadow-sm">
                        <p class="text-amber-600 text-xs font-bold uppercase tracking-wider mb-2">Connect With Chumpay</p>
                        <h2 class="text-slate-900 text-2xl md:text-3xl font-extrabold tracking-tight mb-8 leading-snug">
                            Let’s Build Something Great Together
                        </h2>

                        <form class="flex flex-col gap-5" action="https://api.web3forms.com/submit" method="POST">
                            <input type="hidden" name="access_key" value="0ce48cf1-588a-4736-b8d4-46c3df61190a">
                            <div class="flex flex-col md:flex-row gap-4">
                                <input type="text" placeholder="Your Name" name="name"
                                    class="w-full rounded-xl bg-slate-50/50 border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 outline-none focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all"
                                    required>
                                <input type="text" placeholder="Phone Number" name="phone"
                                    class="w-full rounded-xl bg-slate-50/50 border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 outline-none focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all">
                            </div>

                            <div class="flex flex-col md:flex-row gap-4">
                                <input type="email" placeholder="Email Address" name="email" required
                                    class="w-full rounded-xl bg-slate-50/50 border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 outline-none focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all">

                                <input type="text" placeholder="Subject" name="subject"
                                    class="w-full rounded-xl bg-slate-50/50 border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 outline-none focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all">

                            </div>

                            <textarea rows="6" placeholder="Your Message..." name="message"
                                class="w-full rounded-xl bg-slate-50/50 border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 outline-none resize-none focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all"></textarea>

                            <button type="submit"
                                class="w-full bg-[#0f172a] hover:bg-amber-500 hover:text-slate-950 text-white py-3.5 rounded-xl text-sm font-semibold transition-all duration-200 shadow-md cursor-pointer">
                                Submit Message
                            </button>
                        </form>
                    </div>

                    <!-- RIGHT SIDE (40%) -->
                    <div class="w-full lg:w-[40%] bg-[#0f172a] text-white p-6 sm:p-8 lg:p-10 rounded-3xl shadow-xl flex flex-col justify-between gap-8 min-h-full border border-slate-800">
                        <div class="space-y-6 sm:space-y-7">
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-amber-400 mb-2">Address</h3>
                                <p class="text-slate-300 text-xs sm:text-sm leading-relaxed flex items-start gap-2.5">
                                    <i class="fa-solid fa-location-dot mt-1 text-amber-400 flex-shrink-0"></i>
                                    <span>Chumpay Textiles Tiruppur, Tamil Nadu - 641601, India</span>
                                </p>
                            </div>
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-amber-400 mb-2">Email</h3>
                                <p class="text-slate-300 text-xs sm:text-sm leading-relaxed flex items-center gap-2.5 break-all sm:break-normal">
                                    <i class="fa-solid fa-envelope text-amber-400 flex-shrink-0"></i>
                                    <span>support@chumpay.com</span>
                                </p>
                            </div>
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-amber-400 mb-2">Working Hours</h3>
                                <p class="text-slate-300 text-xs sm:text-sm leading-relaxed flex items-center gap-2.5">
                                    <i class="fa-solid fa-business-time text-amber-400 flex-shrink-0"></i>
                                    <span>Mon – Sat: 9:00 AM - 6:00 PM</span>
                                </p>
                            </div>
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-amber-400 mb-2">Mobile Number</h3>
                                <p class="text-slate-300 text-xs sm:text-sm leading-relaxed flex items-center gap-2.5">
                                    <i class="fa-solid fa-phone text-amber-400 flex-shrink-0"></i>
                                    <span>+91 74490 78888</span>
                                </p>
                            </div>
                        </div>
                        <div class="pt-6 border-t border-slate-800">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-amber-400 mb-3">Social Media</h3>
                            <div class="flex items-center flex-wrap gap-3">
                                <a href="#"
                                    class="w-9 h-9 rounded-full bg-white/10 hover:bg-amber-500 hover:text-slate-950 flex items-center justify-center transition-all duration-200 text-white">
                                    <i class="fa-brands fa-youtube text-sm"></i>
                                </a>
                                <a href="#"
                                    class="w-9 h-9 rounded-full bg-white/10 hover:bg-amber-500 hover:text-slate-950 flex items-center justify-center transition-all duration-200 text-white">
                                    <i class="fa-brands fa-facebook-f text-sm"></i>
                                </a>
                                <a href="#"
                                    class="w-9 h-9 rounded-full bg-white/10 hover:bg-amber-500 hover:text-slate-950 flex items-center justify-center transition-all duration-200 text-white">
                                    <i class="fa-brands fa-instagram text-sm"></i>
                                </a>
                                <a href="#"
                                    class="w-9 h-9 rounded-full bg-white/10 hover:bg-amber-500 hover:text-slate-950 flex items-center justify-center transition-all duration-200 text-white">
                                    <i class="fa-brands fa-linkedin-in text-sm"></i>
                                </a>
                                <a href="#"
                                    class="w-9 h-9 rounded-full bg-white/10 hover:bg-amber-500 hover:text-slate-950 flex items-center justify-center transition-all duration-200 text-white">
                                    <i class="fa-brands fa-x-twitter text-sm"></i>
                                </a>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection
