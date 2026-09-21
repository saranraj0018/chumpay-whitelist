
<x-layouts.app>
    <div class="p-4">
        <div class="flex justify-between mb-4">
            <h2 class="text-xl font-bold">Attribute Values</h2>
            <button id="createAttributeBtn" class="bg-[#363636] text-white px-4 py-2 rounded">
                Create
            </button>
        </div>
        <div class="overflow-x-auto bg-white rounded-xl shadow-md">
            <table class="w-full text-sm text-left text-gray-700 border-collapse">
                <thead>
                <tr class="bg-[#363636] text-white text-sm uppercase tracking-wider">
                    <th class="px-3 py-2">ID</th>
                    <th class="px-3 py-2">Name</th>
                    <th class="px-3 py-2">Attribute Values</th>
                    <th class="px-3 py-2 text-center">Actions</th>
                </tr>
                </thead>
                <tbody id="attributeTableBody" class="divide-y divide-gray-200">
                @foreach($attributes as $attr)
                @php
                    $att_name = $attr->get_variant_value->pluck('value');
                @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3">{{ $attr->name }}</td>
                        <td class="px-4 py-3">{{ $att_name->implode(',') }}</td>
                        <td class="px-4 py-3 flex justify-center gap-4">
                            <button
                                class="text-blue-600 hover:text-blue-800 transition editAttributeBtn"
                                data-id="{{ $attr->id }}"
                                data-attribute="{{ $attr->name  }}"
                                data-attribute_value="{{ $att_name->implode(',') }}">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $attributes->links() }}
        </div>
        @include('admin.attribute.model',['attribute' => $attribute_name])
    </div>
</x-layouts.app>
<script src="{{ asset('admin/js/attribute.js') }}?v={{ time() }}"></script>

