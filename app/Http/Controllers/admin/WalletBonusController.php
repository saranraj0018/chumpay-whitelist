<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\WalletOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletBonusController extends Controller
{
    public function view(Request $request)
    {
        $this->data['wallet_offer'] = WalletOffer::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.wallet.view')->with($this->data);
    }

    public function save(Request $request)
    {

        $rules = [
            'name' => 'nullable|string',
            'minimum_amount' => 'nullable|numeric',
            'maximum_amount'   => 'required|numeric',
            'bonus_amount'     => 'required|numeric',
            'item_amount'     => 'nullable|numeric',
            'status'        => 'required|boolean',
            'valid_days'        => 'required|integer',
        ];

        $request->validate($rules);
        try {
            DB::beginTransaction();
            $exists = WalletOffer::where('id', '!=', $request->wallet_bonus_id)
                ->where(function ($q) use ($request) {
                    $q->whereBetween('min_amount', [$request->minimum_amount, $request->maximum_amount])
                        ->orWhereBetween('max_amount', [$request->minimum_amount, $request->maximum_amount])
                        ->orWhere(function ($q2) use ($request) {
                            $q2->where('min_amount', '<=', $request->minimum_amount)
                                ->where('max_amount', '>=', $request->maximum_amount);
                        });
                })
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'This wallet bonus range already exists!'
                ], 409);
            }

            if (!empty($request->wallet_bonus_id)) {
                $wallet = WalletOffer::find($request->wallet_bonus_id);
                if (!$wallet) {
                    return response()->json([
                        'success' => false,
                        'message' => 'WalletOffer not found'
                    ], 404);
                }
                $message = 'WalletOffer updated successfully!';
            } else {
                $wallet = new WalletOffer();
                $message = 'WalletOffer created successfully!';
            }

            $wallet->name   = $request->name;
            $wallet->min_amount = $request->minimum_amount;
            $wallet->max_amount = $request->maximum_amount;
            $wallet->bonus_amount   = $request->bonus_amount;
            $wallet->item_amount     = $request->item_amount;
            $wallet->valid_days     = $request->valid_days;
            $wallet->is_active        = $request->status;
            $wallet->save();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => $message,
                'coupon'  => $wallet
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
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
