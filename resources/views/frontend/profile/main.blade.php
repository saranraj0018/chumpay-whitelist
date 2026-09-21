@extends('frontend.app')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    <div class="flex flex-col md:flex-row gap-6">
        <div class="w-full md:w-[25%]">
            @include('frontend.profile.sidebar')
        </div>
        <div class="w-full md:w-[50%]">
            <div class="w-full bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 sm:p-8">
                @if(session('success'))
                <div class="mb-5 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium">
                    {{ session('success') }}
                </div>
                @endif
                @if($errors->any())
                <div class="mb-5 px-4 py-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    <div class="flex flex-col sm:flex-row sm:items-center gap-6 mb-6 pb-6 border-b border-slate-100">
                        <input type="hidden" name="exiting_image" id="exiting_image" value="{{ $user->image_path ?? '' }}" />
                        <img id="avatarPreview" src="{{ $user->image_path ? asset('storage/' . $user->image_path) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=000000&color=ffffff' }}" class="w-20 h-20 rounded-full object-cover ring-4 ring-amber-500/20 shadow-sm" alt="profile">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <input type="file" name="avatar" id="fileInput" class="hidden" accept="image/*">
                                <button type="button" onclick="document.getElementById('fileInput').click()"
                                    class="bg-[#0f172a] hover:bg-amber-500 hover:text-slate-950 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition-all duration-200 shadow-xs cursor-pointer">
                                    Upload Photo
                                </button>
                                <button type="button" id="removeBtn"
                                    class="border border-slate-200 hover:border-slate-300 hover:bg-slate-50 px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 transition-colors duration-200 cursor-pointer">
                                    Remove
                                </button>
                            </div>
                            <input type="hidden" name="remove_avatar" id="removeAvatarInput" value="0">
                            <p class="text-xs text-slate-400">
                                Make sure the image is at least 400×400px and under 5 MB.
                            </p>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-700">User Name</label>
                        <input type="text" name="name"
                            value="{{ $user->name ?? ''}}"
                            placeholder="Enter Your User Name"
                            class="w-full mt-2 px-4 py-3 rounded-xl border @error('name') border-rose-400 @else border-slate-200 @enderror focus:outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 text-sm transition-all">
                        @error('name')
                        <p class="text-rose-500 text-xs mt-1.5 pl-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-700">Mobile Number</label>
                        <div class="flex items-center mt-2 border @error('phone') border-rose-400 @else border-slate-200 @enderror rounded-xl overflow-hidden focus-within:border-amber-500 focus-within:ring-2 focus-within:ring-amber-500/20 transition-all bg-white">
                            <div class="flex items-center gap-2 px-4 py-3 bg-slate-50 border-r border-slate-200">
                                <img src="https://flagcdn.com/w20/in.png" class="w-5" alt="IN">
                                <span class="text-xs font-bold text-slate-700">+91</span>
                            </div>
                            <input type="text" name="phone"
                                value="{{ $user->mobile_number ?? ''}}"
                                placeholder="Enter Mobile Number"
                                class="flex-1 px-4 py-3 outline-none text-sm bg-transparent">
                            @if($user->phone)
                            <span class="text-emerald-600 text-xs font-semibold pr-4">✔ Verified</span>
                            @endif
                        </div>
                        @error('phone')
                        <p class="text-rose-500 text-xs mt-1.5 pl-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-700">Email</label>
                        <input type="email" name="email"
                            value="{{ $user->email ?? ''}}"
                            placeholder="Enter Your Email"
                            class="w-full mt-2 px-4 py-3 rounded-xl border @error('email') border-rose-400 @else border-slate-200 @enderror focus:outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 text-sm transition-all">
                        @error('email')
                        <p class="text-rose-500 text-xs mt-1.5 pl-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Submit -->
                    <div class="pt-2">
                        <button type="submit" class="bg-[#0f172a] hover:bg-amber-500 hover:text-slate-950 text-white font-semibold px-8 py-3 rounded-xl text-sm transition-all duration-200 shadow-md cursor-pointer">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Avatar Preview & Remove JS --}}
<script>
    const fileInput = document.getElementById('fileInput');
    const avatarPreview = document.getElementById('avatarPreview');
    const removeBtn = document.getElementById('removeBtn');
    const removeAvatarInput = document.getElementById('removeAvatarInput');

    const defaultAvatar = "https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=000000&color=ffffff";

    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => {
                avatarPreview.src = e.target.result;
                removeAvatarInput.value = '0';
            };
            reader.readAsDataURL(file);
        }
    });

    removeBtn.addEventListener('click', function() {
        avatarPreview.src = defaultAvatar;
        fileInput.value = '';
        removeAvatarInput.value = '1';
    });
</script>

@endsection