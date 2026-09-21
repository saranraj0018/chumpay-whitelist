@props([
    'route' => '',
    'icon' => 'fa-home',
    'name' => 'Dashboard',
    'trigger' => false
])

<li class="px-3 w-full">
    @if ($route)
        <a href="{{ route($route) }}" class="flex w-full text-white items-center gap-3 px-2 py-2 rounded transition hover:bg-[#7e7e7e]
           @if(request()->routeIs($route)) bg-[#696969] @endif">
            <i class="fa {{ $icon }} w-5 text-center"></i>
            <span class="sidebar-text transition-all duration-300">{{ $name }}</span>
            @if ($trigger)
                <i class="fa fa-chevron-down w-5 sidebar-text"></i>
            @endif
        </a>
    @else
        <button type="submit" class="flex w-full text-white items-center gap-3 px-2 py-2 rounded transition hover:bg-[#7e7e7e]">
            <i class="fa {{ $icon }} w-5 text-center"></i>
            <span class="sidebar-text flex-1 text-left transition-all duration-300">{{ $name }}</span>
            @if ($trigger)
                <i class="fa fa-chevron-down w-5 sidebar-text"></i>
            @endif
        </button>
    @endif
</li>
