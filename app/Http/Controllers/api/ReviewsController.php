<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReviewsController extends Controller
{
    public function productReviews(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'mgs'    => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $productId = $request->product_id;
            $reviews = Review::with('user:id,name,image_path')
                ->where('product_id', $productId)
                ->latest()
                ->get();

            $totalReviews  = $reviews->count();
            $averageRating = $totalReviews > 0 ? round($reviews->avg('rating'), 1) : 0;

            $fiveStarCount  = $reviews->where('rating', 5)->count();
            $fourStarCount  = $reviews->where('rating', 4)->count();
            $threeStarCount = $reviews->where('rating', 3)->count();
            $twoStarCount   = $reviews->where('rating', 2)->count();
            $oneStarCount   = $reviews->where('rating', 1)->count();

            $ratings = $reviews->map(function ($review) {
                return [
                    'user_name'   => $review->user->name ?? '',
                    'user_image'  => $review->user && $review->user->image_path
                        ? asset('storage/' . $review->user->image_path)
                        : '',
                    'stars'       => (int) $review->rating,
                    'description' => $review->review ?? '',
                    'image' => !empty($review->image)
                        ? collect(json_decode($review->image, true))
                            ->map(fn($img) => url('storage/'.$img))
                            ->toArray()
                        : [],
                    'date'        => $review->created_at
                        ? $review->created_at->format('d-m-Y')
                        : '',
                ];
            });

            return response()->json([
                'status' => 200,
                'mgs'    => 'Review Successfully',
                'data'   => [
                    [
                        'product_rating' => (string) $averageRating,
                        'rating_count'   => $totalReviews,
                        '5-stars'        => round($fiveStarCount/$totalReviews * 100),
                        '4-stars'        => round($fourStarCount/$totalReviews * 100),
                        '3-stars'        => round($threeStarCount/$totalReviews * 100),
                        '2-stars'        => round($twoStarCount/$totalReviews * 100),
                        '1-stars'        => round($oneStarCount/$totalReviews * 100),
                        'ratings'        => $ratings,
                    ]
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'mgs'    => 'Something went wrong',
                'error'  => $e->getMessage(),
            ], 500);
        }
    }

    public function addReview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'order_detail_id' => 'nullable|integer|exists:order_details,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string',
            'image' => 'nullable|array',
            'image.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'mgs' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $userId = auth()->id();

            if (!$userId) {
                return response()->json([
                    'status' => 401,
                    'mgs' => 'Unauthenticated user',
                ], 401);
            }

            $imagePaths = [];

            if ($request->hasFile('image')) {
                foreach ($request->file('image') as $image) {
                    $path = $image->store('reviews', 'public');
                    $imagePaths[] = $path;
                }
            }

            $lookup = $request->filled('order_detail_id')
                ? ['user_id' => $userId, 'order_detail_id' => $request->order_detail_id]
                : ['user_id' => $userId, 'product_id' => $request->product_id];

            $review = Review::updateOrCreate($lookup, [
                'product_id' => $request->product_id,
                'rating' => $request->rating,
                'review' => $request->review,
                'image' => count($imagePaths) ? json_encode($imagePaths) : null,
            ]);

            DB::commit(); //  MUST be before return

            return response()->json([
                'status' => 200,
                'message' => 'Reviews submitted successfully',
                'data' => $review
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 500,
                'mgs' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
