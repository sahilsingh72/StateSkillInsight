<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'type',
        'parent_id',
        'logo',
        'favicon',
        'tagline',
        'website',
        'email',
        'phone',
        'address',
        'state',
        'country',
        'primary_color',
        'secondary_color',
        'survey_header',
        'footer_text',
        'privacy_text',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(University::class, 'parent_id');
    }

    public function colleges()
    {
        return $this->hasMany(University::class, 'parent_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function surveys()
    {
        return $this->hasMany(Survey::class);
    }

    public function respondents()
    {
        return $this->hasMany(Respondent::class);
    }
}
