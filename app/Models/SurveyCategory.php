<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'code',
        'name',
        'description',
        'opening_message',
        'eligibility',
        'order',
        'is_active',
        'icon',
        'estimated_minutes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function sections()
    {
        return $this->hasMany(SurveySection::class, 'category_id')->orderBy('order');
    }

    public function questions()
    {
        return $this->hasManyThrough(Question::class, SurveySection::class, 'category_id', 'section_id')->orderBy('order');
    }
}
