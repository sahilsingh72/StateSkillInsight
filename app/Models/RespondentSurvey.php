<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RespondentSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'respondent_id',
        'survey_id',
        'category_id',
        'status',
        'current_section_id',
        'completion_percentage',
        'last_saved_at',
        'completed_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'completion_percentage' => 'float',
        'last_saved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function respondent()
    {
        return $this->belongsTo(Respondent::class);
    }

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function category()
    {
        return $this->belongsTo(SurveyCategory::class, 'category_id');
    }

    public function currentSection()
    {
        return $this->belongsTo(SurveySection::class, 'current_section_id');
    }

    public function responses()
    {
        return $this->hasMany(Response::class, 'respondent_survey_id');
    }

    public function scores()
    {
        return $this->hasMany(RespondentScore::class, 'respondent_survey_id');
    }

    public function interventions()
    {
        return $this->hasMany(RespondentIntervention::class, 'respondent_survey_id');
    }
}
