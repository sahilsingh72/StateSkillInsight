<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RespondentIntervention extends Model
{
    protected $fillable = [
        'respondent_survey_id',
        'intervention_rule_id',
        'notes',
    ];

    public function respondentSurvey()
    {
        return $this->belongsTo(RespondentSurvey::class, 'respondent_survey_id');
    }

    public function rule()
    {
        return $this->belongsTo(InterventionRule::class, 'intervention_rule_id');
    }
}
