<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function notificationList()
    {
        $userId = Auth::id();
        $this->data['notifications'] = Notification::where('user_id', $userId)
                                   ->orderBy('created_at', 'desc')->get();
        $update = Notification::where(['user_id' => $userId])->update([
            'status' => 1
        ]);
        return view('frontend.profile.notifications.notifications')->with($this->data);
    }
}
