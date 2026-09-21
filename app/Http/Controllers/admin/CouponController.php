<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function view(Request $request)
    {
        $coupons = Coupon::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.coupon.view', compact('coupons'));
    }

    public function save(Request $request)
    {
        $rules = [
            'coupon_code'   => 'required|unique:coupons,coupon_code,' . $request->coupon_id,
            'discount_type' => 'required|in:1,2',
            'discount_value' =>'required|numeric',
            'description'   => 'required|string',
            'apply_for'     => 'required|in:1,2',
            'max_price'     => 'nullable|numeric',
            'min_price'     => 'nullable|numeric',
            'order_count'   => 'nullable|integer',
            'expires_at'    => 'nullable|date',
            'status'        => 'required|boolean',
        ];

        $request->validate($rules);
        try {
            
            if (!empty($request->coupon_id)) {
                $coupon = Coupon::find($request->coupon_id);
                if (!$coupon) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Coupon not found'
                    ], 404);
                }
                $message = 'Coupon updated successfully';
            } else {
                $coupon = new Coupon();
                $message = 'Coupon created successfully';
            }

            $coupon->coupon_code   = $request->coupon_code;
            $coupon->discount_type = $request->discount_type;
            $coupon->discount_value = $request->discount_value;
            $coupon->description   = $request->description;
            $coupon->apply_for     = $request->apply_for;
            $coupon->max_price     = $request->max_price ?? 0;
            $coupon->min_price     = $request->min_price ?? 0;
            $coupon->order_count   = $request->order_count ?? 0;
            $coupon->expires_at    = $request->expires_at;
            $coupon->status        = $request->status;
            $coupon->save();

            return response()->json([
                'success' => true,
                'message' => $message,
                'coupon'  => $coupon
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function destroy(Request $request)
    {
        if (!$request->id) {
            return response()->json(['success' => false, 'message' => 'Coupon ID is required'], 400);
        }

        $coupon = Coupon::find($request->id);
        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Coupon not found'], 404);
        }

        $coupon->delete();
        return response()->json(['success' => true, 'message' => 'Coupon deleted successfully']);
    }
}
