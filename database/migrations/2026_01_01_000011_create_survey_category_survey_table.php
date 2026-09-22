<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_category_survey', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys')->onDelete('cascade');
            $table->foreignId('survey_category_id')->constrained('survey_categories')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['survey_id', 'survey_category_id']);
        });

        // Link existing master categories (1, 2, 3, 4) to existing surveys
        $surveys = DB::table('surveys')->get();
        $masterCategories = DB::table('survey_categories')->whereIn('id', [1, 2, 3, 4])->get();

        foreach ($surveys as $survey) {
            foreach ($masterCategories as $cat) {
                DB::table('survey_category_survey')->updateOrInsert(
                    [
                        'survey_id' => $survey->id,
                        'survey_category_id' => $cat->id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_category_survey');
    }
};
