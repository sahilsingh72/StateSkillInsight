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
        'storage_driver',
        'sync_error',
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

    public function getAudioUrlAttribute(): string
    {
        if (empty($this->file_path)) {
            return '';
        }

        // If currently stored in local storage
        if ($this->storage_driver === 'local') {
            $localPath = str_replace('local:', '', $this->file_path);
            return \Illuminate\Support\Facades\Storage::disk('public')->url($localPath);
        }

        // If stored in OneDrive
        if ($this->storage_driver === 'onedrive'
            || str_starts_with($this->file_path, 'Surveygrid')
            || str_starts_with($this->file_path, 'VoiceResponses')
            || str_starts_with($this->file_path, 'OneDrive:')) {
            $cleanPath = ltrim(str_replace('OneDrive:', '', $this->file_path), '/');
            return route('admin.onedrive.preview', ['path' => $cleanPath, 'filename' => 'voice_' . $this->id]);
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->file_path);
    }
}
