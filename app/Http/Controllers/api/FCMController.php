<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FCMController extends Controller
{
    public function saveFCMToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fcm_token' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 409,
                    'message' => $validator->errors()->first(),
                ], 409);
            }

            $data = $validator->validated();
            $userId = auth()->id();
            $update = User::where('id', $userId)->update([
                'fcm_token' => $data['fcm_token']
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'FCM Token updated successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => $th->getCode() ?: 500,
                'message' => $th->getMessage(),
            ], $th->getCode() ?: 500);
        }
    }
}
