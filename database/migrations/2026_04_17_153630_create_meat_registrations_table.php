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
        Schema::create('meat_registrations', function (Blueprint $table) {
            $table->id();

            // ربط مع رب الأسرة
            $table->foreignId('telegram_user_id')->constrained()->cascadeOnDelete();

            // لمنع التكرار
            $table->string('national_id')->unique();

            // خيارات التوزيع فقط
            $table->boolean('want_tripe')->default(false); // كرشة
            $table->boolean('want_legs')->default(false);  // رجلين
            $table->boolean('separate_family')->default(false); // أسرة منفصلة

            // ملاحظات (اختياري)
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meat_registrations');
    }
};
