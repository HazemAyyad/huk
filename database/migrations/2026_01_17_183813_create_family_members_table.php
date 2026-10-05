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
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();

            // ربط المرافق برب الأسرة
            $table->foreignId('telegram_user_id')
                  ->constrained('telegram_users')
                  ->cascadeOnDelete();

            // بيانات المرافق
            $table->string('full_name');
            $table->string('national_id')->unique();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_members');
    }
};
