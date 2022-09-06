<?php

use Illuminate\Support\Facades\Route;

//use Illuminate\Routing\Middleware\SubstituteBindings;

use Lightscale\ScormPlayer\Http\Controllers\ScormPlayerController;

Route::name('scorm-player.')->prefix('elearning')->middleware([
    'web',
])->group(function() {

    Route::get('scorm-player-{version}.js', [ScormPlayerController::class, 'jsSource'])
         ->name('javascript');

    $group = function() {
        Route::get('/scorm/{sco}', 'scormLoad')->name('scorm.load');
        Route::post('/scorm/{tracking}', 'scormCommit')->name('scorm.commit');

        Route::get('{module:uuid}', 'player')->name('player');
        Route::get('files/{uuid}/{path}', 'serveModule')->name('serve')->where('path', '.*');
    };

    $route = Route::controller(ScormPlayerController::class);
    $middleware = config('scorm.middleware');

    if(!empty($middleware)) {
        $route->middleware($middleware);
    }

    $route->group($group);

});
