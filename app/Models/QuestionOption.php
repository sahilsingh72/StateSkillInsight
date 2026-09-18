<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'option_text',
        'value',
        'score',
        'order',
    ];

    protected $casts = [
        'score' => 'float',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
