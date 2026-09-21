<x-layouts.auth>
    <div class="w-full bg-white rounded-2xl max-w-2xl shadow-lg p-8 md:p-12">
        <!-- Heading -->
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-gray-900">Create an Account</h2>
            <p class="text-sm text-gray-500 mt-2">Fill in your details to register</p>
        </div>
        @php
            $roles = \App\Models\Role::get();
        @endphp
        <!-- Form -->
        <form method="POST" id="registerForm" action="{{ route('admin.register.update') }}"
            class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @csrf
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700">Role</label>
                <select name="role"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-[#363636]">
                    <option value="">Choose Role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
                <p class="text-red-500 text-sm mt-1 error-name"></p>
                @error('role')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Full Name</label>
                <input name="name" type="text"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-[#363636]" />
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-red-500 text-sm mt-1 error-name"></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Mobile</label>
                <input name="mobile_number" type="number"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-[#363636]" />
                @error('mobile_number')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input name="email" type="email"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-[#363636]" />
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input name="password" type="password"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-[#363636]" />
                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input name="password_confirmation" type="password"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-[#363636]" />
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700">Security Code</label>
                <input name="code" type="text"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-[#363636]" />
                @error('code')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="col-span-2">
                <button type="submit"
                    class="text-white w-full bg-[#363636] text-black font-semibold py-3 rounded-lg shadow-md hover:bg-[#363636] transition-all duration-200">
                    Sign Up
                </button>
            </div>
        </form>
        <p class="text-sm text-gray-600 text-center mt-6">
            Already have an account?
            <a href="{{ route('admin.login') }}" class="text-[#363636] font-medium hover:underline">Sign In</a>
        </p>
    </div>
</x-layouts.auth>
