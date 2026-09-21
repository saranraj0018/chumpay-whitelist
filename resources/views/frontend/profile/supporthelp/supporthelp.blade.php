@extends('frontend.app')

@section('content')

<div class="max-w-7xl mx-auto px-4 py-10">

    <div class="flex flex-col md:flex-row gap-6">

        <!-- Sidebar -->
        <div class="w-full md:w-[25%]">
            @include('frontend.profile.sidebar')
        </div>

        <!-- Content -->
        <div class="w-full md:w-[75%]">

            <div class="w-full bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200/80">

                @if(session('success'))
                <div class="mb-5 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium">{{ session('success') }}</div>
                @endif

                <!-- Support Form -->
                <form action="{{ route('support_help_save') }}" method="POST" enctype="multipart/form-data" class="mb-10">
                    @csrf
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">Description</label>
                    <textarea name="description" placeholder="Explain About Your Problem..." required
                        class="w-full h-28 rounded-xl border border-slate-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 outline-none resize-none transition-all">{{ old('description') }}</textarea>
                    @error('description')
                    <p class="text-xs text-rose-500 mt-1 pl-1">{{ $message }}</p>
                    @enderror
                    <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Add An Image To Provide More Details
                        <span class="text-slate-400 font-normal normal-case">(Optional)</span>
                    </p>
                    <!-- Upload area (shown when no image selected) -->
                    <label id="uploadArea"
                        class="mt-3 flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-slate-300 hover:border-amber-400 hover:bg-amber-50/20 rounded-xl cursor-pointer transition-all">
                        <input type="file" name="image" id="ticketImage" accept="image/*" class="hidden" />
                        <div class="flex items-center gap-2 text-slate-600 text-xs font-semibold">
                            <span>Attach Image</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 16V4m0 0l-4 4m4-4l4 4M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1" />
                            </svg>
                        </div>
                    </label>
                    <!-- Preview area (shown after image selected) -->
                    <div id="previewArea" class="hidden mt-3 relative w-full h-40 rounded-xl overflow-hidden border border-slate-200">
                        <img id="previewImg" src="" alt="Preview" class="w-full h-full object-cover" />
                        <button type="button" id="removeImageBtn"
                            class="absolute top-2 right-2 w-7 h-7 flex items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/80 transition cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <p id="previewFileName" class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-xs px-3 py-1 truncate"></p>
                    </div>
                    @error('image')
                    <p class="text-xs text-rose-500 mt-1 pl-1">{{ $message }}</p>
                    @enderror
                    <button type="submit"
                        class="mt-5 bg-[#0f172a] hover:bg-amber-500 hover:text-slate-950 text-white text-sm font-semibold px-8 py-3 rounded-xl transition-all duration-200 shadow-md cursor-pointer">
                        Submit Ticket
                    </button>
                </form>

                <!-- Ticket List -->
                <div>
                    <h2 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100">Supported Tickets</h2>
                    <div class="space-y-4 max-h-[60vh] overflow-y-auto no-scrollbar pr-1">
                        @forelse($tickets as $ticket)
                        <div class="border border-slate-200/80 rounded-2xl p-5 bg-white shadow-xs hover:border-slate-300 transition-colors">
                            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <p class="text-sm font-bold text-slate-900">Ticket ID: #{{ $ticket->id }}</p>
                                    </div>

                                    <p class="text-[11px] font-medium text-slate-400 mb-3">Created On: {{ $ticket->created_at->format('d/m/Y') }}</p>

                                    <div class="mb-3">
                                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Description:</p>
                                        <p class="text-xs text-slate-600 leading-relaxed">{{ $ticket->description }}</p>
                                    </div>

                                    @if($ticket->image)
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Attachments:</p>
                                        <a href="{{ asset('storage/' . $ticket->image) }}" target="_blank" class="inline-block">
                                            <img src="{{ asset('storage/' . $ticket->image) }}" alt="Ticket attachment"
                                                class="w-20 h-20 object-cover rounded-xl border border-slate-200 hover:opacity-85 transition shadow-2xs" />
                                        </a>
                                    </div>
                                    @endif
                                </div>

                                <div class="w-full md:w-auto flex md:block items-center justify-between md:text-right gap-3 flex-shrink-0">
                                    @php
                                    $statusMap = [
                                    'pending' => 'bg-amber-50 text-amber-700 border border-amber-200/80',
                                    'in_progress' => 'bg-sky-50 text-sky-700 border border-sky-200/80',
                                    'on_hold' => 'bg-slate-100 text-slate-700 border border-slate-200',
                                    'resolved' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/80',
                                    'rejected' => 'bg-rose-50 text-rose-700 border border-rose-200/80',
                                    ];
                                    $statusLabel = [
                                    'pending' => 'Pending',
                                    'in_progress' => 'In Progress',
                                    'on_hold' => 'On Hold',
                                    'resolved' => 'Resolved',
                                    'rejected' => 'Rejected',
                                    ];
                                    @endphp
                                    <span class="inline-flex items-center justify-center px-3.5 py-1 rounded-full text-xs font-bold shadow-2xs {{ $statusMap[$ticket->status] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $statusLabel[$ticket->status] ?? ucfirst($ticket->status) }}
                                    </span>

                                    <p class="text-[11px] text-slate-400 mt-2">Updated: {{ $ticket->updated_at->format('d/m/Y') }}</p>
                                </div>

                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-slate-400 text-center py-8">No tickets raised yet.</p>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const fileInput = document.getElementById('ticketImage');
        const uploadArea = document.getElementById('uploadArea');
        const previewArea = document.getElementById('previewArea');
        const previewImg = document.getElementById('previewImg');
        const previewName = document.getElementById('previewFileName');
        const removeBtn = document.getElementById('removeImageBtn');

        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;

            const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert('Please select a valid image file.');
                this.value = '';
                return;
            }
            if (file.size > 5 * 1024 * 1024) { // 5MB
                alert('Image must be smaller than 5MB.');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewName.textContent = file.name;
                uploadArea.classList.add('hidden');
                previewArea.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        });

        removeBtn.addEventListener('click', function() {
            fileInput.value = '';
            previewImg.src = '';
            previewArea.classList.add('hidden');
            uploadArea.classList.remove('hidden');
        });
    });
</script>

@endsection