<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psychometric_dimensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->nullable()->constrained('surveys')->onDelete('cascade');
            $table->string('category_code')->nullable(); // cat_1, cat_2, cat_3, cat_4
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->decimal('min_score', 8, 2)->default(0.00);
            $table->decimal('max_score', 8, 2)->default(100.00);
            $table->json('interpretation_bands')->nullable();
            $table->timestamps();
        });

        Schema::create('composite_indexes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->nullable()->constrained('surveys')->onDelete('cascade');
            $table->string('name'); // e.g. Graduate Readiness Index (GRI)
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->json('weights_json')->nullable(); // {"dim_tech": 25, "dim_prac": 20, ...}
            $table->timestamps();
        });

        Schema::create('scoring_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dimension_id')->constrained('psychometric_dimensions')->onDelete('cascade');
            $table->string('rule_type')->default('option_sum'); // option_sum, average, weighted
            $table->decimal('weight', 5, 2)->default(1.00);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scoring_rules');
        Schema::dropIfExists('composite_indexes');
        Schema::dropIfExists('psychometric_dimensions');
    }
};
