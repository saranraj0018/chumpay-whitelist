<div id="walletModal" x-data="{ form: { bonus_id: '', name: '', status: '1', minimum_amount: '', maximum_amount: '', bonus_amount: '', item_amount: '', valid_days: '' } }" class="fixed inset-0 hidden items-center justify-center z-50">
    <div class="absolute inset-0 bg-black/40" onclick="$('#walletModal').hide()"></div>
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-[600px] max-w-[90%] relative z-10">
        <h2 class="text-2xl font-bold mb-6 text-gray-800" id="wallet_label">Add Wallet Bonus</h2>
        <form id="walletForm" class="space-y-6">
            <input type="hidden" name="wallet_bonus_id" x-model="form.bonus_id" id="wallet_bonus_id" />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="w-full">
                    <label class="block text-gray-700 font-medium">Name</label>
                    <input type="text" name="name" id="name" x-model="form.name"
                        placeholder="Enter wallet bonus name"
                        class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#363636]">
                </div>
                <div class="w-full">
                    <label>Minimum Amount</label>
                    <input type="number" name="minimum_amount" id="minimum_amount" x-model="form.minimum_amount"
                        class="form-input w-full border border-gray-300 rounded-lg p-2
                                  focus:outline-none focus:ring-2 focus:ring-[#363636]">
                </div>
                <div class="w-full">
                    <label>Maximum Amount<span class="text-red-500">*</span></label>
                    <input type="number" name="maximum_amount" id="maximum_amount" x-model="form.maximum_amount"
                        class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#363636]">
                </div>
                <div class="w-full">
                    <label>Bonus Amount<span class="text-red-500">*</span></label>
                    <input type="number" name="bonus_amount" id="bonus_amount" x-model="form.bonus_amount"
                        class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#363636]">
                </div>
                <div class="w-full">
                    <label>Item Amount<span class="text-red-500">*</span></label>
                    <input type="number" name="item_amount" id="item_amount" x-model="form.item_amount"
                        class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#363636]">
                </div>
                <div class="w-full">
                    <label>Valid Days<span class="text-red-500">*</span></label>
                    <input type="number" name="valid_days" id="valid_days" x-model="form.valid_days"
                        class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#363636]">
                </div>
                <div class="w-full">
                    <label class="block text-gray-700 font-medium mb-2">Status<span
                            class="text-red-500">*</span></label>
                    <select name="status" x-model="form.status" id="status"
                        class="form-input w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#363636]">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="$('#walletModal').hide()"
                    class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-100">Cancel</button>
                <button type="submit" class="bg-[#363636] text-white px-5 py-2 rounded-lg hover:bg-[#363632]"
                    id="save_wallet_form">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<div id="deleteWalletModal" x-data="{ open: false, deleteId: null }">
    <template x-if="open">
        <div class="fixed inset-0 flex items-center justify-center z-50">
            <div class="absolute inset-0 bg-black/40" @click="open = false"></div>
            <div class="bg-white p-6 rounded-xl shadow-xl w-[400px] relative z-10">
                <h2 class="text-lg font-bold mb-4 text-gray-800">Confirm Delete</h2>
                <p class="text-gray-600 mb-6">Are you sure you want to delete this Wallet Bonus?</p>
                <div class="flex justify-end gap-3">
                    <button @click="open = false" class="px-4 py-1 border rounded-lg hover:bg-gray-100">Cancel</button>
                    <button @click="deleteWalletBonus(deleteId)"
                        class="px-4 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
                </div>
            </div>
        </div>
    </template>
</div>
