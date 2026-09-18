# StateSkillInsight — University Alumni, Student & Career Research Platform

A production-ready, university-agnostic **University Alumni, Student & Career Research & Survey Management Web Application** built with **Laravel 12, PHP, Blade, Bootstrap 5, MySQL/SQLite, HTML5 Web Audio API, and Chart.js**.

---

## Key Features

1. **University-Agnostic & Fully Configurable**:
   - Dynamic university branding (Name, Short Name, Logo, Primary/Secondary Accent Colors, Tagline, Address, Survey Header, Footer Copyright, Privacy Statement).
   - Seeded with generic **Demo State University** (no hardcoded institution names).

2. **The Four-Category Research Model (Education–Employment Continuum)**:
   - **Category 1**: Working Alumni (Professional Practice Evidence)
   - **Category 2**: Job-Seeking Alumni (Recruitment Difficulties & Employment Transition)
   - **Category 3**: Current Students (Pre-Graduation Readiness Map)
   - **Category 4**: Educationally Interrupted Students (Supportive Re-engagement & Skill Mapping)

3. **200+ Database-Driven Questions**:
   - 50 questions per category across Profile, Technical, Competency, Psychometric, and Open-Ended/Voice feedback.
   - Multilingual translations (English, Hindi, Odia).
   - Question types: Single Choice, Multiple Choice, Dropdown, Rating (1-5), Likert Scale (1-5), Short Text, Long Text, Voice Answer, Number, Date, Yes/No.

4. **Browser Web Audio API Voice Recording**:
   - Native browser microphone recording (`MediaRecorder` API) with audio preview and background AJAX upload.

5. **Scoring, Psychometric & Composite Index Engine**:
   - Psychometric Dimensions (Technical Capital, Technological Adaptability, Career Confidence, Resilience, AI/Digital Readiness).
   - Composite **Graduate Readiness Index (GRI)** calculation.

6. **Institutional Intervention & Automated Alert Engine**:
   - Automated rule-based classification assigning respondents to support profiles (e.g. Technical + Practical Skill Gap -> Bridge Training).

7. **Admin Analytics & Cross-Analysis Engine**:
   - Main Overview Dashboard with Chart.js donut, bar, and trend charts.
   - Category-specific dashboards.
   - Multi-variable Cross-Analysis filter tool.
   - Category Comparison Matrix table.
   - Executive PDF/Printable Research Report Generator.
   - Raw CSV data exporter.

---

## Default Administrator Credentials

| Role | Email | Password |
|---|---|---|
| **Super Admin** | `superadmin@system.edu` | `password` |
| **University Admin** | `admin@demostateuniversity.edu` | `password` |
| **Research Analyst** | `analyst@demostateuniversity.edu` | `password` |

---

## Setup & Installation Instructions

### 1. Requirements
- PHP >= 8.2 with PDO, SQLite/MySQL, MBString extensions
- Composer
- Node.js & NPM

### 2. Environment Configuration
Ensure `.env` contains proper database configuration:
```ini
APP_NAME="StateSkillInsight"
APP_ENV=local
APP_KEY=base64:omms/qu0Y/l2Za6JTTYGATa1r1QHOlzDPcJMzSCgdS4=
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite
```

### 3. Run Database Migrations & Seeders
Execute:
```bash
php artisan migrate:fresh --seed
```
This will run all 10 migrations and populate **Demo State University**, 4 categories, 20 sections, psychometric dimensions, composite GRI index, intervention rules, 200 questions, and 40 demo respondents with full response histories.

### 4. Start Local Development Server
Execute:
```bash
php artisan serve
```
Open [http://127.0.0.1:8000](http://127.0.0.1:8000) in your web browser.

---

## System Architecture

```
StateSkillInsight/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/ (Dashboard, University, Survey, Category, Question, Respondent, Analytics, Report, Export, Voice, User)
│   │   ├── PublicSurveyController.php
│   │   └── VoiceResponseController.php
│   ├── Models/ (University, User, Role, Permission, Survey, SurveyCategory, SurveySection, Question, Respondent, Response, VoiceResponse, PsychometricDimension, CompositeIndex, RespondentScore, InterventionRule, AuditLog, SurveyInvitation)
│   └── Services/ (ScoringService, InterventionEngine, AnalyticsService, ExportService, SurveyService)
├── database/
│   ├── migrations/ (10 normalized schema migration files)
│   └── seeders/ (DatabaseSeeder, RolePermissionSeeder, UniversitySeeder, UserSeeder, SurveySeeder)
├── resources/views/
│   ├── layouts/ (app.blade.php, admin.blade.php, survey.blade.php)
│   ├── survey/ (landing, register, questionnaire, review, thankyou)
│   └── admin/ (dashboard, university, surveys, categories, questions, respondents, analytics, reports, responses, invitations, audit_logs, users)
└── routes/
    ├── web.php (70 named routes)
    └── auth.php
```
