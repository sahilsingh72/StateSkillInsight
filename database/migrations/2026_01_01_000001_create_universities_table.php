<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('universities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name');
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('tagline')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('India');
            $table->string('primary_color')->default('#1e40af'); // Indigo/Blue
            $table->string('secondary_color')->default('#0d9488'); // Teal
            $table->string('survey_header')->nullable();
            $table->text('footer_text')->nullable();
            $table->text('privacy_text')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('universities');
    }
};
