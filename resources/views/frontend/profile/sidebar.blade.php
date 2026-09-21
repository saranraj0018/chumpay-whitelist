<div class="w-full max-w-xs bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5">
    <!-- Profile Section -->
    <div class="flex items-center gap-3 mb-6 pb-5 border-b border-slate-100">
        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'User') }}&background=000000&color=ffffff"
            class="w-12 h-12 rounded-full object-cover ring-2 ring-amber-500/30 shadow-xs" alt="user">
        <div class="min-w-0">
            <h3 class="font-bold text-slate-900 truncate">{{ auth()->user()->name ?? 'Guest' }}</h3>
            <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email ?? '' }}</p>
        </div>
    </div>
    <!-- Menu -->
    <ul class="space-y-1.5">
        <li>
            <a href="{{ route('profile') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-sm font-medium
                {{ request()->routeIs('profile') ? 'bg-[#0f172a] text-white shadow-xs font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i class="fa-solid fa-user w-5 h-5 flex items-center justify-center {{ request()->routeIs('profile') ? 'text-amber-400' : 'text-slate-400' }}"></i>
                Profile
            </a>
        </li>
        <!-- Orders -->
        <li class="relative">
            @if ($userpendingOrderCount > 0)
                <span
                    class="absolute right-4 top-1/2 -translate-y-1/2
                           bg-rose-500 text-white text-[11px] font-bold
                           px-2 py-0.5 rounded-full shadow-xs">
                    {{ $userpendingOrderCount }}
                </span>
            @endif
            <a href="{{ route('profile.orders') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-sm font-medium
                {{ request()->routeIs('profile.orders') ? 'bg-[#0f172a] text-white shadow-xs font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i class="fa-solid fa-clock-rotate-left w-5 h-5 flex items-center justify-center {{ request()->routeIs('profile.orders') ? 'text-amber-400' : 'text-slate-400' }}"></i>
                Orders & Activity
            </a>
        </li>
        <!-- Address -->
        <li>
            <a href="{{ route('profile.manage-address') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-sm font-medium
                {{ request()->routeIs('profile.manage-address') ? 'bg-[#0f172a] text-white shadow-xs font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i class="fa-solid fa-map-location-dot w-5 h-5 flex items-center justify-center {{ request()->routeIs('profile.manage-address') ? 'text-amber-400' : 'text-slate-400' }}"></i>
                Manage Address
            </a>
        </li>
        <!-- Notifications -->
        <li class="relative">
            @if ($userpendingNotificationCount > 0)
                <span
                    class="absolute right-4 top-1/2 -translate-y-1/2
                           bg-rose-500 text-white text-[11px] font-bold
                           px-2 py-0.5 rounded-full shadow-xs">
                    {{ $userpendingNotificationCount }}
                </span>
            @endif
            <a href="{{ route('notification_list') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-sm font-medium
                {{ request()->routeIs('notification_list') ? 'bg-[#0f172a] text-white shadow-xs font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i class="fa-regular fa-bell w-5 h-5 flex items-center justify-center {{ request()->routeIs('notification_list') ? 'text-amber-400' : 'text-slate-400' }}"></i>
                Notifications
            </a>
        </li>
        <!-- Support -->
        <li class="relative">
            @if ($userpendingTicketCount > 0)
                <span
                    class="absolute right-4 top-1/2 -translate-y-1/2
                           bg-rose-500 text-white text-[11px] font-bold
                           px-2 py-0.5 rounded-full shadow-xs">
                    {{ $userpendingTicketCount }}
                </span>
            @endif
            <a href="{{ route('support_help_lists') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-sm font-medium
                {{ request()->routeIs('support_help_lists') ? 'bg-[#0f172a] text-white shadow-xs font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i class="fa-solid fa-headset text-lg w-5 h-5 flex items-center justify-center {{ request()->routeIs('support_help_lists') ? 'text-amber-400' : 'text-slate-400' }}"></i>
                Support & Help
            </a>
        </li>
        <!-- Logout -->
        <li class="pt-2 border-t border-slate-100">
            <form action="{{ route('web_user_logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-rose-600 hover:bg-rose-50 transition-colors text-sm font-medium">
                    <i class="fa-solid fa-arrow-right-from-bracket w-5 h-5 flex items-center justify-center text-rose-500"></i>
                    Logout
                </button>
            </form>
        </li>
    </ul>
</div>
