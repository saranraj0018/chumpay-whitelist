<div class="bg-white rounded-2xl shadow-md p-5 w-full max-w-[1200px] min-h-[90vh]" x-cloak x-data="{
    steps: ['Product Info', 'Product Details', 'Review'],
    stepNumber: 0,
    modalTitle: 'Create Product',
    form: {
        id: null,
        name: '',
        image: '',
        description: '',
        benefits: '',
        category_id: '',
        sale_price: '',
        regular_price: '',
        purchase_price: '',
        weight: '',
        weight_unit: 'kg',
        tax_type: '',
        tax_percentage: '',
        is_featured_product: false
    },

    nextStep() {
        if (this.stepNumber < this.steps.length - 1) {
            this.stepNumber++;
        }
    },

    prevStep() {
        if (this.stepNumber > 0) {
            this.stepNumber--;
        }
    },

    submitForm() {
        // Add create/update API logic here
        this.closeModal();
    },

    closeModal() {
        this.showModal = false;
    }

}">
    <div class="flex items-center justify-between mb-4 w-full">
        <h2 class="text-xl font-semibold mb-4" x-text="modalTitle"></h2>
        {{-- <button @click.prevent="closeModal()"><i class="fa fa-close"></i></button> --}}
    </div>
    <x-domains.products.step-indigator />

    <form @submit.prevent="submitForm" class="flex flex-col justify-start items-start w-full  h-[75vh] overflow-y-scroll">
        <div class="p-5 space-y-5 flex-1 w-full h-fit">

            {{-- Step 1: Product Information --}}
            <div x-show="stepNumber === 0" class="space-y-4">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-label>Product Name</x-label>
                        <x-input type="text" x-model="form.name" placeholder="eg .., Flower" />
                    </div>

                    <div>
                        <x-label>Category ID</x-label>

                        <x-select x-model="form.category_id">
                            <option value="Table">Table</option>
                            <option value="Chair">Chair</option>
                            <option value="Dinning">Dinning</option>
                        </x-select>
                    </div>


                    <div>
                        <x-label>Description</x-label>
                        <x-textarea placeholder="Enter Description" x-model="form.description"></x-textarea>
                    </div>

                    <div>
                        <x-label>Benefits</x-label>
                        <x-textarea placeholder="Enter Benifits" x-model="form.benefits" />
                    </div>


                    <div class="col-span-2">
                        <x-label>Image URL</x-label>
                        <span x-text="JSON.stringify(form.image)"></span>
                        <x-file @file-changed="console.log('changed')" mimes="image/png,image/jpeg"   />
                    </div>
                </div>
            </div>

            {{-- Step 2: Product Details --}}
            <div x-show="stepNumber === 1" class="space-y-4">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 space-y-5">
                    <div>
                        <x-label>Sale Price</x-label>
                        <x-input placeholder="eg .., 500.00" type="number" step="0.01" x-model="form.sale_price" />
                    </div>

                    <div>
                        <x-label>Regular Price</x-label>
                        <x-input placeholder="eg .., 550.00" type="number" step="0.01"
                            x-model="form.regular_price" />
                    </div>

                    <div>
                        <x-label>Purchase Price</x-label>
                        <x-input placeholder="eg .., 700.00" type="number" step="0.01"
                            x-model="form.purchase_price" />
                    </div>

                    <div class="col-span-2">
                        <x-label>Weight</x-label>
                        <x-input placeholder="eg .., 05" type="number" step="0.01" x-model="form.weight" />
                    </div>

                    <div>
                        <x-label>Weight Unit</x-label>
                        <x-select x-model="form.weight_unit">
                            <option value="kg">kg</option>
                            <option value="g">g</option>
                            <option value="ml">ml</option>
                            <option value="l">l</option>
                        </x-select>
                    </div>



                    <div>
                        <x-label>Tax Type</x-label>
                        <x-select x-model="form.tax_type">
                            <option value="">Select Tax Type</option>
                            <option value="0">Zero</option>
                            <option value="1">Inclusive</option>
                            <option value="2">Exclusive</option>
                        </x-select>
                    </div>

                    <div class="col-span-2">
                        <x-label>Tax Percentage</x-label>
                        <x-input placeholder="eg .., 18" type="number" step="0.01" x-model="form.tax_percentage" />
                    </div>

                    <div class="flex items-center space-x-2 col-span-2">
                        <input type="checkbox" x-model="form.is_featured_product" class="h-4 w-4" />
                        <x-label class="block text-sm font-medium">Is Featured Product</x-label>
                    </div>
                </div>
            </div>

            {{-- Step 3: Review & Save --}}
            <div x-show="stepNumber === 2" class="space-y-4">

                <div class="bg-gray-50 rounded-lg p-4 shadow-sm">
                    <h4 class="text-md font-medium mb-2">Product Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="font-semibold">Name:</span>
                            <p class="text-gray-700" x-text="form.name || '-'"></p>
                        </div>
                        <div>
                            <span class="font-semibold">Category:</span>
                            <p class="text-gray-700" x-text="form.category_id || '-'"></p>
                        </div>
                        <div class="col-span-2">
                            <span class="font-semibold">Description:</span>
                            <p class="text-gray-700" x-text="form.description || '-'"></p>
                        </div>
                        <div class="col-span-2">
                            <span class="font-semibold">Benefits:</span>
                            <p class="text-gray-700" x-text="form.benefits || '-'"></p>
                        </div>
                        <div class="col-span-2">
                            <span class="font-semibold">Image Preview:</span>
                            <div class="mt-2">
                                <img :src="form.image ? URL.createObjectURL(form.image) : ''" alt="Product Image"
                                    class="max-h-40 rounded-lg border border-gray-200 object-contain"
                                    x-show="form.image">
                                <p class="text-gray-500" x-show="!form.image">No image uploaded</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-4 shadow-sm">
                    <h4 class="text-md font-medium mb-2">Pricing & Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="font-semibold">Sale Price:</span>
                            <p class="text-gray-700" x-text="form.sale_price ? '$'+form.sale_price : '-'"></p>
                        </div>
                        <div>
                            <span class="font-semibold">Regular Price:</span>
                            <p class="text-gray-700" x-text="form.regular_price ? '$'+form.regular_price : '-'"></p>
                        </div>
                        <div>
                            <span class="font-semibold">Purchase Price:</span>
                            <p class="text-gray-700" x-text="form.purchase_price ? '$'+form.purchase_price : '-'"></p>
                        </div>
                        <div>
                            <span class="font-semibold">Weight:</span>
                            <p class="text-gray-700" x-text="form.weight ? form.weight+' '+form.weight_unit : '-'">
                            </p>
                        </div>
                        <div>
                            <span class="font-semibold">Tax Type:</span>
                            <p class="text-gray-700" x-text="form.tax_type || '-'"></p>
                        </div>
                        <div>
                            <span class="font-semibold">Tax Percentage:</span>
                            <p class="text-gray-700" x-text="form.tax_percentage ? form.tax_percentage+'%' : '-'"></p>
                        </div>
                        <div class="col-span-3 flex items-center space-x-2">
                            <input type="checkbox" disabled class="h-4 w-4" :checked="form.is_featured_product" />
                            <span class="font-semibold">Is Featured Product</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Buttons --}}
        <div class="flex items-center gap-5 justify-between mt-6 w-full">
            <x-button varient="ghost" type="button" @click="prevStep()" x-show="stepNumber > 0">Back</x-button>

            <div class="flex justify-between items-center w-full">
                <x-button varient="primary" type="button" @click="nextStep()"
                    x-show="stepNumber < steps.length - 1">Next</x-button>

                <x-button varient="primary" type="submit" x-show="stepNumber === steps.length - 1">Save</x-button>
            </div>
        </div>
    </form>

</div>
