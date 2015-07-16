<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Services\Contracts\IMessageHandler;
use Event;
use Log;
use App\Events\SignupClosed;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class JoinGame extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    const SIGNUP_CLOSED = "Signups are now closed";

    public $recipient;
    public $message;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($recipient)
    {
        $this->recipient = "#".$recipient;
        $this->message = self::SIGNUP_CLOSED;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(IMessageHandler $messageHandler)
    {

        $messageHandler->sendMessage($this->recipient,$this->message);
        //SetupGame
        //PickPlayerOne\
        //Send board
        //pass in board
    }
}
