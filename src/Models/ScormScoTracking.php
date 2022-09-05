<?php

namespace Lightscale\ScormPlayer\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Peopleaps\Scorm\Model\ScormScoTrackingModel;

class ScormScoTracking extends ScormScoTrackingModel
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'progression',
        'score',
        'score_raw',
        'score_min',
        'score_max',
        'score_scaled',
        'lesson_status',
        'completion_status',
        'session_time',
        'total_time_int',
        'total_time_string',
        'entry',
        'suspend_data',
        'credit',
        'exit',
        'location',
        'mode',
        'is_locked',
        'details',
        'latest_date',
    ];

    public function user()
    {
        return $this->belongsTo(config('scorm.user_class'));
    }

    public function commentsFromLearner() : Attribute
    {
        return Attribute::make(
            get: fn() => []
        );
    }

    protected function commentsFromLms() : Attribute
    {
        return Attribute::make(
            get: fn() => []
        );
    }

    protected function score() : Attribute
    {
        return Attribute::make(
            get: fn() => [
                'min' => $this->score_min,
                'max' => $this->score_max,
                'raw' => $this->score_raw,
                'scaled' => $this->score_scaled,
            ],
            set: fn(array $data) => [
                'score_min' => $data['min'] ?? null,
                'score_max' => $data['max'] ?? null,
                'score_raw' => $data['raw'] ?? null,
                'score_scaled' => $data['scaled'] ?? null,
            ]
        );
    }

    protected function learnerId() : Attribute
    {
        return Attribute::make(
            get: fn() => $this->user_id
        );
    }

    protected function learnerName() : Attribute
    {
        return Attribute::make(
            get: fn() => $this->user->name
        );
    }

    public function getCMIData() : array
    {
        $data = collect([
            'score',
            'entry',
            'credit',
            'exit',
            'mode',
            'location',
            'suspend_data',
            'completion_status',
            //'comments_from_lms',
            //'comments_from_learner',
            'learner_id',
            'learner_name',
        ])->mapWithKeys(fn($k) => [$k => $this->{$k}])->all();

        return ['cmi' => $data];
    }

    public function setCMIData(array $data)
    {
        $cmi = $data['cmi'] ?? [];
        $this->fill($cmi);
        $this->save();
    }
}
