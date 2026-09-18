<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('survey_sections')->onDelete('cascade');
            $table->text('question_text');
            $table->text('help_text')->nullable();
            $table->string('type'); // single_choice, multiple_choice, dropdown, rating, likert, matrix, short_text, long_text, voice, file_upload, number, date, email, phone, yes_no, ranking, percentage, slider, info
            $table->boolean('is_required')->default(true);
            $table->integer('order')->default(1);
            $table->foreignId('dimension_id')->nullable()->constrained('psychometric_dimensions')->onDelete('set null');
            $table->decimal('weight', 5, 2)->default(1.00);
            $table->json('tags')->nullable(); // ["technical", "ai", "employability"]
            $table->json('settings')->nullable(); // max_recording_sec, min/max values, file types, matrix rows/cols
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->string('option_text');
            $table->string('value');
            $table->decimal('score', 8, 2)->default(0.00);
            $table->integer('order')->default(1);
            $table->timestamps();
        });

        Schema::create('question_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->string('locale', 10); // hi, or, etc.
            $table->text('question_text');
            $table->text('help_text')->nullable();
            $table->json('options_json')->nullable();
            $table->timestamps();
        });

        Schema::create('question_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade'); // Question to hide/show
            $table->foreignId('depends_on_question_id')->constrained('questions')->onDelete('cascade'); // Trigger question
            $table->string('operator')->default('equals'); // equals, not_equals, contains, greater_than, in
            $table->text('value');
            $table->string('action')->default('show'); // show, hide, skip_to_section, end_survey
            $table->foreignId('target_section_id')->nullable()->constrained('survey_sections')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_conditions');
        Schema::dropIfExists('question_translations');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
    }
};
