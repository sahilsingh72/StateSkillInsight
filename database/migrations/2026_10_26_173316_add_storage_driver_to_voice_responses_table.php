<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('voice_responses', function (Blueprint $table) {
            $table->string('storage_driver', 20)->default('onedrive')->after('file_path');
            $table->text('sync_error')->nullable()->after('storage_driver');
        });

        \Illuminate\Support\Facades\DB::table('voice_responses')
            ->where('file_path', 'LIKE', 'voice_responses/%')
            ->orWhere('file_path', 'LIKE', 'local:%')
            ->update(['storage_driver' => 'local']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('voice_responses', function (Blueprint $table) {
            $table->dropColumn(['storage_driver', 'sync_error']);
        });
    }
};
