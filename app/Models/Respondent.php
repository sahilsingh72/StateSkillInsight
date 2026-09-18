<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Respondent extends Model
{
    use HasFactory;

    protected $fillable = [
        'university_id',
        'user_id',
        'token',
        'name',
        'email',
        'mobile',
        'gender',
        'date_of_birth',
        'university_student_alumni_id',
        'programme',
        'department',
        'graduation_year',
        'admission_year',
        'category_code',
        'current_city',
        'state',
        'country',
        'employment_status',
        'consent_given',
        'consent_at',
    ];

    protected $casts = [
        'consent_given' => 'boolean',
        'consent_at' => 'datetime',
        'date_of_birth' => 'date',
    ];

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function respondentSurveys()
    {
        return $this->hasMany(RespondentSurvey::class);
    }
}
