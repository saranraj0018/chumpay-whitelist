@auth
<dialog id="reviewModal" class="rounded-2xl p-0 w-[95%] sm:w-[500px] md:w-[520px] backdrop:bg-black/40">

    <div class="p-4 sm:p-6 relative">

        <!-- Close Button -->
        <button type="button" onclick="document.getElementById('reviewModal').close()"
            class="absolute right-4 top-4 text-gray-500 text-lg">
            ✕
        </button>

        <h2 class="text-lg sm:text-xl font-semibold mb-5">Write a Review</h2>

        <form action="{{ route('review.store', $product->id) }}" method="POST"
            enctype="multipart/form-data" class="space-y-5" id="reviewForm">
            @csrf

            <!-- Rating -->
            <div>
                <label class="block text-sm font-medium mb-2">
                    Your Rating <span class="text-red-500">*</span>
                </label>

                <div id="starRating" class="flex gap-2 text-xl sm:text-2xl text-slate-300">
                    @for ($i = 1; $i <= 5; $i++)
                        <i class="fa-regular fa-star cursor-pointer hover:text-amber-400 transition-colors"
                        data-value="{{ $i }}"></i>
                        @endfor
                </div>
                <input type="hidden" name="rating" id="ratingInput" value="">
                @error('rating')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Review -->
            <div>
                <label class="block text-sm font-medium mb-2">
                    Your Review <span class="text-red-500">*</span>
                </label>

                <textarea name="review" id="reviewText"
                    placeholder="Tell us about your experience with this product..." maxlength="500"
                    class="w-full border border-slate-300 rounded-xl p-3 text-sm h-24 resize-none focus:outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition">{{ old('review') }}</textarea>

                <p class="text-xs text-slate-400 mt-1">
                    <span id="charCount">0</span> / 500 characters
                </p>
                @error('review')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <!-- Upload Photo -->
            <div>
                <label class="block text-sm font-medium mb-2">Add Photo (Optional)</label>

                <label class="border-2 border-dashed border-slate-300 rounded-xl p-6 sm:p-8 flex flex-col items-center justify-center text-slate-400 cursor-pointer hover:border-amber-400 hover:bg-amber-50/20 transition">
                    <input type="file" name="image[]" id="reviewImage" accept="image/jpeg,image/png,image/webp" class="hidden" multiple>
                    <i class="fa-solid fa-arrow-up-from-bracket text-xl sm:text-2xl mb-2 text-slate-400"></i>
                    <p class="text-sm text-center text-slate-600 font-medium" id="uploadText">Click to upload or drag and drop</p>
                    <span class="text-xs text-slate-400 mt-1 text-center">JPG, PNG, or WEBP, max 2MB each</span>
                </label>
                @error('image')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="button" onclick="document.getElementById('reviewModal').close()"
                    class="w-full sm:w-1/2 border border-slate-300 rounded-full py-3 font-semibold text-slate-700 hover:bg-slate-100 transition">
                    Cancel
                </button>
                <button type="submit"
                    class="w-full sm:w-1/2 bg-[#0f172a] text-white rounded-full py-3 font-semibold hover:bg-amber-500 hover:text-black transition-all duration-200 shadow-md">
                    Submit Review
                </button>
            </div>
        </form>
    </div>
</dialog>
@endauth

<script>
    (function() {
        const stars = document.querySelectorAll('#starRating i');
        const ratingInput = document.getElementById('ratingInput');
        const reviewText = document.getElementById('reviewText');
        const charCount = document.getElementById('charCount');
        const fileInput = document.getElementById('reviewImage');
        const uploadText = document.getElementById('uploadText');

        // Star rating
        let current = {{ (int) old('rating', 0) }};

        function paint(val) {
            stars.forEach(s => {
                const v = parseInt(s.dataset.value);
                s.classList.toggle('fa-solid', v <= val);
                s.classList.toggle('fa-regular', v > val);
                s.classList.toggle('text-amber-400', v <= val);
            });
        }

        stars.forEach(star => {
            star.addEventListener('mouseenter', () => paint(parseInt(star.dataset.value)));
            star.addEventListener('mouseleave', () => paint(current));
            star.addEventListener('click', () => {
                current = parseInt(star.dataset.value);
                ratingInput.value = current;
                paint(current);
            });
        });
        paint(current);

        // Character counter
        if (reviewText) {
            const sync = () => charCount.textContent = reviewText.value.length;
            reviewText.addEventListener('input', sync);
            sync();
        }

        // File name feedback
        if (fileInput) {
            fileInput.addEventListener('change', () => {
                uploadText.textContent = fileInput.files.length ?
                    `${fileInput.files.length} image${fileInput.files.length > 1 ? 's' : ''} selected` :
                    'Click to upload or drag and drop';
            });
        }
    })();
</script>