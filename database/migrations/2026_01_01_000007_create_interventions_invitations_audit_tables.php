<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intervention_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category_code'); // cat_1, cat_2, cat_3, cat_4
            $table->json('conditions_json'); // e.g. {"technical_score": {"<": 60}, "practical_score": {"<": 60}}
            $table->text('recommended_intervention');
            $table->string('priority')->default('medium'); // high, medium, low
            $table->timestamps();
        });

        Schema::create('respondent_interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('respondent_survey_id')->constrained('respondent_surveys')->onDelete('cascade');
            $table->foreignId('intervention_rule_id')->constrained('intervention_rules')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('survey_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys')->onDelete('cascade');
            $table->string('name');
            $table->string('email');
            $table->string('mobile')->nullable();
            $table->string('category_code')->default('cat_1');
            $table->string('programme')->nullable();
            $table->string('department')->nullable();
            $table->string('alumni_student_id')->nullable();
            $table->string('token')->unique();
            $table->string('status')->default('invited'); // invited, opened, started, completed
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action');
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('survey_invitations');
        Schema::dropIfExists('respondent_interventions');
        Schema::dropIfExists('intervention_rules');
    }
};
