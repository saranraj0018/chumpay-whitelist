<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewNotification
{
    use Dispatchable, SerializesModels;

    public $userId;
    public $title;
    public $description;
    public $type;
    public $role;

    public function __construct($userId, $title, $description, $type, $role)
    {
        $this->userId = $userId;
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->role = $role;
    }
}
