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
        Schema::table('telegram_users', function (Blueprint $table) {
            $table->string('step')->nullable(); // current step
            $table->string('full_name')->nullable();
            $table->string('national_id')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('telegram_number')->nullable();
            $table->integer('family_members')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telegram_users', function (Blueprint $table) {
            $table->dropColumn([
                'step',
                'full_name',
                'national_id',
                'whatsapp',
                'telegram_number',
                'family_members',
            ]);
        });
    }
};
