<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Events\NewNotification;
use App\Models\Ticket;

class TicketController extends Controller
{
    public function saveTicket(Request $request)
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json([
                'status' => 409,
                'message' => 'User Not Found',
            ], 200);
        }
        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'image' => 'nullable|image',
            'status' => 'nullable|in:pending,in_progress,on_hold,resolved,rejected'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 409,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        $data = $validator->validated();
        $tickets = new Ticket();
        $tickets->user_id = $userId;
        $tickets->description = $request['description'];
        if ($request->hasFile('image')) {
            $img_name = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->image->storeAs('ticket_image', $img_name, 'public');
            $tickets->image = 'ticket_image/' . $img_name;
        }
        $tickets->status = 'pending';
        $tickets->save();

        if ($tickets) {
            event(new NewNotification($userId, "Support Ticket", "Your ticket has been created successfully!.", 1, 1));
        }

        return response()->json([
            'status' => 200,
            'message' => 'Ticket created successfully',
        ]);
    }

    public function ticketLists()
    {
        $userId = auth()->id();

        if (!$userId) {
            return response()->json([
                'status' => 409,
                'message' => 'User Not Found',
            ], 200);
        }

        $get_ticket = Ticket::where('user_id', $userId)->get();

        // Map tickets to include full image URL
        $ticketsWithImageUrl = $get_ticket->map(function ($ticket) {
            return [
                'id' => $ticket->id,
                'title' => $ticket->title,
                'description' => $ticket->description,
                'status' => $ticket->status,
                'image' => $ticket->image ? url('storage/' . $ticket->image) : null, // full URL
                'created_at' => $ticket->created_at,
                'updated_at' => $ticket->updated_at,
            ];
        });

        return response()->json([
            'status' => 200,
            'message' => 'Ticket detail fetched successfully',
            'ticket_details' => $ticketsWithImageUrl
        ]);
    }
}
