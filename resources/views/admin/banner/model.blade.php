<div id="bannerModal" x-data="{ previewUrl: '', existing_image: '', form: { banner_id: 0 } }" class="fixed inset-0 hidden items-center justify-center z-50">
    <div class="absolute inset-0 bg-black/40" @click="$('#bannerModal').hide()"></div>
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-[600px] max-w-[90%] relative z-10">
        <h2 class="text-2xl font-bold mb-6" id="banner_label">Add Banner</h2>
        <form id="bannerForm" class="space-y-6">
            <input type="hidden" name="banner_id" x-model="form.banner_id" />
            <input type="hidden" name="existing_image" x-model="existing_image" />

            <div>
                <label class="block text-gray-700 font-medium mb-2">Banner Image</label>
                <input type="file" name="banner_image" id="banner_image" accept=".png, .jpg, .jpeg"
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
                    <img :src="previewUrl" x-show="previewUrl" class="w-full max-h-[30vh] rounded-lg border object-cover" />
                </div>
                <p class="mt-2 text-sm text-red-600 font-medium">
                  Note: Banner image size should be less than 2 MB
                 </p>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="$('#bannerModal').hide()" class="px-5 py-2 border rounded-lg">Cancel</button>
                <button type="submit" id="save_banner" class="bg-[#363636] text-white px-5 py-2 rounded-lg">Save</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteBannerModal" x-data="{ open: false, deleteId: null }">
    <template x-if="open">
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="absolute inset-0 bg-black/40" @click="open=false"></div>
            <div class="bg-white p-6 rounded-xl shadow-xl w-[400px] relative z-10">
                <h2 class="text-lg font-bold mb-4">Confirm Delete</h2>
                <p class="mb-6">Are you sure you want to delete this banner?</p>
                <div class="flex justify-end gap-3">
                    <button @click="open=false" class="px-4 py-1 border rounded-lg">Cancel</button>
                    <button @click="deleteBanner(deleteId)" class="px-4 py-1 bg-red-600 text-white rounded-lg">Delete</button>
                </div>
            </div>
        </div>
    </template>
</div>
