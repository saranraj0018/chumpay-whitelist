<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $update = Notification::where('status', 0)->update(['status' => 1]);
        $this->data['notifications'] = Notification::where('role', 1)->orderBy('id', 'DESC')->paginate(15);
        return view('admin.notifications.view')->with($this->data);
    }

    public function markAsRead(Request $request)
    {
        $notification = Notification::where('id', $request->id)->update(['status' => 1]);
        return redirect()->back();
    }
}
