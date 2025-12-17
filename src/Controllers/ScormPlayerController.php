<?php

namespace Lightscale\ScormPlayer\Controllers;

use Lightscale\ScormPlayer\Models\{
    Scorm,
    ScormSco,
    ScormScoTracking,
};

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Testing\MimeType;

use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;


class ScormPlayerController extends Controller
{

    private const JS_PATH = __DIR__ . '/../../dist/js/scorm_player.js';

    public function player(Request $request, Scorm $module)
    {
        $sco = $request->query('sco');
        $sco = $module->scos()->findOrFail($sco);

        $route_data = ['sco' => $sco];
        $scorm_api_data = [
            'routes' => [
                'load' => route('scorm-player.scorm.load', $route_data),
            ],
        ];

        $js_uri = route('scorm-player.javascript', [
            'version' => substr(md5(filemtime(self::JS_PATH)), 10, 8)
        ]);

        return view('scorm-player::player', compact(
            'sco',
            'scorm_api_data',
            'js_uri'
        ));
    }

    public function serveModule(string $uuid, string $path)
    {
        $path = Storage::disk(config('scorm.disk'))->path("{$uuid}/{$path}");
        $mime = MimeType::from($path);

        try {
            return response()->file($path, [
                'content-type' => $mime,
            ]);
        }
        catch(FileNotFoundException $e) {
            abort(404);
        }
    }

    public function scormLoad(ScormSco $sco)
    {
        $user = Auth::user();

        $trackingModel = config('scorm.models.scorm_sco_tracking');

        $tracking = $trackingModel::where([
            'sco_id' => $sco->id,
            'user_id' => $user->id,
        ])->first();

        if(!$tracking) {
            $tracking = new $trackingModel([
                'uuid' => Str::uuid(),
                'progression' => 0,
                'completion_status' => 'not attempted',
            ]);
            $tracking->user()->associate($user);
            $tracking->sco_id = $sco->id;
            $tracking->save();
        }

        $commit_url = route('scorm-player.scorm.commit', [
            'tracking' => $tracking->id,
        ]);

        $scorm_entry = route('scorm-player.serve', [
            'uuid' => $sco->scorm->uuid,
            'path' => $sco->entry_url,
        ]);

        return [
            'tracking_id' => $tracking->id,
            'tracking' => $tracking->getCMIData(),
            'entry_url' => $scorm_entry,
            'commit_url' => $commit_url,
        ];
    }

    public function scormCommit(Request $request, ScormScoTracking $tracking)
    {
        $trackingModel = config('scorm.models.scorm_sco_tracking');
        $tracking = $trackingModel::findOrFail($tracking->id);

        $data = $request->all();

        $tracking->setCMIData($data);

        return [
            'result' => true,
        ];
    }

    public function jsSource(Request $request)
    {
        return response()->file(self::JS_PATH, [
            'Content-Type' => 'application/javascript',
        ]);
    }

}
