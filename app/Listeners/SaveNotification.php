<?php

namespace App\Listeners;

use App\Events\NewNotification;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SaveNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NewNotification $event): void
    {
        $notification = new Notification();
        $notification->user_id = $event->userId;
        $notification->title = $event->title;
        $notification->description = $event->description;
        $notification->role = $event->role;
        $notification->save();
    }
}
