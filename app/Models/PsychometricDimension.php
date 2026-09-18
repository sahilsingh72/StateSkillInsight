<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PsychometricDimension extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'category_code',
        'name',
        'code',
        'description',
        'min_score',
        'max_score',
        'interpretation_bands',
    ];

    protected $casts = [
        'interpretation_bands' => 'array',
        'min_score' => 'float',
        'max_score' => 'float',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'dimension_id');
    }

    public function scoringRules()
    {
        return $this->hasMany(ScoringRule::class, 'dimension_id');
    }
}
