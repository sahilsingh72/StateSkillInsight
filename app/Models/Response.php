<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    use HasFactory;

    protected $fillable = [
        'respondent_survey_id',
        'question_id',
        'text_value',
        'number_value',
        'date_value',
        'json_value',
        'score',
    ];

    protected $casts = [
        'json_value' => 'array',
        'number_value' => 'float',
        'score' => 'float',
        'date_value' => 'date',
    ];

    public function respondentSurvey()
    {
        return $this->belongsTo(RespondentSurvey::class, 'respondent_survey_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function selectedOptions()
    {
        return $this->belongsToMany(QuestionOption::class, 'response_options', 'response_id', 'question_option_id');
    }

    public function voiceResponse()
    {
        return $this->hasOne(VoiceResponse::class, 'response_id');
    }
}
