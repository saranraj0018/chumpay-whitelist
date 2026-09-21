@extends('frontend.app')

@section('content')

@php
$statusMap = [
'1' => [
'text' => 'Order Confirmed',
'type' => 'confirmed',
'class' => 'text-emerald-700 bg-emerald-100', // Green
],

'3' => [
'text' => 'Shipped',
'type' => 'shipped',
'class' => 'text-sky-700 bg-sky-100', // Sky Blue
],

'4' => [
'text' => 'Order Delivered',
'type' => 'delivered',
'class' => 'text-teal-700 bg-teal-100', // Teal
],

'5' => [
'text' => 'Cancelled',
'type' => 'cancelled',
'class' => 'text-rose-700 bg-rose-100', // Red/Rose
],

'6' => [
'text' => 'Refunded',
'type' => 'refunded',
'class' => 'text-amber-700 bg-amber-100', // Amber/Orange
],
];

$statusInfo = $statusMap[$order->status] ?? ['text' => 'Processing', 'class' => 'text-gray-600 bg-gray-50'];
$isDelivered = $order->status === '4';
$address = $order->Address;
$paymentMethod = strtoupper($order->payment?->status ?? 'Pending');
$itemsCount = $order->orderDetails->sum('quantity');
$pendingReviewItem = $isDelivered ? $order->orderDetails->first(fn($item) => !$item->review) : null;
$submittedReviewItem = $order->orderDetails->first(fn($item) => $item->review);
$reviewItem = $pendingReviewItem ?: $submittedReviewItem;
$submittedReview = $reviewItem?->review;
$submittedReviewImages =
$submittedReview && $submittedReview->image ? json_decode($submittedReview->image, true) : [];
$submittedReviewImages = is_array($submittedReviewImages) ? $submittedReviewImages : [];
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 py-10">

    <div class="grid grid-cols-1 lg:grid-cols-[1fr_22rem] gap-10">

        <!-- ================= LEFT SIDE ================= -->
        <div>
            <a href="{{ route('profile.orders') }}" class="inline-flex items-center gap-2 cursor-pointer pb-6 text-slate-600 hover:text-slate-950 font-semibold text-sm group transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-600 group-hover:text-slate-950 group-hover:-translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Back to Orders</span>
            </a>

            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Order ID</p>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ $order->order_id }}</h1>
                </div>
                <span
                    class="inline-flex w-fit items-center rounded-full px-3.5 py-1 text-xs font-bold shadow-2xs {{ $statusInfo['class'] }}">
                    {{ $statusInfo['text'] }}
                </span>
            </div>

            @if (session('success'))
            <div class="mb-5 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800 font-medium">
                {{ session('success') }}
            </div>
            @endif

            <div class="overflow-x-auto border border-slate-200/90 rounded-2xl bg-white shadow-xs">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-100 text-left text-[11px] uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5 font-bold">Product</th>
                            <th class="px-5 py-3.5 font-bold text-center">Qty</th>
                            <th class="px-5 py-3.5 font-bold text-right">Price</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($order->orderDetails as $item)
                        @php
                        $image = $item->product_image ?: $item->product?->main_image;
                        $review = $item->review;
                        @endphp
                        <tr class="align-top hover:bg-slate-50/50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex min-w-[16rem] gap-3.5 items-center">
                                    <img src="{{ $image ? asset('storage/' . $image) : '' }}"
                                        alt="{{ $item->product_name }}"
                                        class="h-18 w-18 rounded-xl border border-slate-100 object-contain bg-slate-50 p-1 flex-shrink-0">
                                    <div>
                                        <p class="font-bold text-slate-900 leading-snug">{{ $item->product_name }}</p>
                                        <p class="mt-1 text-xs text-slate-400">ITEM CODE : <span class="font-mono text-slate-600">{{ $item->product_id }}</span>
                                        </p>
                                        @if ($item->product_size)
                                        <p class="mt-1 text-xs font-medium text-slate-500">Size: <span class="text-slate-800 font-bold">{{ $item->product_size }}</span>
                                        </p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center font-bold text-slate-700">{{ $item->quantity }}</td>
                            <td class="px-5 py-4 text-right font-extrabold text-slate-900 whitespace-nowrap">
                                ₹{{ number_format((float) $item->net_amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($reviewItem)
            <div class="mt-8 bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-xs">
                <form action="{{ route('review.store', $reviewItem->product_id) }}" method="POST"
                    enctype="multipart/form-data" class="space-y-5 order-review-form">
                    @csrf
                    <input type="hidden" name="order_detail_id" value="{{ $reviewItem->id }}">

                    <div class="pb-4 border-b border-slate-100">
                        <p class="text-xs font-bold uppercase tracking-wider text-amber-600 mb-1">{{ $submittedReview ? 'Your review' : 'Reviewing' }}</p>
                        <h2 class="text-lg font-bold text-slate-900">{{ $reviewItem->product_name }}</h2>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-slate-900 mb-1">Rate your experience</h3>
                        <p class="text-xs text-slate-400 mb-3">
                            Share your thoughts with other customers
                        </p>

                        <div class="star-rating flex gap-2 text-2xl text-slate-200">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="{{ $submittedReview && $i <= $submittedReview->rating ? 'fa-solid text-amber-400' : 'fa-regular' }} fa-star {{ $submittedReview ? '' : 'cursor-pointer hover:text-amber-400' }} transition-colors"
                                data-value="{{ $i }}"></i>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" class="rating-input"
                            value="{{ $submittedReview?->rating ?? '' }}" required>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-slate-900 mb-2">Review this product</h3>

                        <textarea name="review" required maxlength="500" placeholder="Share your thoughts..."
                            class="review-text w-full h-36 border border-slate-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 rounded-xl p-4 outline-none transition-all resize-none text-sm"
                            {{ $submittedReview ? 'readonly' : '' }}>{{ $submittedReview?->review }}</textarea>
                        <p class="text-[11px] text-slate-400 mt-1">
                            <span class="char-count font-mono">{{ strlen($submittedReview?->review ?? '') }}</span> / 500
                            characters
                        </p>
                    </div>

                    <div>
                        @if ($submittedReview)
                        @if (count($submittedReviewImages))
                        <div class="flex flex-wrap gap-3">
                            @foreach ($submittedReviewImages as $reviewImage)
                            <img src="{{ $reviewImage ? asset('storage/' . $reviewImage) : '' }}"
                                alt="Review image"
                                class="h-24 w-24 rounded-xl border border-slate-200 object-cover shadow-2xs">
                            @endforeach
                        </div>
                        @endif
                        @else
                        <label
                            class="w-full h-24 border-2 border-dashed border-slate-200 hover:border-amber-400 hover:bg-amber-50/20 rounded-xl flex flex-col items-center justify-center text-slate-400 cursor-pointer transition-all">
                            <i class="fa-regular fa-image text-2xl mb-1 text-slate-400"></i>
                            <span class="upload-text text-xs font-semibold text-slate-600">Upload Photo</span>
                            <input type="file" name="image[]" accept="image/jpeg,image/png,image/webp"
                                class="review-image hidden" multiple>
                        </label>
                        <div class="image-preview mt-3 hidden flex flex-wrap gap-3"></div>
                        @endif
                    </div>

                    @unless ($submittedReview)
                    <button type="submit" class="bg-[#0f172a] hover:bg-amber-500 hover:text-slate-950 text-white font-semibold px-8 py-3 rounded-xl text-sm transition-all duration-200 shadow-md cursor-pointer">
                        Submit Review
                    </button>
                    @endunless
                </form>
            </div>
            @endif
        </div>

        <!-- ================= RIGHT SIDE ================= -->
        <div class="space-y-5">
            <!-- Delivery -->
            <div class="border border-slate-200/80 rounded-2xl p-5 sm:p-6 bg-white shadow-xs">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Delivery details</h3>
                <div class="my-3.5 border-t border-slate-100"></div>
                @if ($address)
                <p class="font-bold text-sm text-slate-900">{{ $address->name }}</p>
                <p class="text-xs text-slate-500 leading-relaxed mt-1">
                    {{ collect([$address->address, $address->landmark, $address->city, $address->state, $address->pincode])->filter()->implode(', ') }}
                </p>
                <p class="text-xs text-slate-500 mt-2">
                    Mobile:
                    <span class="font-bold text-slate-800">{{ $address->phone_number }}</span>
                </p>
                @else
                <p class="text-xs text-slate-400">Delivery address not available.</p>
                @endif
            </div>

            <!-- Price -->
            <div class="border border-slate-200/80 rounded-2xl p-5 sm:p-6 bg-white shadow-xs">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Price details</h3>
                <div class="my-3.5 border-t border-slate-100"></div>
                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between text-slate-500">
                        <span>Items</span>
                        <span class="text-slate-800 font-bold">{{ $itemsCount }}</span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Sub Total</span>
                        <span class="text-slate-900 font-bold">₹{{ number_format((float) $order->net_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Shipping</span>
                        <span class="text-slate-900 font-bold">₹{{ number_format((float) $order->shipping_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Taxes</span>
                        <span class="text-slate-900 font-bold">₹{{ number_format((float) $order->gst_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-emerald-600 font-medium">
                        <span>Coupon Discount</span>
                        <span class="font-bold">- ₹{{ number_format((float) $order->coupon_amount, 2) }}</span>
                    </div>
                </div>
                <div class="my-3.5 border-t border-slate-100"></div>
                <div class="flex justify-between text-sm font-bold">
                    <span class="text-slate-700">Total</span>
                    <span class="text-slate-950 text-base font-extrabold">₹{{ number_format((float) $order->gross_amount, 2) }}</span>
                </div>
                <div class="mt-4 flex justify-between items-center bg-slate-50 border border-slate-100 px-4 py-2.5 rounded-xl text-xs">
                    <span class="text-slate-500">Payment method</span>
                    <span class="text-slate-800 font-bold">{{ $paymentMethod }}</span>
                </div>
                <a href="{{ route('orders_invoice_download', $order->id) }}"
                    class="mt-5 w-full bg-[#0f172a] hover:bg-amber-500 hover:text-slate-950 text-white font-semibold rounded-xl py-3 text-xs uppercase tracking-wider transition-all duration-200 text-center block shadow-xs cursor-pointer">
                    Download Invoice
                </a>
            </div>
        </div>
    </div>
</div>
@if (session('toast_message'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast(
            "{{ session('toast_type') }}",
            "{{ session('toast_message') }}"
        );
    });
</script>
@endif
<script>
    (function() {
        document.querySelectorAll('.order-review-form').forEach(form => {
            const stars = form.querySelectorAll('.star-rating i');
            const ratingInput = form.querySelector('.rating-input');
            const reviewText = form.querySelector('.review-text');
            const charCount = form.querySelector('.char-count');
            const fileInput = form.querySelector('.review-image');
            const uploadText = form.querySelector('.upload-text');
            const imagePreview = form.querySelector('.image-preview');
            let current = parseInt(ratingInput.value) || 0;

            function paint(val) {
                stars.forEach(star => {
                    const value = parseInt(star.dataset.value);
                    star.classList.toggle('fa-solid', value <= val);
                    star.classList.toggle('fa-regular', value > val);
                    star.classList.toggle('text-yellow-400', value <= val);
                });
            }

            stars.forEach(star => {
                star.addEventListener('mouseenter', () => paint(parseInt(star.dataset.value)));
                star.addEventListener('mouseleave', () => paint(current));
                star.addEventListener('click', () => {
                    current = parseInt(star.dataset.value);
                    ratingInput.value = current;
                    paint(current);
                });
            });

            reviewText?.addEventListener('input', () => {
                charCount.textContent = reviewText.value.length;
            });

            fileInput?.addEventListener('change', () => {
                const maxSize = 2 * 1024 * 1024;
                const validFiles = [];
                imagePreview.innerHTML = '';
                Array.from(fileInput.files).forEach(file => {
                    if (file.size > maxSize) {
                        showToast(
                            `${file.name} exceeds 2MB. Please choose a smaller image.`,
                            'error');
                        return;
                    }
                    validFiles.push(file);
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.className =
                        'h-28 w-28 rounded-xl border border-gray-200 object-cover';
                    imagePreview.appendChild(img);
                });
                if (validFiles.length !== fileInput.files.length) {
                    const dt = new DataTransfer();
                    validFiles.forEach(file => dt.items.add(file));
                    fileInput.files = dt.files;
                }
                uploadText.textContent = validFiles.length ?
                    `${validFiles.length} image${validFiles.length > 1 ? 's' : ''} selected` :
                    'Upload Photo';
                imagePreview.classList.toggle('hidden', validFiles.length === 0);
            });

            // Explicit required-field validation (hidden inputs skip native HTML5 validation)
            form.addEventListener('submit', (e) => {
                let hasError = false;

                if (!current || current < 1) {
                    hasError = true;
                    showToast('Please select a star rating.', 'error');
                }

                if (!reviewText.value.trim()) {
                    hasError = true;
                    showToast('Please write a review.', 'error');
                }

                if (hasError) {
                    e.preventDefault();
                }
            });
        });
    })();
</script>
@endsection