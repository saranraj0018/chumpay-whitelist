<x-layouts.auth>
    <div class="w-full max-w-5xl bg-white rounded-3xl shadow-lg overflow-hidden flex border">
        <div class="hidden md:flex w-1/2 bg-white items-center justify-center p-8">
            <img src="{{ asset('images/chumpay.png') }}" alt="Chumpay" class="max-w-full max-h-full object-contain" />
        </div>
        <div class="w-full md:w-1/2 p-10 flex flex-col justify-center">
            <h2 class="text-2xl font-bold text-[#363636] mb-2">Welcome Back</h2>
            <p class="text-sm text-gray-500 mb-6">Sign in to your Chumpay dashboard</p>
            <form method="POST" action="{{ route('admin.authenticate') }}" class="space-y-5" x-data="{ show: false, loading: false }"
                @submit="loading = true">
                @csrf
                <div>
                    <label class="text-sm text-gray-700">Email</label>
                    <input type="email" name="email" class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-[#134074]"
                        placeholder="you@example.com" />
                    @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="text-sm text-gray-700">Password</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1
                              focus:outline-none focus:ring-2 focus:ring-[#363636] pr-10"
                            placeholder="••••••••" />
                        <button type="button" @click="show = !show"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 text-lg">
                            <i x-show="!show" class="fa fa-eye-slash" aria-hidden="true"></i>
                            <i x-show="show" class="fa fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="w-full bg-[#363636] text-white py-2 rounded-md flex items-center justify-center gap-2 hover:bg-[#363636]" :disabled="loading">
                    <span x-show="!loading">Sign In</span>
                    <span x-show="loading" class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span>Signing In...</span>
                    </span>
                </button>
            </form>
            <p class="text-sm text-center text-gray-600 mt-6">
                Don’t have an account?
                <a href="{{ route('admin.register') }}" class="text-blue font-medium hover:underline">
                    Sign Up
                </a>
            </p>
        </div>
    </div>
</x-layouts.auth>