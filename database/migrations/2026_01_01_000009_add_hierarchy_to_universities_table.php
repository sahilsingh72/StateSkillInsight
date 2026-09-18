<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            $table->enum('type', ['university', 'affiliated_college', 'autonomous_college'])->default('university')->after('short_name');
            $table->unsignedBigInteger('parent_id')->nullable()->after('type');

            $table->foreign('parent_id')->references('id')->on('universities')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'type']);
        });
    }
};
