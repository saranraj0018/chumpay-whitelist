@props(['mimes' => ''])

<div class="w-full mt-3"
     x-data="{
        fileName: '',
        file: null,
        preview: null,

        previewFile(file) {
            if (!file) return;
            if (!(file instanceof File)) return;

            const reader = new FileReader();
            reader.onload = e => this.preview = e.target.result;
            reader.readAsDataURL(file);
        },

        removeFile() {
            this.file = null;
            this.fileName = '';
            this.preview = null;
            this.$refs.fileInput.value = '';
        }
     }"
>
    <!-- Upload Card -->
    <div class="border border-gray-300 rounded-xl bg-white p-6 flex flex-col justify-center items-center space-y-4 transition-shadow hover:shadow-lg w-full"
         :class="{'border-blue-500 ring ring-blue-200': preview}" x-init="() => $this.dispatch('file-changed', null)"
    >
        <!-- Hidden Input -->
        <input
            type="file"
            accept="{{ $mimes }}"
            x-ref="fileInput"

            @change="file = $refs.fileInput.files[0]; fileName = file ? file.name : ''; previewFile(file); $dispatch('file-changed', file)"
            class="hidden"
        />

        <!-- Upload Area -->
        <template x-if="!preview">
            <button type="button"
                class="flex flex-col justify-center items-center space-y-2 text-gray-500 hover:text-gray-700 w-full py-8 border-2 border-dashed rounded-lg hover:border-blue-400 transition-all duration-150"
                @click="$refs.fileInput.click()"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v-2a4 4 0 014-4h4a4 4 0 014 4v2M12 12v6m0 0l-2-2m2 2l2-2" />
                </svg>
                <span class="text-sm font-medium" x-text="fileName || 'Click or drag file to upload'"></span>
                <span class="text-xs text-gray-400">PNG, JPG, GIF up to 10MB</span>
            </button>
        </template>

        <!-- Preview Area -->
        <template x-if="preview">
            <div class="flex flex-col justify-center items-center space-y-3 w-full">
                <div class="relative w-full">
                    <img :src="preview" alt="Preview" class="w-full max-h-72 rounded-lg border border-gray-200 object-contain" />

                    <!-- Remove Icon -->
                    <button @click="removeFile()" class="absolute top-3 right-3 bg-white p-2 rounded-full shadow hover:bg-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Change Icon -->
                    <button @click="$refs.fileInput.click()" class="absolute bottom-3 right-3 bg-white p-2 rounded-full shadow hover:bg-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M4 20h4l10-10-4-4-10 10v4z" />
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-gray-600" x-text="fileName"></p>
            </div>
        </template>
    </div>
</div>
