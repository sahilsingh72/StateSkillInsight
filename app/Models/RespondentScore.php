<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RespondentScore extends Model
{
    protected $fillable = [
        'respondent_survey_id',
        'dimension_id',
        'composite_index_id',
        'score',
        'max_possible',
        'interpretation_band',
        'calculated_at',
    ];

    protected $casts = [
        'score' => 'float',
        'max_possible' => 'float',
        'calculated_at' => 'datetime',
    ];

    public function respondentSurvey()
    {
        return $this->belongsTo(RespondentSurvey::class, 'respondent_survey_id');
    }

    public function dimension()
    {
        return $this->belongsTo(PsychometricDimension::class, 'dimension_id');
    }

    public function compositeIndex()
    {
        return $this->belongsTo(CompositeIndex::class, 'composite_index_id');
    }
}
