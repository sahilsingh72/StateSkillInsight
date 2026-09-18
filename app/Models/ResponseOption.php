<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponseOption extends Model
{
    protected $fillable = [
        'response_id',
        'question_option_id',
    ];

    public function response()
    {
        return $this->belongsTo(Response::class, 'response_id');
    }

    public function option()
    {
        return $this->belongsTo(QuestionOption::class, 'question_option_id');
    }
}
