<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoiceResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'response_id',
        'file_path',
        'duration_seconds',
        'mime_type',
        'file_size',
        'thematic_tags',
        'transcript_text',
    ];

    protected $casts = [
        'thematic_tags' => 'array',
    ];

    public function response()
    {
        return $this->belongsTo(Response::class, 'response_id');
    }
}
