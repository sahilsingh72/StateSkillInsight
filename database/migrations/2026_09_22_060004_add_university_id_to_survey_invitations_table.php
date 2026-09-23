<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_invitations', function (Blueprint $table) {
            $table->foreignId('university_id')->nullable()->after('survey_id')->constrained('universities')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('survey_invitations', function (Blueprint $table) {
            $table->dropForeign(['university_id']);
            $table->dropColumn('university_id');
        });
    }
};
