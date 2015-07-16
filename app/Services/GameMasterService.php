<?php
/**
 * Created by PhpStorm.
 * User: pcunningham
 * Date: 7/15/15
 * Time: 8:47 AM
 */

namespace App\Services;
use App\Jobs\JoinGame;
use App\Events\NewGame;
use Event;

use Illuminate\Foundation\Bus\DispatchesJobs;
class GameMasterService
{
    use DispatchesJobs;
    public function createNewGame($request)
    {
        //create Repo
        $channel = $request->get("channel_name");
        Event::fire(new NewGame($channel));
        //Rename
        $this->dispatch((new JoinGame($channel))->delay(60));
    }


}