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
        Schema::create('stranded_family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stranded_family_id')->constrained('stranded_families')->onDelete('cascade');

            $table->string('name'); // الاسم
            $table->string('national_id', 9)->nullable(); // رقم الهوية
            $table->string('phone', 11)->nullable(); // رقم الهاتف
            $table->string('passport_number')->nullable(); // جواز السفر
            $table->date('birth_date')->nullable(); // تاريخ الميلاد
            $table->integer('age')->nullable(); // العمر تلقائي
            $table->string('relation'); // صلة القرابة (ابن، بنت، أخ، إلخ)
            $table->string('education_level')->nullable(); // المستوى التعليمي
            $table->string('health_status')->nullable(); // الحالة الصحية
            $table->string('disease_type')->nullable(); // نوع المرض
            $table->boolean('has_disability')->default(false); // إعاقة (نعم/لا)
            $table->string('disability_type')->nullable();
            $table->text('disability_description')->nullable(); // وصف الإعاقة

            // مرفقات العضو
            $table->string('passport_attachment')->nullable(); // جواز سفر
            $table->string('entry_stamp_attachment')->nullable(); // ختم الدخول
            $table->string('medical_reports_attachment')->nullable(); // تقارير طبية
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stranded_family_members');
    }
};
