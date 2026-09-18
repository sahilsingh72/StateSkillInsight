<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'super_admin', 'display_name' => 'Super Administrator', 'description' => 'Full access to all universities and settings.'],
            ['name' => 'university_admin', 'display_name' => 'University Administrator', 'description' => 'Manage university configuration, surveys, respondents, analytics and reports.'],
            ['name' => 'survey_administrator', 'display_name' => 'Survey Administrator', 'description' => 'Create and publish surveys, manage questions, and view responses.'],
            ['name' => 'analyst', 'display_name' => 'Research Analyst', 'description' => 'View analytics, run cross-analysis, and export reports.'],
            ['name' => 'data_operator', 'display_name' => 'Data Operator', 'description' => 'Import respondents, send invitations, and manage submission state.'],
            ['name' => 'respondent', 'display_name' => 'Survey Respondent', 'description' => 'Access assigned survey and submit responses.'],
        ];

        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r['name']], $r);
        }

        $permissions = [
            ['name' => 'manage_universities', 'display_name' => 'Manage Universities', 'module' => 'university'],
            ['name' => 'manage_surveys', 'display_name' => 'Manage Surveys & Builder', 'module' => 'survey'],
            ['name' => 'manage_respondents', 'display_name' => 'Manage Respondents', 'module' => 'respondents'],
            ['name' => 'view_analytics', 'display_name' => 'View Analytics & Dashboards', 'module' => 'analytics'],
            ['name' => 'generate_reports', 'display_name' => 'Generate & Export Reports', 'module' => 'reports'],
            ['name' => 'manage_users', 'display_name' => 'Manage System Users', 'module' => 'users'],
            ['name' => 'view_audit_logs', 'display_name' => 'View System Audit Logs', 'module' => 'system'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }
    }
}
