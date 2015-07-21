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

/**
 * Class HandleQuestionTiming
 * @package App\Jobs
 */
class HandleQuestionTiming extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var mixed
     */
    public $questionID;
    /**
     * @var string
     */
    public $recipient;
    /**
     * @var
     */

    public $message;
    /**
     * @var
     */
    public $gameKey;
    /**
     * @var string
     */
    public $boardKey;
    /**
     * @var string
     */
    public $userKey;
    /**
     * @var string
     */
    public $boardLeaderKey;
    /**
     * @var string
     */
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
    public function __construct(Request $request, Question $question)
    {
        $this->questionID = $question->id;
        $this->gameKey =  "game#{$request->get("channel_id")}";
        $this->userName = $request->get("user_name");
        $this->recipient = "#" . $request->get("channel_name");
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
        if (!Cache::has($this->currentQuestionKey)) {
            return;
        }
        $question = Cache::get($this->currentQuestionKey);
        //Is this a new question?
        if ($question->id != $this->questionID) {
            return;
        }
        Cache::forget($this->currentQuestionKey);
        $gameService->updateGameBoard($this->boardKey, $question->category, $question->value,
            $this->recipient, $this->gameKey, $this->boardLeaderKey, $this->userKey);
        $board = $gameService->getGameBoard($this->boardKey);
        $messageHandler->displayBoard($this->recipient, $board);
        $messageHandler->sendMessage($this->recipient, "Times up Bitch the answer was `" . $question->answer . "`");
        $gameService->pickRandomBoardLeader($this->boardLeaderKey, $this->userKey, $this->recipient);
    }
}
