<?php
/**
 * Created by PhpStorm.
 * User: pcunningham
 * Date: 7/15/15
 * Time: 8:47 AM
 */
//TODO take out functionality that does not have Request
namespace App\Services;

use App\Jobs\JoinGame;
use Cache;
use App\Jobs\HandleQuestionTiming;
use App\Models\Question;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\Contracts\IMessageHandler;

use Illuminate\Foundation\Bus\DispatchesJobs;

class GameMasterService
{
    use DispatchesJobs;
    protected $handler;
    protected $channel;
    protected $userName;
    protected $gameKey;
    protected $userKey;
    protected $boardKey;
    protected $boardLeaderKey;
    protected $currentQuestionKey;


    public function __construct(IMessageHandler $handler, Request $request)
    {
        $this->handler = $handler;
        $this->request = $request;
        $this->channel = "#{$request->get("channel_name")}";
        $this->userName = $request->get("user_name");
        $this->gameKey = "game#{$request->get("channel_id")}";
        $this->userKey = "game:players:#{$request->get("channel_id")}";
        $this->boardLeaderKey = "game:leader#{$request->get("channel_id")}";
        $this->currentQuestionKey = "game:currentQuestion#{$request->get("channel_id")}";

    }

    public function createNewGame()
    {

        if (Cache::has($this->gameKey)) {
            $this->handler->sendMessage($this->channel, trans('gamecommands.gamestarted'));
            return false;
        }
        $this->handler->sendMessage($this->channel, trans('gamecommands.newgame'));
        Cache::forever($this->gameKey, true);
        $this->dispatch((new JoinGame($this->request))->delay(30));
    }


    public function joinGame() {
        if (!Cache::has($this->gameKey)) {
            $this->handler->sendMessageMention($this->channel, $this->userName, trans('gamecommands.noGame'));
            return;
        }
        if (Cache::get($this->gameKey) == true) {

            $users = Cache::get($this->userKey, array());

            if (isset($users[$this->userName])) {
                $this->handler->sendMessageMention($this->channel, $this->userName, trans('gamecommands.alreadyJoined'));
            } else {
                $this->handler->sendMessageMention($this->channel, $this->userName, trans('gamecommands.joined'));

                $users[$this->userName] = new User($this->userName);
                Cache::forever($this->userKey, $users);
            }
        } else {
            $this->handler->sendMessageMention($this->channel, $this->userName, trans('gamecommands.gameStarted'));
        }
    }

    public function getGameBoard($boardKey) {

        $board = Cache::get($boardKey, array());
        if(empty($board)){
            $categories = Category::active()->random()->take(6)->get();
            foreach ($categories as $category){
                $board[$category->name] = array(
                    "id" => $category->id,
                    "name" => $category->name,
                    "available" => array(200,400,600,800,1000)
                );
            }
            Cache::forever($boardKey, $board);
        }
        return $board;
    }

    public function pickRandomBoardLeader($boardLeaderKey,$userKey)
    {

        $users = Cache::get($userKey);
        $userName = array_rand($users, 1);
        Cache::forever($boardLeaderKey, $userName);
        return $userName;
    }

    public function getUserScoreByName($userKey,$name){
        $users = Cache::get($userKey, array());
        return $users[$name]->getScore();
    }

    public function pickQuestion($category,$value)
    {
        if(Cache::has($this->currentQuestionKey)){
            $this->handler->sendMessageMention($this->channel, $this->userName, "Question is picked");
            exit;
        }
        if (Cache::has($this->boardLeaderKey)) {
            $boardLeader = Cache::get($this->boardLeaderKey);
           if($this->userName != $boardLeader){
               $this->handler->sendMessageMention($this->channel, $this->userName, "you dont have control");
           } else {
               $question = $this->displayQuestion($category,$value);
               $this->dispatch((new HandleQuestionTiming($this->request,$question))->delay(30));

               //
           }
        } else {
            $this->handler->sendMessageMention($this->channel, $this->userName, trans('gamecommands.noGame'));
        }
    }

    public function displayQuestion($category,$value){
        $question = Question::where('category', $category)->where('value', $value)->active()->random()->first();
        if(empty($question)){
            $this->handler->sendMessageMention($this->channel, $this->userName, "Question does not exist");
            exit;
        } else {
            //TODO Create Relationship
            $currentCategory = Category::where('id',$question->category)->first();
            $question->category = $currentCategory->name;
            $this->handler->sendMessage($this->channel,
                trans('gamecommands.question',
                        ['category' => $question->category,
                        'total'=>$question->value,
                        'question'=>$question->question]));


        }
        Cache::put($this->currentQuestionKey, $question,1);
        return $question;
    }

    public function answerQuestion()
    {

    }

    public function updateGameBoard($boardKey,$category,$value)
    {
        $board = Cache::get($boardKey);
        $categories = $board[$category]['available'];
        $values = array_diff($categories,[$value]);
        $board[$category]['available'] = $values;
        if (count($values) == 0) {
            unset($board[$category]);
        }

        Cache::forever($boardKey, $board);


}


}