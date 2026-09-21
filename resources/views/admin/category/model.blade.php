<div id="categoryModal" x-data="{ previewUrl: null, exiting_image: '', form: { name: '', status: '1', cat_id: 0, cat_image: '', gender: '', parent_id: '' } }" class="fixed inset-0 hidden items-center justify-center z-50">
    <div class="absolute inset-0 bg-black/40" onclick="$('#categoryModal').hide()"></div>
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-[600px] max-w-[90%] max-h-[90vh] overflow-y-auto relative z-10">
        <h2 class="text-2xl font-bold mb-6 text-gray-800" id="category_label">Add Category</h2>
        <form id="categoryForm" class="space-y-6">
            <input type="hidden" name="category_id" x-model="form.cat_id" id="category_id" />
            <input type="hidden" name="exiting_image" x-model="exiting_image" id="exiting_image" />
            <div class="flex items-center gap-3">
                <div class="w-full">
                    <label class="block text-gray-700 font-medium mb-2">Category Name<span
                            class="text-red-500">*</span></label>
                    <input type="text" name="category_name" id="category_name" x-model="form.name"
                        placeholder="Enter category name"
                        class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#363636]">
                </div>
                <div class="w-full">
                    <label class="block text-gray-700 font-medium mb-2">Status<span
                            class="text-red-500">*</span></label>
                    <select name="category_status" x-model="form.status" id="category_status"
                        class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#363636]">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Category Image<span
                        class="text-red-500">*</span></label>
                <input type="file" name="category_image" id="category_image" accept=".png, .jpg, .jpeg"
                    x-ref="fileInput"
                    @change="
                               const file = $refs.fileInput.files[0];
                               if (file) {
                                   const reader = new FileReader();
                                   reader.onload = e => { previewUrl = e.target.result }
                                   reader.readAsDataURL(file);
                               }
                           "
                    class="form-input w-full border border-gray-300 rounded-lg p-2 cursor-pointer bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#363636] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#363636] file:text-white hover:file:bg-[#363636]">

                <div class="mt-4 flex justify-center overflow-hidden">
                    <img :src="previewUrl" x-show="previewUrl"
                        class="w-40 mx-auto rounded rounded-lg border border-gray-300 shadow-md object-cover" />
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-full">
                    <label class="block text-gray-700 font-medium mb-2">Parent Category<span class="text-red-500">*</span></label>
                    <select name="parent_id" x-model="form.parent_id" id="parent_id" class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#363636]">
                        <option value="">Select Parent Category</option>
                        @foreach ($categories_all as $category)
                            <option value="{{ $category->id }}">{{ $category->name ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full" x-show="!form.parent_id">
                    <label class="block text-gray-700 font-medium mb-2">Gender<span class="text-red-500">*</span></label>
                    <select name="gender" x-model="form.gender" id="gender" class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#363636]">
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="others">Others</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="$('#categoryModal').hide()" class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100">Cancel</button>
                <button type="submit" class="bg-[#363636] text-white px-5 py-2 rounded-lg hover:bg-[#363632]" id="save_cat">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<div id="deleteCategoryModal" x-data="{ open: false, deleteId: null }">
    <template x-if="open">
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="absolute inset-0 bg-black/40" @click="open = false"></div>
            <div class="bg-white p-6 rounded-xl shadow-xl w-[400px] relative z-10">
                <h2 class="text-lg font-bold mb-4 text-gray-800">Confirm Delete</h2>
                <p class="text-gray-600 mb-6">Are you sure you want to delete this category?</p>
                <div class="flex justify-end gap-3">
                    <button @click="open = false" class="px-4 py-1 border rounded-lg hover:bg-gray-100">Cancel</button>
                    <button @click="deleteCategory(deleteId)"
                        class="px-4 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
                </div>
            </div>
        </div>
    </template>
</div>
