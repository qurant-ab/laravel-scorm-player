<?php

namespace Lightscale\ScormPlayer;

use Peopleaps\Scorm\ScormServiceProvider;

class ScormPlayerServiceProvider extends ScormServiceProvider
{

    public function register()
    {
        parent::register();

        $this->mergeConfigFrom(
            __DIR__  . '/../config/scorm-player.php', 'scorm'
        );
    }

    protected function offerPublishing()
    {
        parent::offerPublishing();

       $this->publishes([
            __DIR__ . '/../database/migrations/scorm_player_improvements.php.stub' =>
                $this->getMigrationFileName('scorm_player_improvements.php'),
        ], 'migrations');

    }

    public function boot()
    {
        parent::boot();

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'scorm-player');
    }

}
