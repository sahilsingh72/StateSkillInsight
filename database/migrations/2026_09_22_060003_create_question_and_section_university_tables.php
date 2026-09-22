<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_university', function (Blueprint $table) {
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->foreignId('university_id')->constrained('universities')->onDelete('cascade');
            $table->primary(['question_id', 'university_id']);
        });

        Schema::create('section_university', function (Blueprint $table) {
            $table->foreignId('section_id')->constrained('survey_sections')->onDelete('cascade');
            $table->foreignId('university_id')->constrained('universities')->onDelete('cascade');
            $table->primary(['section_id', 'university_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_university');
        Schema::dropIfExists('question_university');
    }
};
