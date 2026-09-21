<x-layouts.app>

    <div class="p-4">
        <div class="flex justify-between mb-4">
            <h2 class="text-xl font-bold">Products</h2>
            <button id="createProductBtn" class="bg-[#363636] text-white px-4 py-2 rounded">
                Create
            </button>
        </div>
        <div class="overflow-x-auto bg-white rounded-xl shadow-md">
            <table class="w-full text-sm text-left text-gray-700 border-collapse">
                <thead>
                    <tr class="bg-[#363636] text-white text-sm uppercase tracking-wider">
                        <th class="px-3 py-2">ID</th>
                        <th class="px-3 py-2">Name</th>
                        <th class="px-3 py-2">Image</th>
                        <th class="px-3 py-2">Category</th>
                        <th class="px-3 py-2">Product Type</th>
                        <th class="px-3 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="productTableBody" class="divide-y divide-gray-200">
                    @foreach ($product_lists as $product)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3">{{ $product->name }}</td>
                        <td class="px-4 py-3">
                            @if ($product->main_image)
                            <img src="{{ asset('storage/' . $product->main_image) }}"
                                class="h-10 w-10 object-cover rounded-lg shadow-sm border" />
                            @else
                            <span class="text-gray-400 italic">No Image</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $product->get_category->name ?? '' }}</td>
                        <td class="px-4 py-3">{{ $product->product_type ?? '' }}</td>
                        <td class="px-4 py-3 flex justify-center gap-4">
                            <button class="text-blue-600 editProductBtn"
                                data-product='@json($product)'>
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            {{-- @if ($product->get_wishlist->isEmpty() && $product->get_orderdetails->isEmpty()) --}}
                            {{-- <button  onclick="confirmDeleteProduct({{ $product->id }}, '{{ addslashes($product->name) }}')" class="text-red-600 hover:text-red-800 transition deleteBtn"
                            data-id="{{ $product->id }}"
                            data-url="{{ route('delete_product', ['id' => encrypt($product->id)]) }}">
                            <i class="fa-solid fa-delete-left"></i>
                            </button> --}}
                            {{-- @endif --}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $product_lists->links() }}
        </div>
        @include('admin.product.product_add_model', [
        'categories' => $category,
        'variantValues' => $variants,
        ])
        {{-- @include('admin.delete_confirmation_modal') --}}
    </div>
</x-layouts.app>
<script>
    let primaryVariantOptions = `
<option value="">Select</option>
@foreach ($variants as $attr)
<option value="{{ $attr->id }}">{{ $attr->name }}</option>
@endforeach
`;
    const variantValues = @json($variants);
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('admin/js/product.js') }}?v={{ time() }}"></script>
<option value="{{ $attr->id }}">{{ $attr->name }}</option>
@endforeach
`;
const variantValues = @json($variants);
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('admin/js/product.js') }}?v={{ time() }}"></script>