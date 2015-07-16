<?php

namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewGame extends Event
{
    use SerializesModels;
    const NEW_GAME_MSG = "New Game has started you have 60 seconds to join.";

    public $recipient;
    public $message;


    /**
     * Create a new event instance.
     * @param String $recipient
     * @return void
     */
    public function __construct($recipient)
    {
        $this->recipient = "#".$recipient;
        $this->message = self::NEW_GAME_MSG;
    }

}
