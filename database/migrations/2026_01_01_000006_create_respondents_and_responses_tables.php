<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('respondents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('university_id')->constrained('universities')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('token')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('university_student_alumni_id')->nullable();
            $table->string('programme')->nullable();
            $table->string('department')->nullable();
            $table->string('graduation_year')->nullable();
            $table->string('admission_year')->nullable();
            $table->string('category_code')->default('cat_1'); // cat_1, cat_2, cat_3, cat_4
            $table->string('current_city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('India');
            $table->string('employment_status')->nullable();
            $table->boolean('consent_given')->default(true);
            $table->timestamp('consent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('respondent_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('respondent_id')->constrained('respondents')->onDelete('cascade');
            $table->foreignId('survey_id')->constrained('surveys')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('survey_categories')->onDelete('cascade');
            $table->string('status')->default('in_progress'); // not_started, in_progress, review, completed
            $table->foreignId('current_section_id')->nullable()->constrained('survey_sections')->onDelete('set null');
            $table->decimal('completion_percentage', 5, 2)->default(0.00);
            $table->timestamp('last_saved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('respondent_survey_id')->constrained('respondent_surveys')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->longText('text_value')->nullable();
            $table->decimal('number_value', 12, 2)->nullable();
            $table->date('date_value')->nullable();
            $table->json('json_value')->nullable(); // multi-select options, matrix answers, ranking
            $table->decimal('score', 8, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('response_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained('responses')->onDelete('cascade');
            $table->foreignId('question_option_id')->constrained('question_options')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('voice_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained('responses')->onDelete('cascade');
            $table->string('file_path');
            $table->integer('duration_seconds')->nullable();
            $table->string('mime_type')->default('audio/webm');
            $table->integer('file_size')->default(0);
            $table->json('thematic_tags')->nullable();
            $table->text('transcript_text')->nullable();
            $table->timestamps();
        });

        Schema::create('respondent_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('respondent_survey_id')->constrained('respondent_surveys')->onDelete('cascade');
            $table->foreignId('dimension_id')->nullable()->constrained('psychometric_dimensions')->onDelete('cascade');
            $table->foreignId('composite_index_id')->nullable()->constrained('composite_indexes')->onDelete('cascade');
            $table->decimal('score', 8, 2)->default(0.00);
            $table->decimal('max_possible', 8, 2)->default(100.00);
            $table->string('interpretation_band')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('respondent_scores');
        Schema::dropIfExists('voice_responses');
        Schema::dropIfExists('response_options');
        Schema::dropIfExists('responses');
        Schema::dropIfExists('respondent_surveys');
        Schema::dropIfExists('respondents');
    }
};
