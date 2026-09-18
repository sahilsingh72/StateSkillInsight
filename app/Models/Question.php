<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'question_text',
        'help_text',
        'type',
        'is_required',
        'order',
        'dimension_id',
        'weight',
        'tags',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'tags' => 'array',
        'settings' => 'array',
        'weight' => 'float',
    ];

    public function section()
    {
        return $this->belongsTo(SurveySection::class, 'section_id');
    }

    public function dimension()
    {
        return $this->belongsTo(PsychometricDimension::class, 'dimension_id');
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class, 'question_id')->orderBy('order');
    }

    public function translations()
    {
        return $this->hasMany(QuestionTranslation::class, 'question_id');
    }

    public function conditions()
    {
        return $this->hasMany(QuestionCondition::class, 'question_id');
    }

    public function dependentConditions()
    {
        return $this->hasMany(QuestionCondition::class, 'depends_on_question_id');
    }

    public function getTranslationText(string $locale = 'en'): string
    {
        if ($locale === 'en') {
            return $this->question_text;
        }

        $translation = $this->translations->firstWhere('locale', $locale);
        return $translation ? $translation->question_text : $this->question_text;
    }
}
