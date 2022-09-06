<?php

use Illuminate\Support\Facades\Route;

use Lightscale\ScormPlayer\Http\Controllers\ScormPlayerController;

Route::name('scorm-player.')->prefix(config('scorm.route_prefix'))->middleware([
    config('scorm.middleware_group', 'web'),
])->group(function() {

    Route::get('scorm-player-{version}.js', [ScormPlayerController::class, 'jsSource'])
         ->name('javascript');

    $group = function() {
        Route::prefix(config('scorm.route_prefix_scorm'))->group(function() {
            Route::get('{sco}', 'scormLoad')->name('scorm.load');
            Route::post('{tracking}', 'scormCommit')->name('scorm.commit');
        });

        Route::prefix(config('scorm.route_prefix_player'))
             ->get('{module:uuid}', 'player')
             ->name('player');

        Route::prefix(config('scorm.route_prefix_files'))
             ->get('{uuid}/{path}', 'serveModule')
             ->where('path', '.*')
             ->name('serve');
    };

    $route = Route::controller(ScormPlayerController::class);
    $middleware = config('scorm.middleware');

    if(!empty($middleware)) {
        $route->middleware($middleware);
    }

    $route->group($group);

});
