<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompositeIndex extends Model
{
    use HasFactory;

    protected $table = 'composite_indexes';

    protected $fillable = [
        'survey_id',
        'name',
        'code',
        'description',
        'weights_json',
    ];

    protected $casts = [
        'weights_json' => 'array',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }
}
