<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Services\Contracts\IMessageHandler;
use Event;
use Log;
use Cache;
use App\Events\SignupClosed;
use App\Services\BoardService;
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
    public $boardKey;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->recipient = "#".$request->get("channel_name");
        $this->message = self::SIGNUP_CLOSED;
        $this->gamekey = "game#{$request->get("channel_id")}";
        $this->boardKey = "game:board#{$request->get("channel_id")}";
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(IMessageHandler $messageHandler, BoardService $boardService)
    {

        $messageHandler->sendMessage($this->recipient,$this->message);
        Cache::forever($this->gameName, false);
        $board = $boardService->getGameBoard($this->boardKey);
        $messageHandler->displayBoard($this->recipient,$board);

    }
}
