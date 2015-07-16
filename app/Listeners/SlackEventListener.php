<?php

namespace App\Listeners;

use App\Events\Event;
use App\Events\SignupClosed;
use App\Services\Contracts\IMessageHandler;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maknz\Slack\Facades\Slack;

class SlackEventListener
{
    protected $handler;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(IMessageHandler $handler)
    {
        $this->handler = $handler;
    }


    public function sendMessage(Event $event)
    {
        $recipient = $event->recipient;
        $message = $event->message;
        dump($recipient);
        $this->handler->sendMessage($recipient,$message);


    }

    public function subscribe($events)
    {
        $events->listen(
            'App\Events\NewGame',
            'App\Listeners\SlackEventListener@sendMessage'
        );

        $events->listen(
            'App\Events\SignupCLosed',
            'App\Listeners\SlackEventListener@sendMessage'
        );

    }
}
