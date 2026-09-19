<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('survey_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('survey_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('survey_id')->nullable(false)->change();
        });
    }
};
