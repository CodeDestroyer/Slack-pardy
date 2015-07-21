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
use Log;
class SlackMessageHandler implements IMessageHandler
{
    public function sendMessage($recipient, $message)
    {
        Slack::to($recipient)->send($message);
    }

    public function sendMessageMention($recipient, $user, $message)
    {
        Slack::to($recipient)->send("@{$user} - {$message}");
    }

    public function displayBoard($recipient, $categories)
    {
        $boardString = "";
        foreach($categories as $category)
        {
            $questions = implode(' | ',$category['available']);
            $boardString.= "*#{$category['id']} - {$category['name']}*: \n {$questions}\n\n";
        }
        Slack::to($recipient)->send($boardString);
    }

}