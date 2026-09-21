<div id="orderModal" class="fixed inset-0 flex items-center justify-center z-50 overflow-auto bg-black/40 p-4"
    style="display:none;">

    <!-- Modal -->
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-[900px] mx-4 sm:mx-6 lg:mx-0 overflow-hidden flex flex-col max-h-[90vh]">
        <input type="hidden" id="idSet">
        <input type="hidden" name="exiting_image" x-model="exiting_image" id="exiting_image" />
        <!-- Header -->
        <div class="flex justify-between items-center p-5 border-b">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-800" id="orderModalTitle">Order #</h2>
            {{-- <button id="closeModalBtn" class="px-3 py-1 bg-gray-200 rounded-lg hover:bg-gray-300">Close</button> --}}
        </div>

        <!-- Content -->
        <div class="p-5 overflow-y-auto flex-1 space-y-6">
            <!-- Customer Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-gray-700 mb-2">Customer Details</h3>
                    <p><strong>Name:</strong> <span id="orderCustomerName">—</span></p>
                    <p><strong>Email:</strong> <span id="orderCustomerEmail">—</span></p>
                    <p><strong>Mobile:</strong> <span id="orderCustomerMobile">—</span></p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-gray-700 mb-2">Billing Address</h3>
                    <p><strong>Name:</strong> <span id="addressCustomerName">—</span></p>
                    <p><strong>Mobile Number:</strong> <span id="addressCustomerMobile">—</span></p>
                    <p><strong>Address:</strong> <span id="addressCustomerAddress">—</span></p>
                    <p><strong>Address Type:</strong> <span id="addressCustomerAddressType">—</span></p>
                    <p><strong>State:</strong> <span id="addressCustomerState">—</span></p>
                    <p><strong>City:</strong> <span id="addressCustomerCity">—</span></p>
                    <p><strong>Pincode:</strong> <span id="addressCustomerPincode">—</span></p>
                </div>
            </div>

            <!-- Products Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-700 border border-gray-200 rounded-lg">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2">#</th>
                            <th class="px-3 py-2">Product</th>
                            <th class="px-3 py-2">Qty</th>
                            <th class="px-3 py-2">Price</th>
                            <th class="px-3 py-2">Total</th>
                        </tr>
                    </thead>
                    <tbody id="orderProductsBody" class="divide-y divide-gray-200"></tbody>
                </table>
            </div>

            <!-- Amount Breakdown -->
            <div class="bg-gray-50 p-4 rounded-lg max-w-md">
                <h3 class="font-semibold text-gray-700 mb-2">Amount Breakdown</h3>
                <div class="flex justify-between mb-1"><span>Subtotal:</span> <span id="orderSubtotal">₹0</span></div>
                <div class="flex justify-between mb-1"><span>GST:</span> <span id="orderGST">₹0</span></div>
                <div class="flex justify-between mb-1"><span>Shipping:</span> <span id="orderShipping">₹0</span></div>
                <div class="flex justify-between mb-1"><span>Coupon Discount:</span> <span id="orderCoupon">-₹0</span>
                </div>
                <div class="flex justify-between font-bold text-lg border-t border-gray-300 pt-2 mt-2">
                    <span>Grand Total:</span> <span id="orderGrandTotal">₹0</span>
                </div>
            </div>

            <!-- Status Update -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Order Status<span
                            class="text-red-500">*</span></label>
                    <select id="status" class="w-full border p-2 sm:p-3 rounded-lg text-gray-900">
                        <option value="1">Pending</option>
                        <option value="2">In Progress</option>
                        <option value="3">Shipped</option>
                        <option value="4">Delivered</option>
                        <option value="5">Cancelled</option>
                        <option value="6">Refunded</option>
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Date<span class="text-red-500">*</span></label>
                    <input type="date" id="statusDate" class="w-full border p-2 sm:p-3 rounded-lg text-gray-900" />
                </div>
            </div>

            <!-- Refund Fields (shown only when status = Refunded) -->
            <div id="refundFields" class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4" style="display:none;">
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Refund Reference / Note <span
                            class="text-red-500">*</span></label>
                    <textarea id="refundNote" placeholder="Enter refund reference or note" rows="4"
                        class="w-full border p-2 sm:p-3 rounded-lg text-gray-900 resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Refund Proof (Image) <span
                            class="text-red-500">*</span></label>
                    <input type="file" name="refund_image" id="refundImage" accept=".png, .jpg, .jpeg"
                        class="form-input w-full border border-gray-300 rounded-lg p-2 cursor-pointer bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#ab5f00] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#ab5f00] file:text-white hover:file:bg-[#ab5f00]">
                    <span class="text-red-600 text-sm">Only JPG,JPEG,PNG files are allowed and File size must not exceed
                        2MB</span>
                    <div class="mt-4 flex justify-center overflow-hidden">
                        <img id="refundImagePreview" src=""
                            class="w-full max-h-[30vh] rounded-lg border border-gray-300 shadow-md object-cover hidden" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Buttons -->
        <div class="flex justify-end gap-3 p-5 border-t">
            <button id="cancelModalBtn" class="px-5 py-2 border rounded-lg hover:bg-gray-100">Cancel</button>
            <button id="saveStatusBtn" class="bg-[#363636] text-white px-5 py-2 rounded-lg hover:bg-[#363636]">Save</button>
        </div>
    </div>
</div>
