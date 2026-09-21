<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\OrderDetail;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, $product)
    {
        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'review' => 'required|string|max:500',
            'image' => 'nullable|array|max:5',
            'image.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'order_detail_id' => 'nullable|integer|exists:order_details,id',
        ]);

        $orderDetailId = $request->integer('order_detail_id');

        if ($orderDetailId) {
            $orderDetail = OrderDetail::where('id', $orderDetailId)
                ->where('product_id', $product)
                ->whereHas('order', function ($query) {
                    $query->where('user_id', Auth::id());
                })
                ->firstOrFail();

            abort_if($orderDetail->order->status != 4, 403);
        }

        $imagePaths = [];

        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $image) {
                $img_name = time() . '_' . $image->getClientOriginalName();
                $image->storeAs('reviews/', $img_name, 'public');
                $imagePaths[] =  'reviews/' . $img_name;
                }
        }
       
        Review::updateOrCreate(
            $orderDetailId
                ? [
                    'user_id' => Auth::id(),
                    'order_detail_id' => $orderDetailId
                ]
                : [
                    'user_id' => Auth::id(),
                    'product_id' => $product
                ],
            [
                'product_id' => $product,
                'rating' => $request->rating,
                'review' => $request->review,
                'image' => $imagePaths ? json_encode($imagePaths) : null,
            ]
        );

        return redirect()->back()->with([
            'toast_type' => 'success',
            'toast_message' => 'Review submitted successfully!',
        ]);
    }
}
