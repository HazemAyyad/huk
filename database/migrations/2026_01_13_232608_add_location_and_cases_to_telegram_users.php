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
            $table->string('governorate')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->boolean('benefited_before')->nullable();
            $table->string('target_case')->nullable();
            $table->text('companions')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telegram_users', function (Blueprint $table) {
            $table->dropColumn([
                'governorate',
                'city',
                'address',
                'benefited_before',
                'target_case',
                'companions',
            ]);
        });
    }
};
