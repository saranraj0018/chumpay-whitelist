<x-layouts.app>
    <div class="p-4">
        <div class="flex justify-between mb-4">
            <h2 class="text-xl font-bold">Coupons</h2>
            <button id="createCouponBtn" class="bg-[#363636] text-white px-4 py-2 rounded">
                Create
            </button>
        </div>
        <div class="overflow-x-auto bg-white rounded-xl shadow-md">
            <table class="w-full text-sm text-left text-gray-700 border-collapse">
                <thead>
                <tr class="bg-[#363636] text-white text-sm uppercase tracking-wider">
                        <th class="px-3 py-2">ID</th>
                        <th class="px-3 py-2">Code</th>
                        <th class="px-3 py-2">Discount</th>
                        <th class="px-3 py-2">Apply For</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Expires At</th>
                        <th class="px-3 py-2">User</th>
                        <th class="px-3 py-2">Created At</th>
                        <th class="px-3 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="couponTableBody" class="divide-y divide-gray-200">
                    @foreach ($coupons as $coupon)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3">{{ $coupon->coupon_code }}</td>
                            <td class="px-4 py-3">
                                @if ($coupon->discount_type == 1)
                                    {{ $coupon->discount_value }}
                                    <span class="text-green-600 font-bold">%</span>
                                @else
                                    <span class="text-blue-600 font-bold">₹</span>
                                    {{ number_format($coupon->discount_value, 2) }}
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                {{ $coupon->apply_for == 1 ? 'Subtotal' : 'Order' }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="px-3 py-1 text-xs font-semibold rounded-full
                                    {{ $coupon->status ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $coupon->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                {{ \Carbon\Carbon::parse($coupon->expires_at)->format('d M Y, h:i A') }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $coupon->admin?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $coupon->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td class="px-4 py-3 flex justify-center gap-4">
                                <!-- Edit -->
                                <button class="text-blue-600 hover:text-blue-800 transition editCouponBtn"
                                    data-id="{{ $coupon->id }}" data-code="{{ $coupon->coupon_code }}"
                                    data-type="{{ $coupon->discount_type }}"
                                    data-value="{{ $coupon->discount_value }}"
                                    data-description="{{ $coupon->description }}"
                                    data-apply="{{ $coupon->apply_for }}" data-max="{{ $coupon->max_price }}"
                                    data-min="{{ $coupon->min_price }}" data-order="{{ $coupon->order_count }}"
                                    data-expires="{{ $coupon->expires_at ? \Carbon\Carbon::parse($coupon->expires_at)->format('Y-m-d') : '' }}"
                                    data-status="{{ $coupon->status }}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                <!-- Delete -->

                                    <button class="text-red-600 hover:text-red-800 transition btnDeleteCoupon"
                                        data-id="{{ $coupon->id }}">
                                        <i class="fa-solid fa-delete-left"></i>
                                    </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $coupons->links() }}
        </div>
        @include('admin.coupon.modal')
    </div>
</x-layouts.app>

<script src="{{ asset('admin/js/coupon.js') }}?v={{ time() }}"></script>
