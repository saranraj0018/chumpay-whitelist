<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class ApiAuthController extends Controller
{

    public function userRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_name'     => 'required|string|max:255',
            'phone_number'  => 'required|digits:10|integer',
            'age'           => 'nullable|integer|min:1',
            'referal_code'  => 'nullable|string',
            'resend_otp'    => 'nullable|string|in:true,false'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $mobileNumber = $request->phone_number;
        $resendOtp    = $request->boolean('resend_otp');

        // Check if user already exists (only if not resending)
        if (!$resendOtp) {
            $existingUser = User::where('mobile_number', $mobileNumber)->first();

            if ($existingUser) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Mobile number already registered.',
                ], 409);
            }
        }

        // Static OTP
        $otp = "0000";

        // Store data in cache for 5 minutes
        Cache::put('otp_' . $mobileNumber, $otp, 300);
        Cache::put('user_data_' . $mobileNumber, [
            'user_name'    => $request->user_name,
            'age'          => $request->age,
            'referal_code' => $request->referal_code,
        ], 300);

        return response()->json([
            'status'  => 200,
            'message' => 'OTP sent successfully.',
            'otp'     => $otp // remove in production
        ], 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => ['required', 'digits:10', 'integer'],
            'resend_otp'    => 'nullable|string|in:true,false'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 409,
                'message' => $validator->errors()->first(),
            ], 409);
        }

        $mobile_number = $request->phone_number;
        $resendOtp    = $request->boolean('resend_otp');
        $user = User::where('mobile_number', $mobile_number)->first();
        $otp = "0000";
        Cache::put('otp_' . $mobile_number, $otp, 300);
        Cache::put('mobile_' . $mobile_number, $mobile_number, 300);

        if (!$user) {
            return response()->json([
                'status'  => 400,
                'new_user' => true,
                'message' => 'Sign Up With Your Mobile Number!',
            ], 400);
        }else {
            return response()->json([
                'new_user' => false,
                'status'  => 200,
                'message' => 'OTP Sent',
                'otp'     => $otp
            ],200);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'OTP Sent',
            'otp'     => $otp
        ], 200);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|digits:10|integer',
            'otp'          => 'required|digits:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 422,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $mobileNumber = $request->phone_number;

        // Get OTP from cache
        $storedOtp = Cache::get('otp_' . $mobileNumber);
        $storedData = Cache::get('user_data_' . $mobileNumber);

        if (!$storedOtp) {
            return response()->json([
                'status'  => 400,
                'message' => 'OTP expired or not found.',
            ], 400);
        }

        if ($storedOtp !== $request->otp) {
            return response()->json([
                'status'  => 400,
                'message' => 'Invalid OTP.',
            ], 400);
        }

        // Check if user already exists
        $user = User::where('mobile_number', $mobileNumber)->first();

        if (!$user) {
            $user = new User();
            $user->name          = $storedData['user_name'] ?? 'Guest';
            $user->mobile_number = $mobileNumber;
            $user->age           = $storedData['age'] ?? null;
            $user->referal_code  = $storedData['referal_code'] ?? null;
            $user->save();
        }

        // Clear cache
        Cache::forget('otp_' . $mobileNumber);
        Cache::forget('user_data_' . $mobileNumber);

        // Generate JWT token
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status'  => 200,
            'message' => $user->wasRecentlyCreated
                ? 'Register successful.'
                : 'Login successful.',
            'data'    => [
                'token'        => $token,
                'id'           => (string) $user->id,
                'username'     => (string) $user->name,
                'phone_number' => (string) $user->mobile_number,
            ],
        ], 200);
    }

    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'status' => 200,
                'message' => 'Logged out successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ]);
        }
    }
}
