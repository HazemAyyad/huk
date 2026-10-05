<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photographer_applications', function (Blueprint $table) {
            // 1. إضافة الحقول الجديدة
            $table->string('personal_photo')->after('full_name')->nullable();
            $table->string('governorate')->after('personal_photo')->nullable();

            // 2. تغيير حقل الباسبورت (تأكد أنه string ليقبل المسار)
            // إذا كان أصلاً string، فالأفضل إضافة تعليق أو تركه كما هو
            $table->string('passport')->change()->nullable(); 
        });
    }

    public function down(): void
    {
        Schema::table('photographer_applications', function (Blueprint $table) {
            $table->dropColumn(['personal_photo', 'governorate']);
            // تراجع عن التعديلات هنا إذا لزم الأمر
        });
    }
};