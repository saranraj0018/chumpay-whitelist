<x-layouts.app>
    <div class="p-4" x-data="{ open: false }">
        <div class="mt-5 overflow-x-auto bg-white rounded-xl shadow-md" id="deliveryTableWrapper">
            <table id="products" class="w-full text-sm text-left text-gray-700 border-collapse">
                <thead>
                    <tr class="bg-[#363636] text-white  text-sm uppercase tracking-wider">
                        <th class="px-3 py-2">S.No</th>
                        <th class="px-3 py-2">Image</th>
                        <th class="px-3 py-2">User</th>
                        <th class="px-3 py-2">Description</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody id="deliveryListTableBody" class="divide-y divide-gray-200">
                    @if ($ticket_list->isNotEmpty())
                        @foreach ($ticket_list as $list)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-2 py-3 font-medium text-gray-900">{{ $loop->iteration }}</td>
                                <td class="px-2 py-3">
                                    @if ($list->image)
                                        <img src="{{ asset('storage/' . $list->image) }}"
                                            class="h-10 w-10 object-cover rounded-lg" />
                                    @else
                                        <span class="text-gray-400 italic">No Image</span>
                                    @endif
                                </td>
                                <td class="px-2 py-3 font-medium text-gray-900">{{ $list->get_user->name ?? '' }}</td>
                                <td class="px-2 py-3 w-100">{{ $list->description ?? '' }}</td>
                                <td class="px-2 py-3">
                                    @php
                                        $statusClass = '';
                                        $statusText = '';
                                        if ($list->status == 'pending') {
                                            $statusClass = 'bg-gray-100 text-gray-700';
                                            $statusText = 'Pending';
                                        } elseif ($list->status == 'in_progress') {
                                            $statusClass = 'bg-blue-100 text-blue-700';
                                            $statusText = 'In Progress';
                                        } elseif ($list->status == 'on_hold') {
                                            $statusClass = 'bg-orange-100 text-orange-700';
                                            $statusText = 'On Hold';
                                        } elseif ($list->status == 'resolved') {
                                            $statusClass = 'bg-green-100 text-green-700';
                                            $statusText = 'Resolved';
                                        }else {
                                            $statusClass = 'bg-red-100 text-red-700';
                                            $statusText = 'Rejected';
                                        }
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td class="px-2 py-3 flex justify-center">
                                        <button class="text-blue-600 hover:text-blue-800 transition editstatusSave"
                                            data-id="{{ $list->id }}" data-status="{{ $list->status ?? '' }}"  data-image="{{ $list->image ? asset('storage/'.$list->image) : '' }}">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="text-center p-5">No Data Found</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <div class="p-4">
                {{ $ticket_list->links() }}
            </div>
        </div>
        @include('admin.tickets.ticket_status_change')
    </div>
</x-layouts.app>
<script src="{{ asset('admin/js/ticket.js') }}?v={{ time() }}"></script>
