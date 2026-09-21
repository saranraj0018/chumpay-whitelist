<div id="couponModal"
     x-data="{ open: false }"
     x-show="open"
     class="fixed inset-0 flex items-center justify-center z-50"
     style="display:none">

    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/40" @click="open=false"></div>

    <!-- Modal -->
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-[700px] max-w-[95%] relative z-10">
        <h2 class="text-2xl font-bold mb-6 text-gray-800" id="coupon_label">Add Coupon</h2>
        <form id="couponForm" class="space-y-6">
            @csrf
            <input type="hidden" name="coupon_id" id="coupon_id">
            <!-- Code + Type + Value -->
            <div class="flex items-center gap-3">
                <div class="w-full">
                    <label>Coupon Code<span class="text-red-500">*</span></label>
                    <input type="text" name="coupon_code" id="coupon_code"  class="form-input w-full border border-gray-300 rounded-lg p-2
                                  focus:outline-none focus:ring-2 focus:ring-[#363636]">
                </div>
                <div class="w-full">
                    <label>Discount Type<span class="text-red-500">*</span></label>
                    <select name="discount_type" id="discount_type"  class="form-input w-full border border-gray-300 rounded-lg p-2
                                  focus:outline-none focus:ring-2 focus:ring-[#363636]">
                        <option value="1">Percentage</option>
                        <option value="2">Fixed</option>
                    </select>
                </div>
                <div class="w-full">
                    <label>Discount Value<span class="text-red-500">*</span></label>
                    <input type="number" name="discount_value" id="discount_value"  class="form-input w-full border border-gray-300 rounded-lg p-2
                                  focus:outline-none focus:ring-2 focus:ring-[#363636]">
                </div>
            </div>
            <!-- Apply For + Prices + Orders -->
            <div class="flex items-center gap-3">
                <div class="w-full">
                    <label>Apply For<span class="text-red-500">*</span></label>
                    <select name="apply_for" id="apply_for"  class="form-input w-full border border-gray-300 rounded-lg p-2
                                  focus:outline-none focus:ring-2 focus:ring-[#363636]">
                        <option value="1">Subtotal</option>
                        <option value="2">Order</option>
                    </select>
                </div>
                <div class="w-full">
                    <label>Min Price</label>
                    <input type="number" name="min_price" id="min_price"  class="form-input w-full border border-gray-300 rounded-lg p-2
                                  focus:outline-none focus:ring-2 focus:ring-[#363636]">
                </div>
                <div class="w-full">
                    <label>Max Price</label>
                    <input type="number" name="max_price" id="max_price"  class="form-input w-full border border-gray-300 rounded-lg p-2
                                  focus:outline-none focus:ring-2 focus:ring-[#363636]">
                </div>
            </div>
            <!-- Expiry + Status -->
            <div class="flex items-center gap-3">
                <div class="w-full" >
                    <label>Expires At<span class="text-red-500">*</span></label>
                    <input type="date" name="expires_at" id="expires_at"  class="form-input w-full border border-gray-300 rounded-lg p-2
                                  focus:outline-none focus:ring-2 focus:ring-[#363636]">
                </div>
                <div class="w-full">
                    <label>Status</label>
                    <select name="status" id="status"  class="form-input w-full border border-gray-300 rounded-lg p-2
                                  focus:outline-none focus:ring-2 focus:ring-[#363636]">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="w-full hidden" id="order_count_tab">
                    <label>Order Count<span class="text-red-500">*</span></label>
                    <input type="number" name="order_count" id="order_count" class="form-input w-full border rounded-lg p-2">
                </div>
            </div>

            <div class="w-full" id="expires_at_wrapper">
                <label>Description<span class="text-red-500">*</span></label>
                <textarea type="textarea" name="description" id="description"  class="form-input w-full border border-gray-300 rounded-lg p-2
                                  focus:outline-none focus:ring-2 focus:ring-[#363636]"></textarea>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" @click="open=false" class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100">Cancel</button>
                <button type="submit" id="save_coupon"  class="bg-[#363636] text-white px-5 py-2 rounded-lg hover:bg-[#363636]">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteCouponModal" x-data="{ open: false, deleteId: null }">
    <template x-if="open">
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="absolute inset-0 bg-black/40" @click="open=false"></div>
            <div class="bg-white p-6 rounded-xl shadow-xl w-[400px] relative z-10">
                <h2 class="text-lg font-bold mb-4">Confirm Delete</h2>
                <p class="mb-6">Are you sure you want to delete this coupon?</p>
                <div class="flex justify-end gap-3">
                    <button @click="open=false" class="px-4 py-1 border rounded-lg">Cancel</button>
                    <button @click="deleteCoupon(deleteId)" class="px-4 py-1 bg-red-600 text-white rounded-lg">Delete</button>
                </div>
            </div>
        </div>
    </template>
</div>
