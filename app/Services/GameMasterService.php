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
use Log;
use DB;
use App\Facades\VarHelper;
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
        $this->boardKey = "game:board#{$request->get("channel_id")}";
        $this->answerKey = "game:answer:{$request->get("channel_id")}:{$request->get("user_name")}:";

    }

    public function createNewGame()
    {

        if (Cache::has($this->gameKey)) {
            $this->handler->sendMessage($this->channel, trans('gamecommands.gamestarted'));
            return false;
        }
        $this->handler->sendMessage($this->channel, trans('gamecommands.newgame'));
        Cache::forever($this->gameKey, true);
        $this->dispatch((new JoinGame($this->request))->delay(60));
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
            //Completely forgot
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

    public function pickRandomBoardLeader($boardLeaderKey = null,$userKey = null,$recipient = null)
    {
        if(is_null($userKey)){
            $userKey = $this->userKey;
        }
        if(is_null($boardLeaderKey)){
            $boardLeaderKey = $this->boardLeaderKey;
        }
        if(is_null($recipient)){
            $recipient = $this->channel;
        }
        $users = Cache::get($userKey);
        $userName = array_rand($users, 1);
        LOG::error($userName);
        Cache::forever($boardLeaderKey, $userName);
        $total = $this->getUserScoreByName($userKey,$userName);
        $this->handler->sendMessage($recipient,
            trans('gamecommands.boardControl', ['name' => $userName, 'total'=>$total]));

    }

    public function getUserScoreByName($userKey,$name){
        $users = Cache::get($userKey);
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
               $question = $this->displayQuestion($category, $value);
               if (!is_null($question)){
                   $this->dispatch((new HandleQuestionTiming($this->request, $question))->delay(50));
           }

               //
           }
        } else {
            $this->handler->sendMessageMention($this->channel, $this->userName, trans('gamecommands.noGame'));
        }
    }

    public function displayQuestion($category,$value){
        //TODO:: Check to see if question is apart of gameboard.
        $question = Question::where('category', $category)->where('value', $value)->active()->random()->first();
        if(empty($question)){
            $category = Category::find($category);
            $this->handler->sendMessageMention($this->channel, $this->userName, "Question does not exist, probaby double jep");
            $this->updateGameBoard($this->boardKey,$category->name,$value);
            $board = $this->getGameBoard($this->boardKey);
            $this->handler->displayBoard($this->channel,$board);
            return null;
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

    public function answerQuestion($answer)
    {
        //Check for question
        if(!Cache::has($this->currentQuestionKey)){
            $this->handler->sendMessageMention($this->channel, $this->userName, "No question to answer bitch");
            return;
        }
        $question = Cache::get($this->currentQuestionKey);
        if(Cache::has($this->answerKey.$question->id)){
            $this->handler->sendMessageMention($this->channel, $this->userName, "Already Answered Bitch");
            return;
        }
        $realAnswer = $question->answer;
        similar_text(strtoupper($answer),strtoupper($realAnswer),$rightTotal);
        dump($question);
        dump($rightTotal);
        if ($rightTotal >= 50.00){
            $this->handler->sendMessageMention($this->channel, $this->userName, "Right Bitch");
            $this->updateScore($question->value,$this->userName,true);
            $this->nextRound($question,$this->userName);

        }
        else {
            $this->handler->sendMessageMention($this->channel, $this->userName, "Wrong Bitch");
            $this->updateScore($question->value,$this->userName,false);
        }
        Cache::put($this->answerKey.$question->id, true,1);
    }

    private function nextRound($question,$name)
    {
        $this->updateGameBoard($this->boardKey,$question->category,$question->value);
        $board = $this->getGameBoard($this->boardKey);
        $this->handler->displayBoard($this->channel,$board);
        $total = $this->getUserScoreByName($this->userKey,$name);
        $this->handler->sendMessageMention($this->channel, $this->userName, "Still has the board $".$total);
        Cache::forget($this->currentQuestionKey);
    }

    public function updateGameBoard($boardKey,$category,$value,$recipient = null,$gameKey = null,$boardLeaderKey = null,
                                    $userKey = null)
    {
        $board = Cache::get($boardKey);
        if(isset($board[$category]))
        {
            $categories = $board[$category]['available'];
            $values = array_diff($categories,[$value]);
            $board[$category]['available'] = $values;
            if (count($values) == 0) {
                unset($board[$category]);
            }
            Cache::forever($boardKey, $board);
        }
        if(count($board) == 0){
            $this->endGame($recipient,$userKey,$gameKey,$boardKey,$boardLeaderKey);
        }


}
    public function showLeaderBoards($userKey = null,$channel = null)
    {
        $userKey = VarHelper::assignIfNotEmpty($userKey,$this->userKey);
        $channel = VarHelper::assignIfNotEmpty($channel,$this->channel);
        $users = Cache::get($userKey);
        $returnText = "";
        foreach ($users as $user){
            $returnText .= "{$user->getName()} with a total of \${$user->getScore()}\n";
        }
        $this->handler->sendMessage($channel,$returnText);

    }
    public function displayBoard(){

        $board = $this->getGameBoard($this->boardKey);
        $this->handler->displayBoard($this->channel,$board);

    }
    public function printHelp()
    {
        $text =  "`alex new game` - Starts a new game and a 60 second timer to join \n".
                        "`alex join game` - Join a game in the given channel.\n".
                        "`alex pick {catNum} {value}` - catNum is the id of the category.\n".
                        "`alex leaderboard` - show scores for the given game\n".
                        "`alex show board` - show the game board for the given game\n".
                        "`alex new leader` - will randomly pick new board leader\n".
            "`alex {answer}` - how to answer a question.. if the answer is poop.. alex poop";
        $this->handler->sendMessage($this->channel,$text);
    }
    private function updateScore($value,$player,$correct){

        $users = Cache::get($this->userKey);
        $score = $correct ? $value : $value * -1;
        $users[$player]->updateScore($score);
        Cache::forever($this->userKey, $users);

    }

    public function endGame($recipient = null,$userKey = null,$gameKey = null, $boardKey = null, $boardLeaderKey = null)
    {
        $recipient = VarHelper::assignIfNotEmpty($recipient,$this->channel);
        $gameKey = VarHelper::assignIfNotEmpty($gameKey,$this->gameKey);
        $boardKey = VarHelper::assignIfNotEmpty($boardKey,$this->boardKey);
        $boardLeaderKey = VarHelper::assignIfNotEmpty($boardLeaderKey,$this->boardLeaderKey);
        $userKey = VarHelper::assignIfNotEmpty($userKey, $this->userKey);
        $this->handler->sendMessage($recipient, "The Game is Over");
        $this->showLeaderBoards($userKey,$recipient);

        Cache::forget($boardKey);
        Cache::forget($boardLeaderKey);
        Cache::forget($userKey);
        Cache::forget($gameKey);
        exit;

    }




}