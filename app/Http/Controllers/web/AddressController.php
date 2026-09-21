<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    public function addressList()
    {
        if (!Auth::check()) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized']);
        }

        $addresses = Address::where('created_by', Auth::id())
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => 200,
            'addresses' => $addresses,
        ]);
    }

    public function saveAddress(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized']);
        }

        $validator = Validator::make($request->all(), [
            'address_id'   => 'nullable|integer|exists:addresses,id',
            'name'         => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'address_type' => 'required|string|max:50',
            'state'        => 'required|string|max:100',
            'address'      => 'required|string|max:500',
            'city'         => 'required|string|max:100',
            'pincode'      => 'required|string|max:20',
            'landmark'     => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $userId = Auth::id();

        $payload = [
            'name'         => $request->name,
            'phone_number' => $request->phone_number,
            'address_type' => $request->address_type,
            'state'        => $request->state,
            'address'      => $request->address,
            'city'         => $request->city,
            'pincode'      => $request->pincode,
            'landmark'     => $request->landmark,
            'created_by'   => $userId,
        ];

        if ($request->filled('address_id')) {
            // EDIT existing (only if it belongs to this user)
            $address = Address::where('id', $request->address_id)
                ->where('created_by', $userId)
                ->first();

            if (!$address) {
                return response()->json(['status' => 404, 'message' => 'Address not found'], 404);
            }

            $address->update($payload);
            $message = 'Address updated successfully.';
        } else {
            // ADD new — make default if it's the user's first address
            $hasAny = Address::where('created_by', $userId)->exists();
            $payload['is_default'] = $hasAny ? 0 : 1;

            $address = Address::create($payload);
            $message = 'Address saved successfully.';
        }

        return response()->json([
            'status' => 200,
            'message' => $message,
            'address' => $address->fresh(),
        ]);
    }

    public function setDefaultAddress(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized']);
        }

        $request->validate([
            'address_id' => 'required|integer|exists:addresses,id',
        ]);

        $userId = Auth::id();

        $address = Address::where('id', $request->address_id)
            ->where('created_by', $userId)
            ->first();

        if (!$address) {
            return response()->json(['status' => 404, 'message' => 'Address not found'], 404);
        }

        DB::transaction(function () use ($userId, $address) {
            // unset ALL of this user's defaults first
            Address::where('created_by', $userId)->update(['is_default' => 0]);
            // set only the chosen one
            Address::where('id', $address->id)->update(['is_default' => 1]);
        });

        return response()->json([
            'status' => 200,
            'message' => 'Default address updated.',
            'address_id' => $address->id,
        ]);
    }

    public function deleteAddress(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized']);
        }

        $request->validate([
            'address_id' => 'required|integer|exists:addresses,id',
        ]);

        $userId = Auth::id();

        $address = Address::where('id', $request->address_id)
            ->where('created_by', $userId)
            ->first();

        if (!$address) {
            return response()->json(['status' => 404, 'message' => 'Address not found'], 404);
        }

        // BLOCK delete if this address is used by any order
        $usedInOrder = Order::where('address_id', $address->id)->whereNot('status',4)->exists();
        if ($usedInOrder) {
            return response()->json([
                'status' => 422,
                'message' => 'This address is linked to an order and cannot be deleted.',
            ], 422);
        }

        $wasDefault = $address->is_default == 1;

        DB::transaction(function () use ($address, $wasDefault, $userId) {
            $address->delete();

            // if we deleted the default, promote another address to default
            if ($wasDefault) {
                $next = Address::where('created_by', $userId)->orderByDesc('id')->first();
                if ($next) {
                    $next->update(['is_default' => 1]);
                }
            }
        });

        return response()->json([
            'status' => 200,
            'message' => 'Address deleted successfully.',
            'deleted_id' => $address->id,
        ]);
    }
}
