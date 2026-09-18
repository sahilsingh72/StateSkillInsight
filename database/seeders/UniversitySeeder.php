<?php

namespace Database\Seeders;

use App\Models\University;
use Illuminate\Database\Seeder;

class UniversitySeeder extends Seeder
{
    public function run(): void
    {
        $mainUni = University::firstOrCreate(
            ['short_name' => 'DSU'],
            [
                'name' => 'Demo State University',
                'type' => 'university',
                'parent_id' => null,
                'logo' => null,
                'favicon' => null,
                'tagline' => 'Excellence in Research, Innovation & Employability Intelligence',
                'website' => 'https://www.demostateuniversity.edu',
                'email' => 'contact@demostateuniversity.edu',
                'phone' => '+91 (0674) 235-0010',
                'address' => 'University Campus, Knowledge City, State - 751001',
                'state' => 'State Region',
                'country' => 'India',
                'primary_color' => '#1e40af', // Deep Royal Blue
                'secondary_color' => '#0f766e', // Deep Teal
                'survey_header' => 'University Alumni, Student & Career Research Survey',
                'footer_text' => '© 2026 Demo State University. All Rights Reserved. Institutional Research & Planning Cell.',
                'privacy_text' => 'I understand that my responses will be used for academic research, institutional planning, and curriculum improvement. Individual privacy is maintained.',
                'is_active' => true,
            ]
        );

        // Affiliated College under DSU
        University::firstOrCreate(
            ['short_name' => 'GIET'],
            [
                'name' => 'Government Institute of Engineering & Technology',
                'type' => 'affiliated_college',
                'parent_id' => $mainUni->id,
                'tagline' => 'Constituent Engineering College under DSU',
                'website' => 'https://www.giet.edu',
                'email' => 'info@giet.edu',
                'phone' => '+91 (0674) 235-0020',
                'address' => 'Technical Block, Knowledge City, State - 751002',
                'state' => 'State Region',
                'country' => 'India',
                'primary_color' => '#2563eb',
                'secondary_color' => '#0284c7',
                'is_active' => true,
            ]
        );

        // Autonomous College (Standalone)
        University::firstOrCreate(
            ['short_name' => 'SXAC'],
            [
                'name' => 'St. Xavier Autonomous College of Science & Commerce',
                'type' => 'autonomous_college',
                'parent_id' => null,
                'tagline' => 'Independent Autonomous Higher Education Institution',
                'website' => 'https://www.xavierautonomous.edu',
                'email' => 'admin@xavierautonomous.edu',
                'phone' => '+91 (0674) 235-0030',
                'address' => 'Xavier Square, Capital Region, State - 751003',
                'state' => 'State Region',
                'country' => 'India',
                'primary_color' => '#7c3aed',
                'secondary_color' => '#db2777',
                'is_active' => true,
            ]
        );
    }
}
