<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'university_id',
        'role_id',
        'name',
        'email',
        'mobile',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function roleRelation()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function getRoleNameAttribute(): string
    {
        return $this->roleRelation->name ?? '';
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string $roleName): bool
    {
        return $this->role_name === $roleName || ($this->roles && $this->roles->contains('name', $roleName));
    }

    public function isSuperAdmin(): bool
    {
        return $this->role_name === 'super_admin';
    }

    public function isUniversityAdmin(): bool
    {
        return in_array($this->role_name, ['super_admin', 'university_admin']);
    }

    public function canManageSurveys(): bool
    {
        return in_array($this->role_name, ['super_admin', 'university_admin', 'survey_administrator']);
    }
}
