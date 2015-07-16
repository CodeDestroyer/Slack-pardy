<?php
/**
 * Created by PhpStorm.
 * User: pcunningham
 * Date: 7/15/15
 * Time: 9:41 PM
 */

namespace App\Services\Adapters;

use App\Services\Contracts\IMessageHandler;
use Maknz\Slack\Facades\Slack;

class SlackMessageHandler implements IMessageHandler
{
    public function sendMessage($recipient, $message)
    {
        Slack::to($recipient)->send($message);
    }

}