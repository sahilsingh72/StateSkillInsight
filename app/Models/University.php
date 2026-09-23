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
        'programmes',
        'departments',
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
        'programmes' => 'array',
        'departments' => 'array',
    ];

    public function getAvailableProgrammesAttribute()
    {
        if (!empty($this->programmes) && is_array($this->programmes) && count($this->programmes) > 0) {
            $list = array_values(array_filter(array_map('trim', $this->programmes)));
            if (!in_array('Other', $list)) {
                $list[] = 'Other';
            }
            return $list;
        }

        return ['Other'];
    }

    public function getDepartmentsForProgramme($programmeName = null)
    {
        $customDepartments = $this->departments;

        if (!empty($customDepartments) && is_array($customDepartments)) {
            if ($programmeName && isset($customDepartments[$programmeName]) && is_array($customDepartments[$programmeName])) {
                $list = array_values(array_filter(array_map('trim', $customDepartments[$programmeName])));
                if (!in_array('Other', $list)) {
                    $list[] = 'Other';
                }
                return $list;
            }

            if ($programmeName) {
                foreach ($customDepartments as $key => $depts) {
                    if (is_array($depts) && (strcasecmp($key, $programmeName) === 0 || stripos($programmeName, $key) !== false)) {
                        $list = array_values(array_filter(array_map('trim', $depts)));
                        if (!in_array('Other', $list)) {
                            $list[] = 'Other';
                        }
                        return $list;
                    }
                }
            }
        }

        return ['Other'];
    }

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
