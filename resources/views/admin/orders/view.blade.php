<x-layouts.app>
    <div class="p-4">
        <div class="flex justify-between mb-4">
            <h2 class="text-xl font-bold">Orders</h2>
        </div>

        <div class="overflow-x-auto bg-white rounded-xl shadow-md">
            <table class="w-full text-sm text-left text-gray-700 border-collapse">
                <thead>
                    <tr class="bg-[#363636] text-white text-sm uppercase tracking-wider">
                        <th class="px-3 py-2">ID</th>
                        <th class="px-3 py-2">Order ID</th>
                        <th class="px-3 py-2">User</th>
                        <th class="px-3 py-2">Phone</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Amount</th>
                        <th class="px-3 py-2 text-center">Order Date</th>
                        <th class="px-3 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="orderTableBody" class="divide-y divide-gray-200">
                    @foreach ($orders as $order)
                    @php
                    switch ($order->status) {
                    case '1':
                    $status = 'Ordered';
                    $badgeClass = 'bg-gray-100 text-gray-800';
                    break;
                    case '2':
                    $status = 'Inprogress';
                    $badgeClass = 'bg-blue-100 text-blue-800';
                    break;
                    case '3':
                    $status = 'Shipped';
                    $badgeClass = 'bg-blue-100 text-blue-800';
                    break;

                    case '4':
                    $status = 'Delivered';
                    $badgeClass = 'bg-green-100 text-green-800';
                    break;

                    case '5':
                    $status = 'Cancelled';
                    $badgeClass = 'bg-red-100 text-red-800';
                    break;

                    case '6':
                    $status = 'Refunded';
                    $badgeClass = 'bg-yellow-100 text-yellow-800';
                    break;

                    default:
                    $status = 'Unknown';
                    $badgeClass = 'bg-gray-100 text-gray-800';
                    break;
                    }
                    @endphp

                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 font-mono">{{ $order->order_id }}</td>
                        <td class="px-4 py-3">{{ $order->user->name ?? 'Guest' }}</td>
                        <td class="px-4 py-3">{{ $order->phone }}</td>

                        <td class="px-4 py-3">
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full {{ $badgeClass }}">
                                {{ $status }}
                            </span>
                        </td>

                        <td class="px-4 py-3">₹{{ number_format($order->gross_amount, 2) }}</td>
                        <td class="px-4 py-3">{{ optional($order->created_at)->format('d M Y h:i A') }}</td>

                        <td class="px-4 py-3 text-center">
                            <button class="text-blue-500 hover:text-blue-700 viewOrderBtn"
                                data-order='@json($order)'>
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $orders->links() }}
        </div>
        @include('admin.orders.modal')
    </div>
</x-layouts.app>
<script src="{{ asset('admin/js/order.js') }}?v={{ time() }}"></script>