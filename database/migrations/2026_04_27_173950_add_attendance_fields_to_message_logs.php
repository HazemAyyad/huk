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
        Schema::table('message_logs', function (Blueprint $table) {
            $table->timestamp('attended_at')->nullable();
            $table->timestamp('excused_at')->nullable(); // معتذر
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('message_logs', function (Blueprint $table) {
            $table->dropColumn(['attended_at', 'excused_at']);
        });
    }
};
