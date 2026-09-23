<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function adminAuthenticate(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        $get_admin = Admin::where('email', $request->email)->first();
        if (!$validator->passes()) {
            return redirect()->route('admin.login')
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }
        if (!Auth::guard('admin')->attempt([
            'email' => $request['email'],
            'password' => $request['password']
        ], $request->get('remember'))) {
            return redirect()->route('admin.login')->withErrors(['password' => 'Either Email/Password is incorrect'])
                ->withInput($request->only('email'));
        }
        $admin = Auth::guard('admin')->user();
        session()->put('user', $admin);
        session()->get('user');
        //        $role_id = Role::pluck('id')->all();
        if (!empty($admin->role) && !empty($role_id) && !in_array($admin->role, $role_id)) {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'You are not authorized to access'])
                ->withInput($request->only('email'));
        }

        return redirect()->route('dashboard');
    }

    public function registerUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role' => ['required'],
            'name' => ['required', 'string', 'max:255'],
            'mobile_number' => ['required', 'digits:10'],
            'email' => ['required', 'email', 'max:255', 'unique:' . Admin::class],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
            'code' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value !== 'ryh2026') {
                        $fail('The security code is invalid.');
                    }
                }
            ],
        ]);

        if (!$validator->passes()) {
            return redirect()->route('admin.register')
                ->withErrors($validator)
                ->withInput($request->only('email', 'user_name', 'role', 'referral_code'));
        }

        $admin = new Admin();
        $admin->name          = $request['name'];
        $admin->email         = $request['email'];
        $admin->password      = Hash::make($request['password']);
        $admin->role_id          = $request['role'];
        $admin->mobile_number = $request['mobile_number'];
        $admin->code          = $request['code'];
        $admin->save();

        Auth::guard('admin')->login($admin);
        return redirect()->route('dashboard');
    }

    public function logout(): \Illuminate\Http\RedirectResponse
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }

    public function user_logout(Request $request): \Illuminate\Http\RedirectResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate(); // Invalidate session
        $request->session()->regenerateToken(); // Regenerate CSRF token for security
        return redirect()->route('admin.login')->with('success', 'You have been logged out successfully.');
    }
}
