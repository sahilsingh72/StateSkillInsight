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
        'max_respondents',
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
        'max_respondents' => 'integer',
    ];

    public function hasStarted(): bool
    {
        return empty($this->start_date) || now()->startOfDay()->gte($this->start_date->startOfDay());
    }

    public function isExpired(): bool
    {
        return !empty($this->end_date) && now()->startOfDay()->gt($this->end_date->endOfDay());
    }

    public function isQuotaFull(): bool
    {
        if (empty($this->max_respondents)) {
            return false;
        }
        return $this->respondentSurveys()->count() >= $this->max_respondents;
    }

    public function isAcceptingResponses(): bool
    {
        return $this->status === 'published' && $this->hasStarted() && !$this->isExpired() && !$this->isQuotaFull();
    }

    public function getClosedReason(): ?string
    {
        if ($this->status !== 'published') {
            return "This survey is currently " . strtolower($this->status) . " and not accepting responses.";
        }
        if (!$this->hasStarted()) {
            return "This survey campaign has not started yet. It will open on " . $this->start_date->format('d M Y') . ".";
        }
        if ($this->isExpired()) {
            return "This survey campaign expired on " . $this->end_date->format('d M Y') . " and is now closed.";
        }
        if ($this->isQuotaFull()) {
            return "This survey has reached its maximum respondent limit (" . number_format($this->max_respondents) . " respondents) and is no longer accepting new submissions.";
        }
        return null;
    }

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function universities()
    {
        return $this->belongsToMany(University::class, 'survey_university');
    }

    public function categories()
    {
        return $this->belongsToMany(SurveyCategory::class, 'survey_category_survey', 'survey_id', 'survey_category_id')->orderBy('order');
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
