<?php

namespace App\Jobs;

use App\Events\SignupClosed;
use App\Services\BoardService;
use App\Services\Contracts\IMessageHandler;
use App\Services\GameMasterService;
use Cache;
use Event;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class JoinGame extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    //REPLACE
    const SIGNUP_CLOSED = "Signups are now closed";

    public $recipient;
    public $message;
    public $gameKey;
    public $boardKey;
    public $userKey;
    public $boardLeaderKey;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->recipient = "#".$request->get("channel_name");
        $this->message = self::SIGNUP_CLOSED;
        $this->gameKey = "game#{$request->get("channel_id")}";
        $this->boardKey = "game:board#{$request->get("channel_id")}";
        $this->userKey = "game:players:#{$request->get("channel_id")}";
        $this->boardLeaderKey = "game:leader#{$request->get("channel_id")}";
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(IMessageHandler $messageHandler, GameMasterService $gameService)
    {

        $messageHandler->sendMessage($this->recipient,$this->message);
        Cache::forever($this->gameKey, false);
        $board = $gameService->getGameBoard($this->boardKey);
        $messageHandler->displayBoard($this->recipient,$board);
        $name = $gameService->pickRandomBoardLeader($this->boardLeaderKey,$this->userKey);
        $total = $gameService->getUserScoreByName($this->userKey,$name);
        $messageHandler->sendMessage($this->recipient,
            trans('gamecommands.boardControl', ['name' => $name, 'total'=>$total]));
    }
}
