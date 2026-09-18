<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'name',
        'email',
        'mobile',
        'category_code',
        'programme',
        'department',
        'alumni_student_id',
        'token',
        'status',
        'sent_at',
        'opened_at',
        'expires_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }
}
