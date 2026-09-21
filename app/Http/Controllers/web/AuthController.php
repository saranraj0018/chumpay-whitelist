<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
     public function sendOtp(Request $request)
    {
        $request->validate([
            'mobile' => 'required|digits:10',
        ]);

        $mobile = $request->mobile;
        $otp = '0000';

         Cache::put('otp_' . $mobile, $otp, now()->addMinutes(5));


        return response()->json([
            'status'  => true,
            'message' => 'OTP sent successfully',
            'mobile'  => $mobile,
        ]);
    }

    // Step 2: Verify OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'mobile' => 'required|digits:10',
            'otp'    => 'required|digits:4',
        ]);

        $cachedOtp = Cache::get('otp_' . $request->mobile);

        if (!$cachedOtp || $cachedOtp !== $request->otp) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid or expired OTP',
            ], 422);
        }

        // Check if user already exists
        $user = User::where('mobile_number', $request->mobile)->first();

        if ($user) {
            // Existing user → log in directly
            Auth::login($user);
            Cache::forget('otp_' . $request->mobile);

            return response()->json([
                'status'     => true,
                'message'    => 'Login successful',
                'is_new'     => false,
            ]);
        }

        // New user → keep mobile verified in cache for registration step
        Cache::put('verified_mobile_' . $request->mobile, true, now()->addMinutes(10));

        return response()->json([
            'status'  => true,
            'message' => 'OTP verified',
            'is_new'  => true,
            'mobile'  => $request->mobile,
        ]);
    }

    // Step 3: Save name + email + mobile to users table
    public function registerUser(Request $request)
    {
        $request->validate([
            'mobile' => 'required|digits:10',
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|unique:users,email',
        ]);

        // Ensure mobile was actually verified
        if (!Cache::get('verified_mobile_' . $request->mobile)) {
            return response()->json([
                'status'  => false,
                'message' => 'Mobile number not verified',
            ], 422);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'mobile_number'   => $request->mobile,
            'password' => Hash::make($request->mobile),
        ]);

        Auth::login($user);

        // Clean up cache
        Cache::forget('otp_' . $request->mobile);
        Cache::forget('verified_mobile_' . $request->mobile);

        return response()->json([
            'status'  => true,
            'message' => 'Registration successful',
        ]);
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    }
}
