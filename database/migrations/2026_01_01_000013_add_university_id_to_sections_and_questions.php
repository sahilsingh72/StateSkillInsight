<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_sections', function (Blueprint $table) {
            $table->foreignId('university_id')->nullable()->after('category_id')->constrained('universities')->onDelete('cascade');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('university_id')->nullable()->after('section_id')->constrained('universities')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['university_id']);
            $table->dropColumn('university_id');
        });

        Schema::table('survey_sections', function (Blueprint $table) {
            $table->dropForeign(['university_id']);
            $table->dropColumn('university_id');
        });
    }
};
