<?php

namespace Lightscale\ScormPlayer;

use Illuminate\Support\ServiceProvider;

class ScormPlayerServiceProvider extends ServiceProvider
{

    public function register()
    {
        \Log::debug('player regist');
    }

    public function boot()
    {
        // Load routes
        //$this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Load views
        //$this->loadViewsFrom(__DIR__.'/../resources/views', 'courier');
    }

}
