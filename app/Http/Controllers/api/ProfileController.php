<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $wallet = Wallet::where('user_id', $user->id)->first();
        return response()->json([
            "status" => 200,
            "msg"    => "Profile Screen",
            "data"   => [
                "user_name"     => $user->name,
                "user_image"    => $user->image_path ? url('storage/' . $user->image_path) : null,
                "phone_number"  => $user->mobile_number,
                "email"         => $user->email,
                "age"           => $user->age,
                "wallet_amount" => optional($user->wallet)->balance
                    + optional($user->wallet)->bonus_balance,
                "gender"        => $user->gender,
                "wallet_amount" => optional($wallet)->balance + optional($wallet)->bonus_balance,
                "valid_till"    => optional($wallet)->bonus_valid_until ??  '',
            ]
        ]);
    }

    public function editUser(Request $request)
    {
        $rules = [
            'user_name'     => 'required|string|max:255',
            'email'         => 'nullable|email',
            'phone_number'  => 'required|string|max:15',
            'gender'        => 'nullable|in:m,f,o',
            'age'           => 'nullable|integer|min:0',
            'user_image'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
        $userId = auth()->id();
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 409,
                'message' => $validator->errors()->first(),
            ], 409);
        }

        if ($request->hasFile('user_image')) {
            $img_name = time() . '_' . $request->file('user_image')->getClientOriginalName();
            $request->user_image->storeAs('user_image', $img_name, 'public');
            $image = 'user_image/' . $img_name;
        }else{
            $get_user = User::find($userId);
            $image = $get_user->image_path;
        }

        $update = User::where('id', $userId)->update([
            'name'              => $request->user_name,
            'image_path'        => $image ?? null,
            'mobile_number'     => $request->phone_number,
            'email'             => $request->email,
            'gender'            => $request->gender,
            'age'               => $request->age,
        ]);

        return response()->json([
            'status'  => 200,
            'message' => 'User updated successfully!',
        ], 200);
    }
}
