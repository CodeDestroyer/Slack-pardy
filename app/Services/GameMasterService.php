<?php
/**
 * Created by PhpStorm.
 * User: pcunningham
 * Date: 7/15/15
 * Time: 8:47 AM
 */

namespace App\Services;

use App\Jobs\JoinGame;
use Cache;
use App\Events\NewGame;
use Event;
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

    public function __construct(IMessageHandler $handler, Request $request)
    {
        $this->handler = $handler;
        $this->request = $request;
        $this->channel = "#{$request->get("channel_name")}";
        $this->userName = $request->get("user_name");
        $this->gameKey = "game#{$request->get("channel_id")}";
        $this->userKey = "game:players:#{$request->get("channel_id")}";

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

}