<section class="bg-white py-12">
    <div class="max-w-[1220px] mx-auto px-4 sm:px-6">

        {{-- ── Header Row (Title left, Buttons top-right) ── --}}
        <div class="flex items-center justify-between gap-4 mb-8">
            {{-- Left: Heading --}}
            <div>
                <p class="text-[0.70rem] uppercase tracking-[0.24em] text-slate-400 mb-1.5 font-bold flex items-center gap-1.5">
                    <span class="text-amber-500 font-extrabold">//</span> BROWSE
                </p>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 leading-tight tracking-tight">
                    Popular
                    <span class="text-amber-500">Categories</span>
                </h2>
            </div>

            {{-- Right: Circular Buttons --}}
            <div class="flex items-center gap-2.5 flex-shrink-0">
                {{-- Prev Button --}}
                <button id="pc-prev" aria-label="Previous"
                    class="w-10 h-10 flex items-center justify-center rounded-full
                               border border-slate-200 bg-white text-slate-700 shadow-sm
                               transition-all duration-200
                               hover:border-slate-900 hover:text-slate-900 hover:bg-slate-50 active:scale-95">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                </button>

                {{-- Next Button --}}
                <button id="pc-next" aria-label="Next"
                    class="w-10 h-10 flex items-center justify-center rounded-full
                               bg-[#0f172a] text-white shadow-sm
                               transition-all duration-200
                               hover:bg-amber-500 hover:text-black active:scale-95">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- ── 7-Item Circular Carousel Track ── --}}
        <div class="overflow-hidden w-full py-4 -my-4 select-none">
            <div id="pc-track" class="flex items-center pc-track-wrap gap-4">

                @foreach ($categories as $category)
                <a href="{{ route('shop') }}"
                    class="pc-item group flex flex-col items-center flex-shrink-0
                              no-underline text-inherit cursor-pointer">

                    {{-- Circle --}}
                    <div class="flex items-center justify-center
                                    w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-slate-50 p-4
                                    border-2 border-slate-100 overflow-hidden
                                    transition-all duration-300 ease-out
                                    group-hover:border-amber-400 group-hover:bg-amber-50/60
                                    group-hover:shadow-[0_8px_20px_rgba(245,158,11,0.18)]
                                    group-hover:scale-105">
                        <img src="{{ $category['image'] ? asset('storage/' . $category['image']) : '' }}"
                            alt="{{ $category['name'] }}"
                            class="w-full h-full object-contain
                                        transition-transform duration-300 group-hover:scale-110"
                            draggable="false">
                    </div>

                    {{-- Label --}}
                    <span class="mt-3 text-[0.78rem] sm:text-[0.84rem] font-bold
                                     text-slate-700 text-center leading-tight whitespace-nowrap
                                     transition-colors duration-200 group-hover:text-amber-600">
                        {{ $category['name'] }}
                    </span>
                </a>
                @endforeach

            </div>
        </div>

    </div>
</section>



<script>
    (function() {
        const track = document.getElementById('pc-track');
        const btnPrev = document.getElementById('pc-prev');
        const btnNext = document.getElementById('pc-next');
        if (!track) return;

        const initialItems = Array.from(track.children);
        if (initialItems.length === 0) return;

        // Ensure we have at least 14 items in the circle queue so 7 are on screen and 7+ buffer offscreen
        while (track.children.length < 14) {
            initialItems.forEach(item => {
                track.appendChild(item.cloneNode(true));
            });
        }

        let isAnimating = false;

        // Calculate step width (1 item width + gap)
        function getStep() {
            if (track.children.length >= 2) {
                return track.children[1].offsetLeft - track.children[0].offsetLeft;
            }
            return track.firstElementChild.offsetWidth + 16;
        }

        /* ── Next: Circular rotation (shift left, move first to last) ── */
        function next() {
            if (isAnimating) return;
            isAnimating = true;
            const step = getStep();

            track.style.transition = 'transform 0.32s cubic-bezier(0.25, 1, 0.5, 1)';
            track.style.transform = `translateX(-${step}px)`;

            let handled = false;

            function onEnd() {
                if (handled) return;
                handled = true;
                track.removeEventListener('transitionend', onEnd);
                track.style.transition = 'none';
                track.appendChild(track.firstElementChild);
                track.style.transform = 'translateX(0)';
                void track.offsetWidth; // force reflow
                isAnimating = false;
            }

            track.addEventListener('transitionend', onEnd);
            setTimeout(onEnd, 380); // safety fallback
        }

        /* ── Prev: Circular rotation (move last to first, slide in from left) ── */
        function prev() {
            if (isAnimating) return;
            isAnimating = true;
            const step = getStep();

            track.insertBefore(track.lastElementChild, track.firstElementChild);
            track.style.transition = 'none';
            track.style.transform = `translateX(-${step}px)`;
            void track.offsetWidth; // force reflow

            track.style.transition = 'transform 0.32s cubic-bezier(0.25, 1, 0.5, 1)';
            track.style.transform = 'translateX(0)';

            let handled = false;

            function onEnd() {
                if (handled) return;
                handled = true;
                track.removeEventListener('transitionend', onEnd);
                track.style.transition = 'none';
                isAnimating = false;
            }

            track.addEventListener('transitionend', onEnd);
            setTimeout(onEnd, 380); // safety fallback
        }

        /* ── Button Listeners ── */
        btnNext.addEventListener('click', next);
        btnPrev.addEventListener('click', prev);

        /* ── Mouse Drag & Touch Swipe (Circle Loop method) ── */
        let isDragging = false;
        let startX = 0;
        let currentDiff = 0;
        let hasMoved = false;

        function startDrag(clientX) {
            if (isAnimating) return;
            isDragging = true;
            hasMoved = false;
            startX = clientX;
            currentDiff = 0;
            track.classList.add('grabbing');
            track.style.transition = 'none';
        }

        function moveDrag(clientX) {
            if (!isDragging || isAnimating) return;
            currentDiff = clientX - startX;
            if (Math.abs(currentDiff) > 5) {
                hasMoved = true;
            }
            track.style.transform = `translateX(${currentDiff}px)`;
        }

        function endDrag() {
            if (!isDragging) return;
            isDragging = false;
            track.classList.remove('grabbing');

            if (currentDiff < -40) {
                next();
            } else if (currentDiff > 40) {
                prev();
            } else {
                // Spring back if small movement
                track.style.transition = 'transform 0.2s ease-out';
                track.style.transform = 'translateX(0)';
            }
            currentDiff = 0;
        }

        // Mouse Events
        track.addEventListener('mousedown', e => {
            startDrag(e.clientX);
            e.preventDefault();
        });
        window.addEventListener('mousemove', e => {
            moveDrag(e.clientX);
        });
        window.addEventListener('mouseup', () => {
            endDrag();
        });

        // Touch Events
        track.addEventListener('touchstart', e => {
            if (e.touches.length === 1) {
                startDrag(e.touches[0].clientX);
            }
        }, {
            passive: true
        });

        track.addEventListener('touchmove', e => {
            if (e.touches.length === 1) {
                moveDrag(e.touches[0].clientX);
            }
        }, {
            passive: true
        });

        track.addEventListener('touchend', () => {
            endDrag();
        });

        // Prevent accidental link opening while dragging
        track.addEventListener('click', e => {
            if (hasMoved) {
                e.preventDefault();
                e.stopPropagation();
                hasMoved = false;
            }
        }, true);
    })();
</script>