<?php

namespace App\Jobs;


use App\Models\Question;
use App\Services\Contracts\IMessageHandler;
use App\Services\GameMasterService;
use Illuminate\Http\Request;
use Cache;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleQuestionTiming extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    public $questionID;
    public $recipient;
    public $message;
    public $gameKey;
    public $boardKey;
    public $userKey;
    public $boardLeaderKey;
    public $currentQuestionKey;

    /**
     * HandleQuestionTiming constructor.
     * @param $questionID
     * @param $recipient
     * @param $message
     * @param $gameKey
     * @param $boardKey
     * @param $userKey
     * @param $boardLeaderKey
     */
    public function __construct(Request$request, Question $question)
    {
        $this->questionID = $question->id;
        $this->recipient = "#".$request->get("channel_name");
        $this->boardKey = "game:board#{$request->get("channel_id")}";
        $this->userKey = "game:players:#{$request->get("channel_id")}";
        $this->boardLeaderKey = "game:leader#{$request->get("channel_id")}";
        $this->currentQuestionKey = "game:currentQuestion#{$request->get("channel_id")}";
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(IMessageHandler $messageHandler, GameMasterService $gameService)
    {
        //No need to close question
        if(!Cache::has($this->currentQuestionKey)){
            return;
        }
        $question = Cache::get($this->currentQuestionKey);
        //Is this a new question?
        if($question->id != $this->questionID){
            return;
        }
        $messageHandler->sendMessage($this->recipient,"Times up Bitch the answer was ".$question->answer);
        Cache::forget($this->currentQuestionKey);
        $gameService->updateGameBoard($this->boardKey,$question->category,$question->value);
        $board = $gameService->getGameBoard($this->boardKey);
        $messageHandler->displayBoard($this->recipient,$board);
        $name = $gameService->pickRandomBoardLeader($this->boardLeaderKey,$this->userKey);
        $total = $gameService->getUserScoreByName($this->userKey,$name);
        $messageHandler->sendMessage($this->recipient,
            trans('gamecommands.boardControl', ['name' => $name, 'total'=>$total]));
    }
}
