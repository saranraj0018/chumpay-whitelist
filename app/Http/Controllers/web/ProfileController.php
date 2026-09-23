<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        $this->data['user'] = Auth::user();

        return view('frontend.profile.main')->with($this->data);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:15'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'avatar' => ['nullable', 'image'],
        ]);
        $exists = User::whereNot('id', $user->id)->where('mobile_number', $request->phone)->first();
        if (! empty($exists)) {
            return response()->json(['status' => 404, 'message' => 'This mobile number Number Already exists!'], 404);
        }
        $profile_image = $user->image_path;
        if ($request->input('remove_avatar') === '1') {
            if ($user->image_path && Storage::disk('public')->exists($user->image_path)) {
                Storage::disk('public')->delete($user->image_path);
            }
            $profile_image = null;
        } elseif ($request->hasFile('avatar')) {
            if ($user->image_path && Storage::disk('public')->exists($user->image_path)) {
                Storage::disk('public')->delete($user->image_path);
            }
            $img_name = time().'_'.$request->file('avatar')->getClientOriginalName();
            $request->avatar->storeAs('user_image', $img_name, 'public');
            $profile_image = 'user_image/'.$img_name;
        }

        $update = User::where('id', $user->id)->update([
            'name' => $request->name,
            'email' => $request->email,
            'mobile_number' => $request->phone,
            'image_path' => $profile_image,
        ]);

        return back()->with('success', 'Profile updated successfully!');
    }

    public function orders()
    {
        $orders = Order::with('orderDetails.product')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('frontend.profile.ordersactivity.ordersactivity', compact('orders'));
    }

    public function orderStatus($order)
    {
        $order = Order::with([
            'orderDetails.product',
            'orderDetails.variantSizeValue',
            'orderDetails.variantColorValue',
            'orderDetails.review' => fn ($query) => $query->where('user_id', Auth::id()),
            'Address',
            'payment',
        ])
            ->where('id', $order)
            ->where('user_id', Auth::id())   // ensures users only see their own orders
            ->first();

        abort_if(! $order, 404);

        return view('frontend.profile.ordersactivity.orderstatus', compact('order'));
    }
}
