<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterventionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_code',
        'conditions_json',
        'recommended_intervention',
        'priority',
    ];

    protected $casts = [
        'conditions_json' => 'array',
    ];

    public function respondentInterventions()
    {
        return $this->hasMany(RespondentIntervention::class, 'intervention_rule_id');
    }
}
