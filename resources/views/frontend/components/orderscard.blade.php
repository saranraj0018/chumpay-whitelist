<div class="max-w-7xl mx-8 sm:mx-auto py-8">
    @php
    $statusMap = [
    '1' => [
    'text' => 'Order Confirmed',
    'type' => 'confirmed',
    'class' => 'text-emerald-700 bg-emerald-100',
    ],
    '3' => [
    'text' => 'Shipped',
    'type' => 'shipped',
    'class' => 'text-sky-700 bg-sky-100',
    ],
    '4' => [
    'text' => 'Order Delivered',
    'type' => 'delivered',
    'class' => 'text-teal-700 bg-teal-100',
    ],
    '5' => [
    'text' => 'Cancelled',
    'type' => 'cancelled',
    'class' => 'text-rose-700 bg-rose-100',
    ],
    '6' => [
    'text' => 'Refunded',
    'type' => 'refunded',
    'class' => 'text-amber-700 bg-amber-100',
    ],
    ];
    @endphp

    @if ($orders->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-5 h-[70vh] overflow-auto">
        @foreach ($orders as $order)
        @php
        $statusInfo = $statusMap[$order->status] ?? [
        'text' => 'Processing',
        'type' => 'processing',
        'class' => 'text-blue-600 bg-blue-50',
        ];
        $isDelivered = $order->status === '4';
        $firstItem = $order->orderDetails->first();
        $productImage = $firstItem->product?->main_image ?? '';
        $productNames = $order->orderDetails->pluck('product_name')->take(2)->implode(', ');
        $extra =
        $order->orderDetails->count() > 2 ? ' +' . ($order->orderDetails->count() - 2) . ' more' : '';

        if ($isDelivered && $order->delivered_at) {
        $deliveryText =
        'Delivered on ' . \Carbon\Carbon::parse($order->delivered_at)->format("D, jS M 'y");
        } elseif ($order->shipped_at) {
        $deliveryText = 'Shipped on ' . \Carbon\Carbon::parse($order->shipped_at)->format("D, jS M 'y");
        } else {
        $deliveryText = 'Placed on ' . $order->created_at->format("D, jS M 'y");
        }
        @endphp

        <div class="bg-white border border-slate-200/90 rounded-[1.5rem] p-4 shadow-sm hover:shadow-md hover:border-amber-400/70 transition-all duration-300 flex flex-col justify-between">

            <!-- Product Image -->
            <div class="w-full h-[260px] bg-slate-50/60 rounded-xl overflow-hidden flex items-center justify-center p-2">
                <img src="{{ $productImage ?? null ? asset('storage/' . $productImage) : '' }}" alt="product"
                    class="w-full h-full object-contain">
            </div>

            <!-- Status -->
            <div class="mt-3 flex items-center gap-2">
                @if ($statusInfo['type'] === 'confirmed')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                        <path
                            d="M12 2a10 10 0 100 20 10 10 0 000-20zm4.59 7.58l-5.66 5.66a1 1 0 01-1.42 0l-2.83-2.83a1 1 0 111.41-1.41l2.12 2.12 4.95-4.95a1 1 0 011.43 1.41z" />
                    </svg>
                    {{ $statusInfo['text'] }}
                </span>
                @elseif(in_array($statusInfo['type'], ['cancelled', 'refunded', 'returned']))
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 fill-current"
                        viewBox="0 0 24 24">
                        <path
                            d="M12 2a10 10 0 100 20 10 10 0 000-20zm3.54 12.12a1 1 0 01-1.42 1.42L12 13.41l-2.12 2.13a1 1 0 01-1.42-1.42L10.59 12 8.46 9.88a1 1 0 011.42-1.42L12 10.59l2.12-2.13a1 1 0 011.42 1.42L13.41 12l2.13 2.12z" />
                    </svg>
                    {{ $statusInfo['text'] }}
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 fill-current"
                        viewBox="0 0 24 24">
                        <path
                            d="M3 7.5A1.5 1.5 0 014.5 6h11A1.5 1.5 0 0117 7.5V8h1.38a2 2 0 011.79 1.11l1.45 2.89a2 2 0 01.21.89V16a2 2 0 01-2 2h-.18a3 3 0 01-5.64 0H9.82a3 3 0 01-5.64 0H4a2 2 0 01-2-2V7.5zm2 0V16h.18a3 3 0 015.64 0h4.36a3 3 0 015.64 0H21v-3.11L19.55 10H17v2a1 1 0 11-2 0V8H5v-.5z" />
                    </svg>
                    {{ $statusInfo['text'] }}
                </span>
                @endif
            </div>

            <!-- Delivery Date -->
            <p class="mt-2 text-[0.95rem] font-bold text-slate-900 leading-snug">
                {{ $deliveryText }}
            </p>

            <!-- Order ID -->
            <p class="mt-0.5 text-xs text-slate-400 font-medium">ID: {{ $order->order_id }}</p>

            <!-- Product Names -->
            <p class="mt-1 text-sm text-slate-600 leading-5 line-clamp-2">
                {{ $productNames }}{{ $extra }}
            </p>

            <!-- Buttons -->
            <div class="mt-4 space-y-2 pt-2 border-t border-slate-100">
                <a href="{{ route('profile.order.status', ['order' => $order->id]) }}"
                    class="w-full block text-center rounded-full border border-slate-300 py-2.5 text-sm font-semibold text-slate-800 bg-white hover:bg-[#0f172a] hover:text-white hover:border-[#0f172a] transition duration-200 shadow-xs">
                    View Order
                </a>
                @if ($isDelivered)
                <a href="{{ route('orders_invoice_download', $order->id) }}"
                    class="w-full block text-center rounded-full border border-slate-200 py-2 text-sm font-medium text-slate-600 bg-slate-50 hover:bg-amber-500 hover:text-black hover:border-amber-500 transition duration-200">
                    Download Invoice
                </a>
                @else
                <span
                    class="w-full block text-center rounded-full border border-slate-200 py-2 text-sm font-medium text-slate-400 bg-slate-100/70 cursor-not-allowed"
                    title="Invoice will be available once the order is delivered">
                    Download Invoice
                </span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="w-full h-[40vh] flex items-center justify-center">
        <div class="flex flex-col md:flex-row items-center gap-8 px-4">
            <div class="flex justify-center">
                <img src="{{ asset('assets/images/noorders.png') }}" alt="No Orders"
                    class="w-[220px] sm:w-[280px] md:w-[340px] object-contain">
            </div>
            <div class="text-center md:text-left">
                <h2 class="text-[28px] sm:text-[32px] font-bold text-slate-900">No orders yet</h2>
                <p class="mt-2 text-sm text-slate-400 max-w-[320px] leading-6">
                    You haven't placed any orders yet. Start shopping to fill this space!
                </p>
                <a href="/shop"
                    class="inline-block mt-5 bg-[#0f172a] text-white text-sm font-semibold px-7 py-3 rounded-full hover:bg-amber-500 hover:text-black transition-all duration-200 shadow-md">
                    Start Shopping
                </a>
            </div>
        </div>
    </div>
    @endif
</div>