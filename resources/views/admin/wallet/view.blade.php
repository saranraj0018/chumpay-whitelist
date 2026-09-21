<x-layouts.app>
    <div class="p-4">
        <div class="flex justify-between mb-4">
            <h2 class="text-xl font-bold">Wallet Bonus</h2>
            <button id="createWalletBtn" class="bg-[#363636] text-white px-4 py-2 rounded"> Create </button>
        </div>
        <div class="overflow-x-auto bg-white rounded-xl shadow-md">
            <table class="w-full text-sm text-left text-gray-700 border-collapse">
                <thead>
                    <tr class="bg-[#363636] text-white text-sm uppercase tracking-wider">
                        <th class="px-2 py-2">ID</th>
                        <th class="px-2 py-2">Name</th>
                        <th class="px-2 py-2">Minimum Amount</th>
                        <th class="px-2 py-2">Maximum Amount</th>
                        <th class="px-2 py-2">Bonus Amount</th>
                        <th class="px-2 py-2">Item Amount</th>
                        <th class="px-2 py-2">Valid Days</th>
                        <th class="px-2 py-2">Status</th>
                        <th class="px-2 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="walletTableBody" class="divide-y divide-gray-200">
                    @foreach ($wallet_offer as $wallet)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-2 py-3 font-medium text-gray-900">{{ $loop->iteration }}</td>
                            <td class="px-2 py-3">{{ $wallet->name ?? '' }}</td>
                            <td class="px-2 py-3">{{ $wallet->min_amount ?? '' }}</td>
                            <td class="px-2 py-3">{{ $wallet->max_amount ?? '' }}</td>
                            <td class="px-2 py-3">{{ $wallet->bonus_amount ?? '' }}</td>
                            <td class="px-2 py-3">{{ $wallet->item_amount ?? '' }}</td>
                            <td class="px-2 py-3">{{ $wallet->valid_days ?? '' }}</td>
                            <td class="px-2 py-3">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $wallet->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $wallet->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-2 py-3 flex justify-center gap-4">
                                <button class="text-blue-600 hover:text-blue-800 transition editWalletBtn"
                                    data-id="{{ $wallet->id }}" data-name="{{ $wallet->name }}"
                                    data-status="{{ $wallet->is_active }}"
                                    data-minimum_amount="{{ $wallet->min_amount }}"
                                    data-maximum_amount="{{ $wallet->max_amount }}"
                                    data-bonus_amount="{{ $wallet->bonus_amount }}"
                                    data-item_amount="{{ $wallet->item_amount }}"
                                    data-valid_days="{{ $wallet->valid_days }}">
                                   <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                {{-- <button class="text-red-600 hover:text-red-800 transition btnDeleteWallet" data-id="{{ $wallet->id }}">
                                <i class="fa-solid fa-delete-left"></i>
                            </button> --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $wallet_offer->links() }}
        </div>
        @include('admin.wallet.modal')
    </div>
</x-layouts.app>
<script src="{{ asset('admin/js/walletbonus.js') }}?v={{ time() }}"></script>
