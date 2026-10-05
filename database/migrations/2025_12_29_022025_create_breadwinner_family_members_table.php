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
        Schema::create('breadwinner_family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained('family_breadwinners', 'id')->onDelete('cascade');
            $table->string('name'); // الاسم
            $table->string('national_id', 9)->nullable(); // رقم الهوية
            $table->date('birth_date')->nullable(); // تاريخ الميلاد
            $table->integer('age')->nullable(); // العمر (تلقائي)
            $table->string('passport_number')->nullable(); // جواز السفر
            $table->string('phone', 11)->nullable(); // رقم الهاتف
            $table->string('relation'); // صلة القرابة
            $table->string('education_level')->nullable(); // المستوى التعليمي
            $table->string('health_status')->nullable(); // الحالة الصحية
            $table->string('disease_type')->nullable(); // نوع المرض
            $table->boolean('has_disability')->default(false); // إعاقة (نعم/لا)
            $table->string('disability_type')->nullable(); // نوع الإعاقة
            $table->text('disability_description')->nullable(); // وصف الإعاقة

            // مرفقات (ثلاثة كما مذكور)
            $table->string('passport_attachment')->nullable(); // جواز سفر
            $table->string('entry_stamp_attachment')->nullable(); // ختم الدخول
            $table->string('medical_reports_attachment')->nullable(); // تقارير طبية (يمكن JSON إذا متعددة)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('breadwinner_family_members');
    }
};
