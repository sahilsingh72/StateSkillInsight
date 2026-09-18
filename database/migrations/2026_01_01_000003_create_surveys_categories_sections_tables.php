<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('university_id')->constrained('universities')->onDelete('cascade');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->text('opening_message')->nullable();
            $table->string('status')->default('published'); // draft, published, paused, closed, archived
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('language')->default('en');
            $table->string('target_respondents')->default('All Students & Alumni');
            $table->integer('estimated_completion_time')->default(20); // in minutes
            $table->boolean('allow_anonymous')->default(true);
            $table->boolean('require_auth')->default(false);
            $table->boolean('allow_resume')->default(true);
            $table->boolean('enable_voice')->default(true);
            $table->string('version')->default('1.0');
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('survey_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys')->onDelete('cascade');
            $table->string('code'); // cat_1, cat_2, cat_3, cat_4
            $table->string('name'); // e.g. Working Alumni, Job-Seeking Alumni, Current Students, Interrupted Students
            $table->text('description')->nullable();
            $table->text('opening_message')->nullable();
            $table->text('eligibility')->nullable();
            $table->integer('order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->string('icon')->nullable();
            $table->integer('estimated_minutes')->default(15);
            $table->timestamps();
        });

        Schema::create('survey_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('survey_categories')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('order')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_sections');
        Schema::dropIfExists('survey_categories');
        Schema::dropIfExists('surveys');
    }
};
