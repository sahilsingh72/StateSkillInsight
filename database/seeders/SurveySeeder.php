<?php

namespace Database\Seeders;

use App\Models\CompositeIndex;
use App\Models\InterventionRule;
use App\Models\PsychometricDimension;
use App\Models\Question;
use App\Models\QuestionCondition;
use App\Models\QuestionOption;
use App\Models\QuestionTranslation;
use App\Models\Respondent;
use App\Models\RespondentScore;
use App\Models\RespondentSurvey;
use App\Models\Response;
use App\Models\Survey;
use App\Models\SurveyCategory;
use App\Models\SurveySection;
use App\Models\University;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SurveySeeder extends Seeder
{
    public function run(): void
    {
        $university = University::first();

        // 1. Create Main Survey
        $survey = Survey::create([
            'university_id' => $university->id,
            'title' => 'National University Education–Employment Continuum Research Survey 2026',
            'subtitle' => 'Comprehensive Longitudinal Study on Graduate Competency, Employability & Institutional Alignment',
            'description' => 'This study collects empirical data across Working Alumni, Job-Seeking Alumni, Current Students, and Educationally Interrupted Learners to evaluate curriculum relevance, practical industry exposure, AI/digital readiness, and workplace transition.',
            'opening_message' => 'Welcome to the Institutional Career & Skill Research Platform. Your honest feedback contributes directly to university curriculum evolution and career support policies.',
            'status' => 'published',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->addMonths(6),
            'language' => 'en',
            'target_respondents' => 'All Students, Graduates & Alumni',
            'estimated_completion_time' => 15,
            'allow_anonymous' => true,
            'require_auth' => false,
            'allow_resume' => true,
            'enable_voice' => true,
            'version' => '1.0',
        ]);

        // 2. Create Categories
        $catData = [
            [
                'code' => 'cat_1',
                'name' => 'Working Alumni',
                'description' => 'Alumni who completed their UG/PG programme and are currently professionally engaged.',
                'opening_message' => 'As a working professional, your insights on how academic knowledge translates into workplace practice are invaluable.',
                'eligibility' => 'Completed UG/PG degree and currently employed, freelancing, or running an enterprise.',
                'order' => 1,
                'icon' => 'bi-briefcase',
                'estimated_minutes' => 15,
            ],
            [
                'code' => 'cat_2',
                'name' => 'Job-Seeking Alumni',
                'description' => 'Alumni who completed their UG/PG programme but are currently seeking suitable employment.',
                'opening_message' => 'We want to understand the real recruitment barriers graduates face to provide targeted skill bridges and employer connect.',
                'eligibility' => 'Graduated from UG/PG programme and actively searching for employment or career transition.',
                'order' => 2,
                'icon' => 'bi-person-vcard',
                'estimated_minutes' => 15,
            ],
            [
                'code' => 'cat_3',
                'name' => 'Current Students',
                'description' => 'Current UG/PG students preparing for upcoming employment and professional careers.',
                'opening_message' => 'Help us assess pre-graduation career readiness, internship exposure, and emerging skill preparation.',
                'eligibility' => 'Currently enrolled in 1st, 2nd, 3rd, 4th year or PG studies.',
                'order' => 3,
                'icon' => 'bi-mortarboard',
                'estimated_minutes' => 15,
            ],
            [
                'code' => 'cat_4',
                'name' => 'Educationally Interrupted Students',
                'description' => 'Students who discontinued from UG/PG programmes seeking re-entry, skill mapping, or livelihoods.',
                'opening_message' => 'This supportive, non-judgmental study seeks to reconnect interrupted learners with education completion or skill pathways.',
                'eligibility' => 'Previously enrolled in UG/PG but discontinued studies prior to completion.',
                'order' => 4,
                'icon' => 'bi-arrow-counterclockwise',
                'estimated_minutes' => 15,
            ],
        ];

        $categories = [];
        foreach ($catData as $c) {
            $c['survey_id'] = $survey->id;
            $categories[$c['code']] = SurveyCategory::create($c);
        }

        // 3. Create Psychometric Dimensions
        $dimensions = [
            // Category 1
            'dim_cat1_tech' => PsychometricDimension::create([
                'survey_id' => $survey->id,
                'category_code' => 'cat_1',
                'name' => 'Technical Capital Index',
                'code' => 'dim_cat1_tech',
                'description' => 'Perceived strength of foundational technical knowledge acquired during degree studies.',
            ]),
            'dim_cat1_adapt' => PsychometricDimension::create([
                'survey_id' => $survey->id,
                'category_code' => 'cat_1',
                'name' => 'Technological Adaptability Index',
                'code' => 'dim_cat1_adapt',
                'description' => 'Ability to adopt modern tools, AI, and industry frameworks in daily professional practice.',
            ]),
            'dim_cat1_lead' => PsychometricDimension::create([
                'survey_id' => $survey->id,
                'category_code' => 'cat_1',
                'name' => 'Leadership & Management Orientation',
                'code' => 'dim_cat1_lead',
                'description' => 'Project management, team coordination, and executive decision-making capabilities.',
            ]),

            // Category 2
            'dim_cat2_conf' => PsychometricDimension::create([
                'survey_id' => $survey->id,
                'category_code' => 'cat_2',
                'name' => 'Career Transition Confidence',
                'code' => 'dim_cat2_conf',
                'description' => 'Self-perceived confidence in interview situations, technical assessments, and salary negotiation.',
            ]),
            'dim_cat2_resil' => PsychometricDimension::create([
                'survey_id' => $survey->id,
                'category_code' => 'cat_2',
                'name' => 'Job-Search Resilience Index',
                'code' => 'dim_cat2_resil',
                'description' => 'Persistence in continuous learning and job application despite recruitment rejection.',
            ]),
            'dim_cat2_skill' => PsychometricDimension::create([
                'survey_id' => $survey->id,
                'category_code' => 'cat_2',
                'name' => 'Self-directed Employability Index',
                'code' => 'dim_cat2_skill',
                'description' => 'Proactive upskilling through online certifications and portfolio project creation.',
            ]),

            // Category 3
            'dim_cat3_read' => PsychometricDimension::create([
                'survey_id' => $survey->id,
                'category_code' => 'cat_3',
                'name' => 'Graduate Readiness Index (GRI)',
                'code' => 'dim_cat3_read',
                'description' => 'Composite assessment of student technical, practical, digital, and communication readiness.',
            ]),
            'dim_cat3_ai' => PsychometricDimension::create([
                'survey_id' => $survey->id,
                'category_code' => 'cat_3',
                'name' => 'Digital & AI Adaptability Index',
                'code' => 'dim_cat3_ai',
                'description' => 'Familiarity with generative AI tools, prompt engineering, and modern digital tools.',
            ]),
            'dim_cat3_prac' => PsychometricDimension::create([
                'survey_id' => $survey->id,
                'category_code' => 'cat_3',
                'name' => 'Practical Competence Index',
                'code' => 'dim_cat3_prac',
                'description' => 'Hands-on lab, project work, and internship experience quality.',
            ]),

            // Category 4
            'dim_cat4_reentry' => PsychometricDimension::create([
                'survey_id' => $survey->id,
                'category_code' => 'cat_4',
                'name' => 'Education Re-engagement Readiness',
                'code' => 'dim_cat4_reentry',
                'description' => 'Willingness to re-enroll in credit completion, flexible diplomas, or skill certifications.',
            ]),
            'dim_cat4_work' => PsychometricDimension::create([
                'survey_id' => $survey->id,
                'category_code' => 'cat_4',
                'name' => 'Existing Capability Index',
                'code' => 'dim_cat4_work',
                'description' => 'Retained technical skills and current practical work knowledge.',
            ]),
        ];

        // 4. Create Composite Index
        CompositeIndex::create([
            'survey_id' => $survey->id,
            'name' => 'Graduate Readiness Index (GRI)',
            'code' => 'GRI',
            'description' => 'Weighted composite score across technical foundation, practical exposure, AI readiness, and professional confidence.',
            'weights_json' => [
                'Technical & Academic Foundation' => 25,
                'Practical/Industry Exposure' => 20,
                'Digital & AI Readiness' => 15,
                'Professional Competence' => 15,
                'Career Clarity' => 10,
                'Learning Agility' => 10,
                'Industry Awareness' => 5,
            ],
        ]);

        // 5. Seed Sections and 50 Questions per Category (Total 200 Questions)
        $this->seedCategoryQuestions($categories['cat_1'], 'Working Alumni', $dimensions);
        $this->seedCategoryQuestions($categories['cat_2'], 'Job-Seeking Alumni', $dimensions);
        $this->seedCategoryQuestions($categories['cat_3'], 'Current Students', $dimensions);
        $this->seedCategoryQuestions($categories['cat_4'], 'Educationally Interrupted Students', $dimensions);

        // 6. Seed Intervention Rules
        $this->seedInterventionRules();

        // 7. Seed Demo Responses across all 4 categories
        $this->seedDemoRespondentsAndResponses($university, $survey, $categories);
    }

    private function seedCategoryQuestions(SurveyCategory $category, string $catName, array $dimensions)
    {
        $code = $category->code;

        // Create 5 sections per category
        $sections = [
            'sec_a' => SurveySection::create([
                'category_id' => $category->id,
                'title' => 'Section A: Profile & Academic Journey',
                'description' => "Demographics, background details, and academic foundation for {$catName}.",
                'order' => 1,
            ]),
            'sec_b' => SurveySection::create([
                'category_id' => $category->id,
                'title' => 'Section B: Competency & Capability Assessment',
                'description' => 'Core technical knowledge, practical capabilities, and curriculum alignment.',
                'order' => 2,
            ]),
            'sec_c' => SurveySection::create([
                'category_id' => $category->id,
                'title' => 'Section C: Technology, AI & Industry Readiness',
                'description' => 'Digital readiness, emerging tools, AI adoption, and industry exposure.',
                'order' => 3,
            ]),
            'sec_d' => SurveySection::create([
                'category_id' => $category->id,
                'title' => 'Section D: Psychometric & Professional Mindset',
                'description' => 'Self-perception rating scale, resilience, agility, and transition confidence.',
                'order' => 4,
            ]),
            'sec_e' => SurveySection::create([
                'category_id' => $category->id,
                'title' => 'Section E: Professional Voice & Recommendations',
                'description' => 'Open-ended feedback, text narrative, and voice-recorded suggestions.',
                'order' => 5,
            ]),
        ];

        // Seed 10 Profile MCQs (Section A)
        for ($i = 1; $i <= 10; $i++) {
            $dimKey = match($code) {
                'cat_1' => 'dim_cat1_tech',
                'cat_2' => 'dim_cat2_conf',
                'cat_3' => 'dim_cat3_read',
                'cat_4' => 'dim_cat4_work',
            };

            if ($i == 1) {
                $q = Question::create([
                    'section_id' => $sections['sec_a']->id,
                    'question_text' => "What is your current academic/employment status in {$catName}?",
                    'help_text' => 'Select the option that best describes your situation.',
                    'type' => 'single_choice',
                    'is_required' => true,
                    'order' => $i,
                    'dimension_id' => $dimensions[$dimKey]->id,
                    'tags' => ['profile', 'demographic'],
                ]);
                $options = [
                    ['option_text' => 'Employed Full-time in Core Discipline', 'value' => 'fulltime_core', 'score' => 100],
                    ['option_text' => 'Employed in Non-Core Discipline', 'value' => 'fulltime_noncore', 'score' => 75],
                    ['option_text' => 'Self-Employed / Entrepreneur', 'value' => 'entrepreneur', 'score' => 90],
                    ['option_text' => 'Actively Seeking Employment', 'value' => 'seeking', 'score' => 50],
                    ['option_text' => 'Pursuing Higher Studies / Certifications', 'value' => 'higher_studies', 'score' => 80],
                ];
                foreach ($options as $optIdx => $opt) {
                    $opt['question_id'] = $q->id;
                    $opt['order'] = $optIdx + 1;
                    QuestionOption::create($opt);
                }
            } elseif ($i == 2) {
                $q = Question::create([
                    'section_id' => $sections['sec_a']->id,
                    'question_text' => 'Which academic discipline/programme were you enrolled in?',
                    'type' => 'dropdown',
                    'is_required' => true,
                    'order' => $i,
                    'tags' => ['discipline', 'academic'],
                ]);
                $options = ['Computer Science & Engineering', 'Electrical & Electronics', 'Mechanical Engineering', 'Civil Engineering', 'Business Administration (MBA/BBA)', 'Basic & Applied Sciences', 'Humanities & Social Work'];
                foreach ($options as $optIdx => $optText) {
                    QuestionOption::create([
                        'question_id' => $q->id,
                        'option_text' => $optText,
                        'value' => Str::slug($optText),
                        'score' => 80,
                        'order' => $optIdx + 1,
                    ]);
                }
            } else {
                $q = Question::create([
                    'section_id' => $sections['sec_a']->id,
                    'question_text' => "{$catName} Profile Question {$i}: How would you rate your primary academic foundation quality?",
                    'type' => 'single_choice',
                    'is_required' => true,
                    'order' => $i,
                    'tags' => ['academic', 'profile'],
                ]);
                $opts = ['Excellent', 'Above Average', 'Average', 'Needs Improvement'];
                foreach ($opts as $optIdx => $o) {
                    QuestionOption::create([
                        'question_id' => $q->id,
                        'option_text' => $o,
                        'value' => Str::slug($o),
                        'score' => (4 - $optIdx) * 25,
                        'order' => $optIdx + 1,
                    ]);
                }
            }
        }

        // Seed 10 Competency MCQs (Section B)
        for ($i = 1; $i <= 10; $i++) {
            $q = Question::create([
                'section_id' => $sections['sec_b']->id,
                'question_text' => "{$catName} Competency Question {$i}: Rate the alignment of university lab practicals with real industry problems.",
                'type' => ($i % 2 == 0) ? 'likert' : 'rating',
                'is_required' => true,
                'order' => $i,
                'dimension_id' => $dimensions[match($code) {
                    'cat_1' => 'dim_cat1_tech',
                    'cat_2' => 'dim_cat2_skill',
                    'cat_3' => 'dim_cat3_prac',
                    'cat_4' => 'dim_cat4_work',
                }]->id,
                'tags' => ['competency', 'practical'],
            ]);
            $opts = ['Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree'];
            foreach ($opts as $optIdx => $o) {
                QuestionOption::create([
                    'question_id' => $q->id,
                    'option_text' => $o,
                    'value' => (string)($optIdx + 1),
                    'score' => ($optIdx + 1) * 20,
                    'order' => $optIdx + 1,
                ]);
            }
        }

        // Seed 10 Technology/AI MCQs (Section C)
        for ($i = 1; $i <= 10; $i++) {
            if ($i == 1) {
                $q = Question::create([
                    'section_id' => $sections['sec_c']->id,
                    'question_text' => "Which modern AI and digital tools do you actively use for problem solving?",
                    'type' => 'multiple_choice',
                    'is_required' => false,
                    'order' => $i,
                    'tags' => ['ai', 'digital', 'technology'],
                ]);
                $opts = ['Generative AI (ChatGPT/Claude/Copilot)', 'Data Analytics Tools (Python/R/Tableau)', 'Cloud Platforms (AWS/Azure/GCP)', 'Version Control (Git/GitHub)', 'Project Management (Jira/Trello)'];
                foreach ($opts as $optIdx => $o) {
                    QuestionOption::create([
                        'question_id' => $q->id,
                        'option_text' => $o,
                        'value' => Str::slug($o),
                        'score' => 20,
                        'order' => $optIdx + 1,
                    ]);
                }
            } else {
                $q = Question::create([
                    'section_id' => $sections['sec_c']->id,
                    'question_text' => "{$catName} Tech Question {$i}: Rate your proficiency level in applying emerging tools to domain challenges.",
                    'type' => 'rating',
                    'is_required' => true,
                    'order' => $i,
                    'dimension_id' => $dimensions[match($code) {
                        'cat_1' => 'dim_cat1_adapt',
                        'cat_2' => 'dim_cat2_skill',
                        'cat_3' => 'dim_cat3_ai',
                        'cat_4' => 'dim_cat4_work',
                    }]->id,
                    'tags' => ['technology', 'ai'],
                ]);
                for ($r = 1; $r <= 5; $r++) {
                    QuestionOption::create([
                        'question_id' => $q->id,
                        'option_text' => "Level {$r}",
                        'value' => (string)$r,
                        'score' => $r * 20,
                        'order' => $r,
                    ]);
                }
            }
        }

        // Seed 10 Psychometric Questions (Section D)
        for ($i = 1; $i <= 10; $i++) {
            $q = Question::create([
                'section_id' => $sections['sec_d']->id,
                'question_text' => "{$catName} Psychometric Statement {$i}: 'I quickly adapt to technological disruptions and unexpected professional challenges.'",
                'type' => 'likert',
                'is_required' => true,
                'order' => $i,
                'dimension_id' => $dimensions[match($code) {
                    'cat_1' => 'dim_cat1_lead',
                    'cat_2' => 'dim_cat2_resil',
                    'cat_3' => 'dim_cat3_read',
                    'cat_4' => 'dim_cat4_reentry',
                }]->id,
                'tags' => ['psychometric', 'resilience'],
            ]);
            $opts = [
                '1 - Strongly Disagree',
                '2 - Disagree',
                '3 - Neutral',
                '4 - Agree',
                '5 - Strongly Agree',
            ];
            foreach ($opts as $optIdx => $o) {
                QuestionOption::create([
                    'question_id' => $q->id,
                    'option_text' => $o,
                    'value' => (string)($optIdx + 1),
                    'score' => ($optIdx + 1) * 20,
                    'order' => $optIdx + 1,
                ]);
            }
        }

        // Seed 10 Open-ended / Voice Questions (Section E)
        for ($i = 1; $i <= 10; $i++) {
            $type = ($i <= 5) ? 'voice' : (($i <= 8) ? 'long_text' : 'short_text');
            $q = Question::create([
                'section_id' => $sections['sec_e']->id,
                'question_text' => ($type === 'voice')
                    ? "{$catName} Voice Feedback {$i}: Please record a short audio message (up to 2 minutes) sharing your key recommendation for curriculum reform."
                    : "{$catName} Open Text Question {$i}: Describe the specific technical or practical skill bridge that would have prepared you better.",
                'type' => $type,
                'is_required' => false,
                'order' => $i,
                'settings' => ($type === 'voice') ? ['max_recording_seconds' => 120] : null,
                'tags' => ['feedback', 'open_ended', 'voice'],
            ]);

            // Add translation for the first voice question
            if ($i == 1 && $type === 'voice') {
                QuestionTranslation::create([
                    'question_id' => $q->id,
                    'locale' => 'hi',
                    'question_text' => 'पाठ्यक्रम सुधार के लिए अपना मुख्य सुझाव साझा करने के लिए कृपया एक संक्षिप्त ऑडियो संदेश (2 मिनट तक) रिकॉर्ड करें।',
                ]);
                QuestionTranslation::create([
                    'question_id' => $q->id,
                    'locale' => 'or',
                    'question_text' => 'ପାଠ୍ୟକ୍ରମ ସଂସ୍କାର ପାଇଁ ଆପଣଙ୍କର ପ୍ରମୁଖ ସୁପାରିଶ ସେୟାର କରିବା ପାଇଁ ଦୟାକରି ଏକ କ୍ଷୁଦ୍ର ଅଡିଓ ବାର୍ତ୍ତା ରେକର୍ଡ କରନ୍ତୁ |',
                ]);
            }
        }
    }

    private function seedInterventionRules()
    {
        $rules = [
            [
                'name' => 'Technical + Practical Skill Gap',
                'category_code' => 'cat_2',
                'conditions_json' => ['technical_score' => ['<' => 60], 'practical_score' => ['<' => 60]],
                'recommended_intervention' => 'Domain-specific 8-week technical bootcamps with industry live project attachment.',
                'priority' => 'high',
            ],
            [
                'name' => 'Interview Conversion Gap',
                'category_code' => 'cat_2',
                'conditions_json' => ['communication_score' => ['<' => 60], 'technical_score' => ['>=' => 75]],
                'recommended_intervention' => 'Executive mock interview drills, soft skill refinement, and corporate resume storytelling workshops.',
                'priority' => 'medium',
            ],
            [
                'name' => 'Emerging AI & Technology Gap',
                'category_code' => 'cat_3',
                'conditions_json' => ['ai_score' => ['<' => 50]],
                'recommended_intervention' => 'Mandatory hands-on Generative AI and Data Analytics micro-credit module insertion.',
                'priority' => 'high',
            ],
            [
                'name' => 'High Potential Interrupted Re-entry',
                'category_code' => 'cat_4',
                'conditions_json' => ['reentry_score' => ['>=' => 70]],
                'recommended_intervention' => 'Priority fast-track credit transfer and flexible distance degree completion counselling.',
                'priority' => 'high',
            ],
        ];

        foreach ($rules as $r) {
            InterventionRule::create($r);
        }
    }

    private function seedDemoRespondentsAndResponses(University $uni, Survey $survey, array $categories)
    {
        $programmes = ['Computer Science & Engineering', 'Electrical Engineering', 'Mechanical Engineering', 'Civil Engineering', 'MBA'];
        $departments = ['Department of Computer Science', 'Department of Electrical Science', 'Department of Mechanical Science', 'Department of Civil Engineering', 'School of Management'];
        $cities = ['Bhubaneswar', 'Cuttack', 'Rourkela', 'Sambalpur', 'Berhampur', 'Delhi', 'Bengaluru', 'Hyderabad', 'Pune'];

        // Seed 40 Demo Respondents (10 per category)
        foreach (['cat_1', 'cat_2', 'cat_3', 'cat_4'] as $catIdx => $catCode) {
            $cat = $categories[$catCode];
            $sections = $cat->sections;
            $firstSection = $sections->first();
            $questions = $cat->questions;

            for ($r = 1; $r <= 10; $r++) {
                $respName = match($catCode) {
                    'cat_1' => "Working Alumni {$r} (Rajesh Kumar)",
                    'cat_2' => "Job Seeker {$r} (Priya Sharma)",
                    'cat_3' => "Current Student {$r} (Ansuman Das)",
                    'cat_4' => "Interrupted Student {$r} (Rakesh Mohanty)",
                };

                $respondent = Respondent::create([
                    'university_id' => $uni->id,
                    'token' => Str::random(32),
                    'name' => $respName,
                    'email' => "respondent_{$catCode}_{$r}@example.com",
                    'mobile' => '98' . rand(10000000, 99999999),
                    'gender' => ($r % 2 == 0) ? 'Male' : 'Female',
                    'date_of_birth' => now()->subYears(20 + $r)->format('Y-m-d'),
                    'university_student_alumni_id' => "DSU202" . rand(10, 99) . "0" . $r,
                    'programme' => $programmes[$r % count($programmes)],
                    'department' => $departments[$r % count($departments)],
                    'graduation_year' => (string)(2020 + ($r % 5)),
                    'admission_year' => (string)(2016 + ($r % 5)),
                    'category_code' => $catCode,
                    'current_city' => $cities[$r % count($cities)],
                    'state' => 'State Region',
                    'country' => 'India',
                    'employment_status' => ($catCode === 'cat_1') ? 'Employed Full-time' : (($catCode === 'cat_2') ? 'Actively Seeking' : (($catCode === 'cat_3') ? 'Currently Studying' : 'Discontinued')),
                    'consent_given' => true,
                    'consent_at' => now()->subDays(rand(1, 30)),
                ]);

                $respSurvey = RespondentSurvey::create([
                    'respondent_id' => $respondent->id,
                    'survey_id' => $survey->id,
                    'category_id' => $cat->id,
                    'status' => 'completed',
                    'current_section_id' => $firstSection->id,
                    'completion_percentage' => 100.00,
                    'last_saved_at' => now()->subHours(rand(1, 48)),
                    'completed_at' => now()->subHours(rand(1, 48)),
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Mozilla/5.0 Chrome/120.0',
                ]);

                // Create responses for questions
                $totalScore = 0;
                $qCount = 0;

                foreach ($questions as $q) {
                    $qCount++;
                    if ($q->type === 'single_choice' || $q->type === 'dropdown' || $q->type === 'likert' || $q->type === 'rating') {
                        $opt = $q->options->random() ?? $q->options->first();
                        if ($opt) {
                            $res = Response::create([
                                'respondent_survey_id' => $respSurvey->id,
                                'question_id' => $q->id,
                                'text_value' => $opt->option_text,
                                'score' => $opt->score,
                            ]);
                            $totalScore += $opt->score;
                        }
                    } elseif ($q->type === 'multiple_choice') {
                        $opts = $q->options->take(2);
                        $res = Response::create([
                            'respondent_survey_id' => $respSurvey->id,
                            'question_id' => $q->id,
                            'json_value' => $opts->pluck('option_text')->toArray(),
                            'score' => 60,
                        ]);
                    } elseif ($q->type === 'voice') {
                        $res = Response::create([
                            'respondent_survey_id' => $respSurvey->id,
                            'question_id' => $q->id,
                            'text_value' => '[Voice Audio Recorded]',
                            'score' => 80,
                        ]);
                    } else {
                        $res = Response::create([
                            'respondent_survey_id' => $respSurvey->id,
                            'question_id' => $q->id,
                            'text_value' => "Sample response from {$respName} for research assessment.",
                            'score' => 75,
                        ]);
                    }
                }

                // Create Respondent Score
                $avgScore = ($qCount > 0) ? ($totalScore / ($qCount * 20)) * 100 : 70;
                $avgScore = min(100, max(40, round($avgScore, 2)));

                RespondentScore::create([
                    'respondent_survey_id' => $respSurvey->id,
                    'score' => $avgScore,
                    'max_possible' => 100.00,
                    'interpretation_band' => ($avgScore >= 80) ? 'High Readiness' : (($avgScore >= 60) ? 'Moderate Readiness' : 'Intervention Needed'),
                    'calculated_at' => now(),
                ]);
            }
        }
    }
}
