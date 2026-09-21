<x-layouts.app>
<div class="p-4">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">Shipping Rules</h2>
         <div class="flex gap-3">
             <button id="openPlatformBtn" class="bg-[#363636] text-white px-4 py-2 rounded">
                Platform Fee
             </button>
             <button id="openShippingBtn" class="bg-[#363636] text-white px-4 py-2 rounded">
                 Default Shipping
             </button>
             <button id="openTaxBtn" class="bg-[#363636] text-white px-4 py-2 rounded">
            Tax
        </button>
        <button id="createShippingBtn" class="bg-[#363636] text-white px-4 py-2 rounded">
            Create
        </button>
         </div>
    </div>
    <div class="overflow-x-auto bg-white rounded-xl shadow-md">
        <table class="w-full text-sm text-left text-gray-700 border-collapse">
            <thead>
                <tr class="bg-[#363636] text-white text-sm uppercase tracking-wider">
                    <th class="px-3 py-2">ID</th>
                    <th class="px-3 py-2">Name</th>
                    <th class="px-3 py-2">Delivery Fee</th>
                    <th class="px-3 py-2">Minimum Amount</th>
                     <th class="px-3 py-2">Maximum Amount</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2 text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="shippingTableBody" class="divide-y divide-gray-200">
                @if($shippings->isNotEmpty())
                @foreach ($shippings as $ship)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3">{{ $ship->name }}</td>
                        <td class="px-4 py-3">{{ number_format($ship->delivery_fee,2) }}</td>
                        <td class="px-4 py-3">{{ number_format($ship->minimum_delivery_amount,2) }}</td>
                        <td class="px-4 py-3">{{ number_format($ship->maximum_delivery_amount,2) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full
                                {{ $ship->status ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $ship->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 flex justify-center gap-4">
                            <button class="text-blue-600 hover:text-blue-800 transition editShippingBtn"
                                data-id="{{ $ship->id }}" data-name="{{ $ship->name }}" data-delivery_fee="{{ $ship->delivery_fee }}" data-minimum_delivery_amount="{{ $ship->minimum_delivery_amount }}"
                                data-maximum_delivery_amount="{{ $ship->maximum_delivery_amount }}" data-status="{{ $ship->status }}">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button class="text-red-600 hover:text-red-800 transition btnDeleteShipping"
                                data-id="{{ $ship->id }}">
                                <i class="fa-solid fa-delete-left"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
                @else
                     <td colspan="8" class="py-5 text-center">No data available</td>
                @endif
            </tbody>
        </table>
    </div>
    <div class="p-4">
        {{ $shippings->links() }}
    </div>
    @include('admin.shipping.model')
</div>
</x-layouts.app>
<script src="{{ asset('admin/js/shipping.js') }}?v={{ time() }}"></script>

