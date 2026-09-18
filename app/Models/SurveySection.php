<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveySection extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'description',
        'order',
    ];

    public function category()
    {
        return $this->belongsTo(SurveyCategory::class, 'category_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'section_id')->orderBy('order');
    }
}
