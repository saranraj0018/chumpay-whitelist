@extends('frontend.app')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    <div class="flex flex-col md:flex-row gap-6">
        <div class="w-full md:w-[25%]">
            @include('frontend.profile.sidebar')
        </div>
        <div class="w-full md:w-[75%]">
            <div class="w-full bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200/80">
                <h2 class="text-lg font-bold text-slate-900 mb-5 pb-3 border-b border-slate-100">Notifications</h2>
                <div
                    class="h-[65vh] overflow-y-auto space-y-3 no-scrollbar pr-1">
                    @if ($notifications->count() > 0)
                    @foreach ($notifications as $item)
                    @php
                    $text = strtolower($item->title . ' ' . $item->description);
                    if (str_contains($text, 'delivered')) {
                    $icon = 'success.png';
                    $iconClass = 'w-9 h-9';
                    } elseif (
                    str_contains($text, 'shipped') ||
                    str_contains($text, 'out for delivery')
                    ) {
                    $icon = 'orderout.png';
                    $iconClass = 'w-7 h-7';
                    } elseif (str_contains($text, 'confirmed') || str_contains($text, 'created')) {
                    $icon = 'confirmed.png';
                    $iconClass = 'w-7 h-7';
                    } elseif (str_contains($text, 'returned')) {
                    $icon = 'returned.png';
                    $iconClass = 'w-7 h-7';
                    } elseif (str_contains($text, 'rejected') || str_contains($text, 'cancelled')) {
                    $icon = 'rejected.png';
                    $iconClass = 'w-7 h-7';
                    } else {
                    $icon = 'pending.png';
                    $iconClass = 'w-7 h-7';
                    }
                    $isUnread = $item->status == 0;
                    @endphp
                    <div class="flex items-start sm:items-center gap-4 border rounded-2xl p-4 transition-all duration-200 {{ $isUnread ? 'bg-amber-50/40 border-amber-200/60 shadow-xs' : 'bg-white border-slate-100 hover:border-slate-200 hover:shadow-xs' }}">
                        <!-- ICON -->
                        <div class="relative flex-shrink-0 w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center">
                            <img src="{{ asset('assets/images/notificationsicon/' . $icon) }}"
                                class="{{ $iconClass }} object-contain">
                        </div>
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-slate-900 text-sm mb-0.5">{{ $item->title }}</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">{{ $item->description }}</p>
                            <span class="text-[11px] font-medium text-slate-400 mt-1 block">{{ $item->created_at->diffForHumans() }}</span>
                        </div>
                        <!-- Dot (unread indicator) -->
                        @if ($isUnread)
                        <span class="w-2.5 h-2.5 bg-amber-500 rounded-full shadow-xs ring-4 ring-amber-100 flex-shrink-0 mt-1 sm:mt-0"></span>
                        @endif
                    </div>
                    @endforeach
                    @else
                    <div class="flex flex-col items-center justify-center text-center py-16">
                        <img src="{{ asset('assets/images/notificationbg.png') }}" alt="No Notifications"
                            class="w-[200px] sm:w-[280px] mb-6 object-contain opacity-80">
                        <p class="text-slate-500 text-sm font-medium">You have no notifications yet.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection