<?php

namespace App\Http\Controllers\web;

use App\Events\NewNotification;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $this->data['tickets'] = Ticket::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
        return view('frontend.profile.supporthelp.supporthelp', $this->data);
    }

    public function ticketSave(Request $request)
    {
        $userId = auth()->id();
        if (!$userId) {
            return redirect()->back()->with('error', 'User Not Found');
        }
        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'image'       => 'nullable|image|max:5120', // 5MB
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $ticket = new Ticket();
        $ticket->user_id     = $userId;
        $ticket->description = $request->description;

        if ($request->hasFile('image')) {
            $img_name = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->storeAs('ticket_image', $img_name, 'public');
            $ticket->image = 'ticket_image/' . $img_name;
        }

        $ticket->status = 'pending';
        $ticket->save();

        if ($ticket) {
            event(new NewNotification(
                $userId,
                "Support Ticket",
                "Your ticket has been created successfully!.",
                1,
                1
            ));
        }

        return redirect()
            ->route('support_help_lists')
            ->with('success', 'Ticket created successfully');
    }
}
