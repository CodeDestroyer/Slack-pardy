<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Services\Contracts\IMessageHandler;
use Event;
use Log;
use Cache;
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
    public $gameName;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->recipient = "#".$request->get("channel_name");
        $this->message = self::SIGNUP_CLOSED;
        $this->gameName = "game#{$request->get("channel_id")}";
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(IMessageHandler $messageHandler)
    {

        $messageHandler->sendMessage($this->recipient,$this->message);
        Cache::forever($this->gameName, false);

    }
}
