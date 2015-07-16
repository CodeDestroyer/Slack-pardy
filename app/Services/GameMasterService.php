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
use App\Services\Contracts\IMessageHandler;

use Illuminate\Foundation\Bus\DispatchesJobs;
class GameMasterService
{
    use DispatchesJobs;
    protected $handler;
    public function __construct(IMessageHandler $handler){
        $this->handler = $handler;
    }
    public function createNewGame($request)
    {
        //create Repo
        $channel = "#{$request->get("channel_name")}";
        $gameName = "game#{$request->get("channel_id")}";
        if (Cache::has($gameName))
        {
         $this->handler->sendMessage($channel,trans('gamecommands.gamestarted'));
         return false;
        }
        $this->handler->sendMessage($channel,trans('gamecommands.newgame'));
        Cache::forever($gameName, true);
        $this->dispatch((new JoinGame($request))->delay(60));
    }


    public function joinGame($request)
    {
        $userKey = "player#{$request->get("channel_id")}:{$request->get('user_id')}";
        $gameName = "game#{$request->get("channel_id")}";
        $channel = "#{$request->get("channel_name")}";
        $userName = $request->get("user_name");
        if(!Cache::has($gameName)){
            $this->handler->sendMessageMention($channel,$userName,trans('gamecommands.noGame'));
            return;
        }
        if (Cache::get($gameName) == true){
            if (Cache::has($userKey))
            {
                $this->handler->sendMessageMention($channel,$userName,trans('gamecommands.alreadyJoined'));
            }
            else
            {
                $this->handler->sendMessageMention($channel,$userName,trans('gamecommands.joined'));
                Cache::forever($userKey,0);
            }
        }
        else {
            $this->handler->sendMessageMention($channel,$userName,trans('gamecommands.gameStarted'));
        }
    }


}