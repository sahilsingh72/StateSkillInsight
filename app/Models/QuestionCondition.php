<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionCondition extends Model
{
    protected $fillable = [
        'question_id',
        'depends_on_question_id',
        'operator',
        'value',
        'action',
        'target_section_id',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function dependsOnQuestion()
    {
        return $this->belongsTo(Question::class, 'depends_on_question_id');
    }

    public function targetSection()
    {
        return $this->belongsTo(SurveySection::class, 'target_section_id');
    }
}
