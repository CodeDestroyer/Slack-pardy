<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MessageHandlerService extends ServiceProvider
{

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Services\Contracts\IMessageHandler', 'App\Services\Adapters\SlackMessageHandler');
    }
}
