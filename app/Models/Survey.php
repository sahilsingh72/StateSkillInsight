<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    use HasFactory;

    protected $fillable = [
        'university_id',
        'title',
        'subtitle',
        'description',
        'opening_message',
        'status',
        'start_date',
        'end_date',
        'language',
        'target_respondents',
        'estimated_completion_time',
        'allow_anonymous',
        'require_auth',
        'allow_resume',
        'enable_voice',
        'version',
        'settings',
    ];

    protected $casts = [
        'allow_anonymous' => 'boolean',
        'require_auth' => 'boolean',
        'allow_resume' => 'boolean',
        'enable_voice' => 'boolean',
        'settings' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function categories()
    {
        return $this->hasMany(SurveyCategory::class)->orderBy('order');
    }

    public function psychometricDimensions()
    {
        return $this->hasMany(PsychometricDimension::class);
    }

    public function compositeIndexes()
    {
        return $this->hasMany(CompositeIndex::class);
    }

    public function invitations()
    {
        return $this->hasMany(SurveyInvitation::class);
    }

    public function respondentSurveys()
    {
        return $this->hasMany(RespondentSurvey::class);
    }
}
