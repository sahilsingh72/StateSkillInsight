<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoringRule extends Model
{
    protected $fillable = [
        'dimension_id',
        'rule_type',
        'weight',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'weight' => 'float',
    ];

    public function dimension()
    {
        return $this->belongsTo(PsychometricDimension::class, 'dimension_id');
    }
}
