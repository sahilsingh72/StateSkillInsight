<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'university_id',
        'created_by',
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function canBeEditedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        // Superadmin can edit and delete any question
        if ($user->isSuperAdmin()) {
            return true;
        }

        // If the question was created by a Superadmin, non-superadmins CANNOT edit or delete it
        if ($this->creator && $this->creator->isSuperAdmin()) {
            return false;
        }

        // If question is global (no specific university_id or created_by is null/superadmin), non-superadmins CANNOT edit or delete it
        if (is_null($this->created_by) || is_null($this->university_id)) {
            return false;
        }

        // If created by someone from another university, non-superadmins CANNOT edit or delete it
        if ($this->university_id !== $user->university_id && (!$this->creator || $this->creator->university_id !== $user->university_id)) {
            return false;
        }

        return true;
    }

    public function section()
    {
        return $this->belongsTo(SurveySection::class, 'section_id');
    }

    public function university()
    {
        return $this->belongsTo(University::class, 'university_id');
    }

    public function universities()
    {
        return $this->belongsToMany(University::class, 'question_university', 'question_id', 'university_id');
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
