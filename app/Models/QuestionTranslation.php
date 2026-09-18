<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionTranslation extends Model
{
    protected $fillable = [
        'question_id',
        'locale',
        'question_text',
        'help_text',
        'options_json',
    ];

    protected $casts = [
        'options_json' => 'array',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
