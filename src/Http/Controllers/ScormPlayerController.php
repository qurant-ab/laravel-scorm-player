<?php

namespace Lightscale\ScormPlayer\Http\Controllers;

use Lightscale\ScormPlayer\Models\{
    Scorm,
    ScormSco,
    ScormScoTracking,
};

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Testing\MimeType;

use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;


class ScormPlayerController extends Controller
{

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

        return view('scorm-player::player', compact(
            'sco',
            'scorm_api_data'
        ));
    }

    public function serveModule(string $uuid, string $path)
    {
        $path = Storage::disk('scorm-local')->path("{$uuid}/{$path}");
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

        $tracking = ScormScoTracking::where([
            'sco_id' => $sco->id,
            'user_id' => $user->id,
        ])->first();

        if(!$tracking) {
            $tracking = new ScormScoTracking([
                'uuid' => Str::uuid(),
                'progression' => 0,
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
        $data = $request->all();

        $tracking->setCMIData($data);

        return [
            'result' => true,
        ];
    }

}
