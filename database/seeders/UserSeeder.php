<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $uni = University::first();

        $superAdminRole = Role::where('name', 'super_admin')->first();
        $uniAdminRole = Role::where('name', 'university_admin')->first();
        $analystRole = Role::where('name', 'analyst')->first();

        // Super Admin (Global System Administration - Not tied to any university or college)
        User::firstOrCreate(
            ['email' => 'superadmin@system.edu'],
            [
                'university_id' => null,
                'name' => 'System Super Admin',
                'mobile' => '9876543210',
                'password' => Hash::make('password'),
                'role_id' => $superAdminRole?->id,
                'status' => 'active',
            ]
        );

        // University Admin
        User::firstOrCreate(
            ['email' => 'admin@demostateuniversity.edu'],
            [
                'university_id' => $uni->id,
                'name' => 'Dr. Institutional Admin',
                'mobile' => '9876543211',
                'password' => Hash::make('password'),
                'role_id' => $uniAdminRole?->id,
                'status' => 'active',
            ]
        );

        // Analyst
        User::firstOrCreate(
            ['email' => 'analyst@demostateuniversity.edu'],
            [
                'university_id' => $uni->id,
                'name' => 'Senior Research Analyst',
                'mobile' => '9876543212',
                'password' => Hash::make('password'),
                'role_id' => $analystRole?->id,
                'status' => 'active',
            ]
        );
    }
}
