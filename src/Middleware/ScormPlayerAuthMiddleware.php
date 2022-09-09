<?php

namespace Lightscale\ScormPlayer\Middleware;

use Peopleaps\Scorm\Model\ScormModel as Scorm;
use Lightscale\ScormPlayer\Models\{
    ScormSco,
    ScormScoTracking,
};

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Support\Facades\Cache;

class ScormPlayerAuthMiddleware
{
    private $cacheTimeout;

    public function __construct()
    {
        $this->cacheTimeout = config('scorm.cache_timeout');
    }

    public function handle(Request $request, Closure $next)
    {
        try {
            $module = $this->findModule($request);
        }
        catch(\Exception $e) {
            return $this->failedResponse();
        }

        $user = $request->user();
        $authorized = Cache::remember(
            'scorm-player-authorize-{$user->id}_{$module->id}',
            $this->cacheTimeout,
            fn() => $this->authorize($request, $module)
        );

        if($authorized) return $next($request);
        else return $this->failedResponse();
    }

    protected function failedResponse() : Response
    {
        return response('Not authorized', 503);
    }

    protected function findModule(Request $request) : Scorm
    {
        $route = $request->route();
        $parameters = $route->parameters();
        $routeName = $route->getName();

        if($routeName === 'scorm-player.serve') {
            $uuid = $route->parameter('uuid');
            return Cache::remember(
                "scrorm-player-uuid-{$uuid}-scorm",
                $this->cacheTimeout,
                fn() => Scorm::where('uuid', $uuid)->firstOrFail()
            );
        }
        else foreach($parameters as $val) {
            if(getType($val) !== 'object') continue;

            if(is_a($val, Scorm::class))
                return $val;
            elseif(is_a($val, ScormSco::class))
                return Cache::remember(
                    "scorm-player-sco-{$val->id}-scorm",
                    $this->cacheTimeout,
                    fn() => $val->scorm
                );
            elseif(is_a($val, ScormScoTracking::class))
                return Cache::remember(
                    "scorm-player-sco-tracking-{$val->id}-scorm",
                    $this->cacheTimeout,
                    fn() => $val->sco->scorm
                );
        }

        throw new \Exception('Failed to find module from route');
    }

    protected function authorize(Request $req, Scorm $module) : bool
    {
        return false;
    }
}
