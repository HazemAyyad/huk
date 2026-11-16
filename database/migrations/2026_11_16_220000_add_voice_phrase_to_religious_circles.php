<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('religious_circles', function (Blueprint $table): void {
            $table->string('voice_phrase', 180)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('religious_circles', function (Blueprint $table): void {
            $table->dropColumn('voice_phrase');
        });
    }
};
