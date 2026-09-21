<div id="attributeModal" x-data="{  form: { attribute: '', attribute_value: '' } }" class="fixed inset-0 hidden items-center justify-center z-50">
    <div class="absolute inset-0 bg-black/40" onclick="$('#attributeModal').hide()"></div>
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-[600px] max-w-[90%] relative z-10">
        <h2 class="text-2xl font-bold mb-6 text-gray-800" id="attribute_label">Add Variant Values</h2>
        <form id="attributeForm" class="space-y-6">
            <input type="hidden" name="attribute_id" x-model="form.cat_id" id="attribute_id" />
            <div class="flex items-center gap-3">
                <div class="w-full">
                    <label class="block text-gray-700 font-medium mb-2">Attribute<span class="text-red-500">*</span></label>
                    <select name="attribute" x-model="form.attribute" id="attribute" class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#363636]">
                        <option value="">Select Attribute</option>
                        @foreach ($attribute as $attri)
                            <option value="{{ $attri->id }}">{{ $attri->name ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full">
                    <label class="block text-gray-700 font-medium mb-2">Attribute Value<span
                            class="text-red-500">*</span></label>
                    <input type="text" name="attribute_value" id="attribute_value" x-model="form.attribute_value"
                        placeholder="red,blue or x,xxl,m"
                        class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#363636]">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="$('#attributeModal').hide()" class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100">Cancel</button>
                <button type="submit" class="bg-[#363636] text-white px-5 py-2 rounded-lg hover:bg-[#363632]" id="save_attributevalue"> Save </button>
            </div>
        </form>
    </div>
</div>


