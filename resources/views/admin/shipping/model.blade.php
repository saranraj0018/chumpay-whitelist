<div id="shippingModal" x-data="{ form: { shipping_id: 0, name: '', delivery_fee: 0, free_delivery: false, set_amount: 0, message: '', free_delivery_amount: 0, platform_fee: 0, minimum_delivery_amount: 0, status: 1 } }" class="fixed inset-0 hidden items-center justify-center z-50">
    <div class="absolute inset-0 bg-black/40" @click="document.getElementById('shippingModal').style.display='none'">
    </div>
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-[550px] max-w-[95%] relative z-10">
        <h2 class="text-2xl font-bold mb-6 text-gray-800" id="shipping_label">Add Shipping Rule</h2>
        <form id="shippingForm" class="grid grid-cols-2 gap-4">
            <input type="hidden" x-model="form.shipping_id" name="shipping_id">
            <span id="delivery_error" class="text-red-500 text-sm"></span>
            <!-- Name -->
            <div class="col-span-2">
                <label class="block font-medium mb-1">Name</label>
                <input type="text" name="name" x-model="form.name" id="name"
                    class="form-input border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#363636] w-full">
            </div>
            <!-- Minimum Delivery Amount -->
            <div>
                <label class="block font-medium mb-1">Minimum Delivery Amount</label>
                <input type="number" name="minimum_delivery_amount" id="minimum_delivery_amount" value="0"
                    x-model="form.minimum_delivery_amount" step="0.01"
                    class="border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#363636] w-full">
            </div>
            <!-- Maximum Delivery Amount -->
            <div>
                <label class="block font-medium mb-1">Maximum Delivery Amount</label>
                <input type="number" name="maximum_delivery_amount" id="maximum_delivery_amount"
                    x-model="form.maximum_delivery_amount" step="0.01"
                    class="border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#363636] w-full">
            </div>
            <!-- Delivery Fee -->
            <div>
                <label class="block font-medium mb-1">Delivery Fee</label>
                <input type="text" inputmode="decimal" x-model="form.delivery_fee" name="delivery_fee"
                    class="border border-gray-300 rounded-lg p-2 w-full focus:outline-none focus:ring-2 focus:ring-[#363636]">
            </div>
            <!-- Status -->
            <div>
                <label class="block font-medium mb-1">Status</label>
                <select name="status" x-model="form.status"
                    class="border border-gray-300 rounded-lg p-2 w-full focus:outline-none focus:ring-2 focus:ring-[#363636]">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <!-- Buttons -->
            <div class="col-span-2 flex justify-end gap-3 pt-4">
                <button type="button" @click="document.getElementById('shippingModal').style.display='none'"
                    class="border border-gray-300 px-4 py-2 rounded-lg">
                    Cancel
                </button>
                <button type="submit" id="save_shipping" class="bg-[#363636] text-white px-5 py-2 rounded-lg">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<div id="deleteShippingModal" x-data="{ open: false, deleteId: null }">
    <template x-if="open">
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="absolute inset-0 bg-black/40" @click="open=false"></div>
            <div class="bg-white p-6 rounded-xl shadow-xl w-[400px] relative z-10">
                <h2 class="text-lg font-bold mb-4">Confirm Delete</h2>
                <p class="mb-6">Are you sure you want to delete this shipping rule?</p>
                <div class="flex justify-end gap-3">
                    <button @click="open=false" class="px-4 py-1 border rounded-lg">Cancel</button>
                    <button @click="deleteShipping(deleteId)"
                        class="px-4 py-1 bg-red-600 text-white rounded-lg">Delete</button>
                </div>
            </div>
        </div>
    </template>

    <!-- TAX MODAL -->
    <div id="taxModal" x-data="{ tax: { id: 0, percent: 0 } }" class="fixed inset-0 hidden items-center justify-center z-50">

        <div class="absolute inset-0 bg-black/40" @click="document.getElementById('taxModal').style.display='none'">
        </div>

        <div class="bg-white p-6 rounded-xl shadow-xl w-[350px] relative z-10">
            <h2 class="text-xl font-bold mb-6">Tax Percentage</h2>

            <form id="taxForm" class="grid gap-4">

                <input type="hidden" name="tax_id" x-model="tax.id">

                <div>
                    <label class="block mb-1 font-medium">Tax (%)</label>
                    <input type="number" step="0.01" name="percent" x-model="tax.percent" id="percent"
                        class="border rounded-lg w-full p-2">
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" class="px-4 py-2 border rounded-lg"
                        @click="document.getElementById('taxModal').style.display='none'">
                        Cancel
                    </button>

                    <button type="submit" class="px-4 py-2 bg-[#363636] text-white rounded-lg">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
{{--    Shipping --}}
<div id="d_shippingModal" x-data="{ shipping_amount: { id: 0, default_shipping: 0 } }" class="fixed inset-0 hidden items-center justify-center z-50">

    <div class="absolute inset-0 bg-black/40" @click="document.getElementById('d_shippingModal').style.display='none'">
    </div>

    <div class="bg-white p-6 rounded-xl shadow-xl w-[350px] relative z-10">
        <h2 class="text-xl font-bold mb-6">Default Shipping</h2>

        <form id="defaultShippingForm" class="grid gap-4">

            <input type="hidden" name="d_shipping_id" x-model="shipping_amount.id">

            <div>
                <label class="block mb-1 font-medium">Amount ($)</label>
                <input type="number" step="0.01" name="default_shipping" id="shipping_amount"
                    x-model="shipping_amount.default_shipping" class="border rounded-lg w-full p-2">
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" class="px-4 py-2 border rounded-lg"
                    @click="document.getElementById('d_shippingModal').style.display='none'">
                    Cancel
                </button>

                <button type="submit" class="px-4 py-2 bg-[#363636] text-white rounded-lg">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<div id="d_platformfeeModal" x-data="{ platform_fee : { id: 0, platform_fee: 0 } }" class="fixed inset-0 hidden items-center justify-center z-50">
    <div class="absolute inset-0 bg-black/40" @click="document.getElementById('d_platformfeeModal').style.display='none'">
    </div>
    <div class="bg-white p-6 rounded-xl shadow-xl w-[350px] relative z-10">
        <h2 class="text-xl font-bold mb-6">Platform Fee</h2>
        <form id="platformfeeForm" class="grid gap-4">
            <input type="hidden" name="d_platform_id" x-model="platform_fee.id">
            <div>
                <label class="block mb-1 font-medium">Amount ($)</label>
                <input type="number" step="0.01" name="platform_fee" id="platform_fee"
                    x-model="platform_fee.platform_fee" class="border rounded-lg w-full p-2">
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" class="px-4 py-2 border rounded-lg"
                    @click="document.getElementById('d_platformfeeModal').style.display='none'">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-[#363636] text-white rounded-lg">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>
