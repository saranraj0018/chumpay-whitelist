<?php

namespace App\Http\Controllers\admin;

use App\Events\NewNotification;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class OrderController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function view(Request $request)
    {
        $orders = Order::with('user', 'Address', 'orderDetails', 'payment')->whereHas('payment', function ($q) {
            $q->where('status', 'PAID');
        })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('admin.orders.view', compact('orders'));
    }

    public function updateStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_id' => 'required',
                'status'   => 'required|integer',
                'date'     => 'required|date',
                'id'       => 'required|exists:orders,id',
            ]);
            $order = Order::with('user')->findOrFail($validated['id']);
            $user  = $order->user;
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            $status = (int) $validated['status'];
            $date   = $validated['date'];
            // Update status timestamps
            switch ($status) {
                case 3:
                    $order->shipped_at = $date;
                    break;
                case 4:
                    $order->delivered_at = $date;
                    break;
                case 5:
                    $order->cancelled_at = $date;
                    break;
                case 6:
                    $order->refunded_at = $date;
                    break;
            }
            $order->status = $status;
            if ($request->hasFile('refund_image')) {
                $img_name = time() . '_' . $request->file('refund_image')->getClientOriginalName();
                $request->file('refund_image')->storeAs('refunds', $img_name, 'public');
                $order->refund_image = 'refunds/' . $img_name;
            } elseif ($request->has('existing_image')) {
                $order->refund_image = $request->existing_image;
            }
            if ($request->has('refund_note')) {
                $order->refund_note = $request->refund_note;
            }

            $order->save(); // Save first (important)

            try {
                if (!empty($user->fcm_token)) {
                    match ($status) {
                        2 => $this->sendOrderNotification($user, $order, 'inprogress', 'Order In Progress'),
                        3 => $this->sendOrderNotification($user, $order, 'shipped', 'Order Shipped'),
                        4 => $this->sendOrderNotification($user, $order, 'delivered', 'Order Delivered'),
                        5 => $this->sendOrderNotification($user, $order, 'cancelled', 'Order Cancelled'),
                        6 => $this->sendOrderNotification($user, $order, 'refunded', 'Order Refunded'),
                        default => null,
                    };
                }
            } catch (\Throwable $e) {
                Log::error('Notification failed: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        } catch (\Throwable $e) {
            Log::error('Update Status Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    /**
     * Helper function to send FCM notification
     */
    protected function sendOrderNotification($user, $order, $statusName, $title)
    {
        if (empty($user->fcm_token)) {
            return;
        }
        if ($user->fcm_token) {
            $notification = new Notification();
            $notification->user_id = $user->id;
            $notification->title = $title;
            $notification->description = "Your order #{$order->order_id} status is now {$statusName}";
            // $notification->type = 1;
            $notification->role = 2;
            $notification->save();

            $this->firebase->sendNotification(
                $user->fcm_token,
                $title,
                "Your order #{$order->order_id} status is now {$statusName}",
                [
                    'order_id' => $order->order_id,
                    'status' => $statusName,
                    'type' => 1
                ]
            );
        }
    }
}
