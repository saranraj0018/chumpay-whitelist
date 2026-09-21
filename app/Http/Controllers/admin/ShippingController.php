<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Shipping;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ShippingController extends Controller
{
    public function view()
    {
        $shippings = Shipping::paginate(10);
        return view('admin.shipping.view', compact('shippings'));
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'delivery_fee' => 'nullable|numeric|min:0',
            'minimum_delivery_amount' => 'nullable|numeric|min:0',
            'maximum_delivery_amount' => 'nullable|numeric|gt:minimum_delivery_amount',
            'status' => 'required|boolean'

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 409,
                'message' => $validator->errors()->first(),
            ], 409);
        }
        try {
            DB::beginTransaction();
            $exists = Shipping::where('id', '!=', $request->shipping_id)
                ->where(function ($q) use ($request) {
                    $q->where('minimum_delivery_amount', '<=', $request->maximum_delivery_amount)
                        ->where(function ($q2) use ($request) {
                            $q2->whereNull('maximum_delivery_amount')
                                ->orWhere('maximum_delivery_amount', '>=', $request->minimum_delivery_amount);
                        });
                })
                ->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'This delivery amount range already exists!'
                ], 409);
            }

            $shipping = $request->shipping_id ? Shipping::find($request->shipping_id) : new Shipping();
            $message = $request->shipping_id ? 'Shipping updated successfully' : 'Shipping created successfully';
            $shipping->name = $request->name;
            $shipping->delivery_fee = $request->delivery_fee ?? 0;
            $shipping->minimum_delivery_amount = $request->minimum_delivery_amount;
            $shipping->maximum_delivery_amount = $request->maximum_delivery_amount;
            $shipping->status = $request->status;
            $shipping->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'shipping' => $shipping
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Shipping Save Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong! Please try again later.'
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        $shipping = Shipping::find($request->id);
        if (!$shipping) return response()->json(['success' => false, 'message' => 'Shipping not found'], 404);
        $shipping->delete();
        return response()->json(['success' => true, 'message' => 'Shipping deleted successfully']);
    }

    public function getTax()
    {
        $tax = Tax::first();
        return response()->json($tax);
    }

    public function getShipping()
    {
        $tax = Tax::first();
        return response()->json($tax);
    }

    public function taxSave(Request $request)
    {
        $request->validate([
            'percent' => 'required|numeric|min:1',
        ]);

        $tax = Tax::first();

        if (!$tax) {
            $tax = new Tax();
        }

        $tax->percent = $request->percent;
        $tax->save();

        return response()->json([
            'success' => true,
            'message' => 'Tax saved successfully!'
        ]);
    }

    public function shippingSave(Request $request)
    {

        $request->validate([
            'default_shipping' => 'nullable|numeric|',
        ]);

        $tax = Tax::first();
        if (!$tax) {
            $tax = new Tax();
        }
        $tax->default_shipping = $request->default_shipping;
        $tax->save();

        return response()->json([
            'success' => true,
            'message' => 'Shipping saved successfully!'
        ]);
    }

    public function getPlatformFee()
    {
        $tax = Tax::first();
        return response()->json($tax);
    }

    public function platformfeeSave(Request $request)
    {
        $request->validate([
            'platform_fee' => 'nullable|numeric|',
        ]);
        $tax = Tax::first();
        if (!$tax) {
            $tax = new Tax();
        }
        $tax->platform_fee = $request->platform_fee;
        $tax->save();

        return response()->json([
            'success' => true,
            'message' => 'Platform fee saved successfully!'
        ]);
    }
}
