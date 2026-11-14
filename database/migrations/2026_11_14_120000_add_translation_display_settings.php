<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_content_settings', function (Blueprint $table) {
            $table->boolean('show_translation_source_names')->default(false);
            $table->boolean('show_translation_footnotes')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('app_content_settings', function (Blueprint $table) {
            $table->dropColumn(['show_translation_source_names', 'show_translation_footnotes']);
        });
    }
};
