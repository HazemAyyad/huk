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
        Schema::create('patient_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_code')->unique();

            // البيانات الأساسية للمريض الرئيسي
            $table->string('full_name'); // الاسم رباعي
            $table->string('national_id', 9)->unique()->nullable(); // رقم الهوية
            $table->string('main_phone', 11); // رقم هاتف مصري معتمد (واتس اب)
            $table->string('additional_phone', 11)->nullable(); // رقم هاتف إضافي
            $table->string('passport_number')->nullable(); // رقم جواز السفر
            $table->date('birth_date')->nullable(); // تاريخ الميلاد
            $table->integer('age')->nullable(); // العمر (تلقائي)
            $table->string('detailed_address'); // العنوان بالتفصيل
            $table->enum('housing_status', [
                'Rent',
                'Owned',
                'Hosted'
            ]); // حالة السكن
            $table->decimal('rent_value', 10, 2)->nullable(); // قيمة الإيجار
            $table->string('social_status')->nullable(); // الحالة الاجتماعية
            $table->date('entry_date_to_egypt')->nullable(); // تاريخ دخول مصر
            $table->enum('entry_method', [
                'Hala Company',
                'External Lists',
                'Foreign Lists',
                'Medical Referrals'
            ])->nullable(); // طريقة دخول مصر

            // بيانات المرض
            $table->enum('disease_type', [
                'Cancer',
                'Heart',
                'Orthopedics and Neurology',
                'Dermatology',
                'Ear, Nose and Throat (ENT)',
                'Kidney',
                'Other'
            ]); // نوع المرض
            $table->string('other_disease_type')->nullable(); // إذا أخرى → كتابة النوع
            $table->text('health_description')->nullable(); // وصف الحالة الصحية
            $table->decimal('monthly_treatment_cost', 12, 2)->nullable(); // تكاليف العلاج الشهرية

            // المرافق
            $table->boolean('has_companion')->default(false); // هل يوجد مرافق (نعم/لا)
            $table->string('companion_name')->nullable(); // اسم المرافق
            $table->string('companion_national_id', 9)->nullable(); // رقم هوية المرافق
            $table->string('companion_passport_number')->nullable(); // جواز سفر المرافق
            $table->string('companion_phone', 11)->nullable(); // رقم هاتف المرافق

            // الوضع الاقتصادي
            $table->boolean('has_fixed_income')->default(false); // هل يوجد مصدر دخل ثابت
            $table->decimal('income_value', 10, 2)->nullable(); // قيمة الدخل
            $table->string('income_source')->nullable(); // الجهة

            $table->foreignId('governorate_id')->nullable()->constrained("governorates", "id")->cascadeOnDelete();
            $table->foreignId('city_id')->nullable()->constrained("cities", "id")->cascadeOnDelete();
            $table->foreignId('area_id')->nullable()->constrained("areas", "id")->cascadeOnDelete();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('image')->nullable();
            $table->longText('qr_code')->nullable();

            // ملاحظات عامة
            $table->text('notes')->nullable();

            // خاص بالإدارة
            $table->text('case_description')->nullable(); // وصف الحالة
            $table->text('researcher_opinion')->nullable(); // رأي الباحث
            $table->boolean('deserves_support')->nullable(); // هل تستحق الأسرة (نعم/لا)
            $table->text('deserve_reason')->nullable(); // السبب
            $table->string('researcher_name')->nullable(); // اسم الباحث
            $table->foreignId('researcher_id')->nullable()->constrained('users', 'id')->cascadeOnDelete();

            // مرفقات المريض الرئيسي
            $table->string('id_attachment')->nullable(); // الهوية
            $table->string('passport_attachment')->nullable(); // جواز السفر
            $table->string('entry_stamp_attachment')->nullable(); // ختم الدخول
            $table->string('medical_report_attachment')->nullable(); // التقرير الطبي
            $table->string('prescription_photo_attachment')->nullable(); // صورة روشتة الأدوية
            $table->string('referral_attachment')->nullable(); // التحويلة
            $table->string('rent_contract_attachment')->nullable(); // عقد الإيجار

            // مرفقات المرافق
            $table->string('companion_id_attachment')->nullable(); // هوية المرافق
            $table->string('companion_passport_attachment')->nullable(); // جواز سفر المرافق
            $table->string('companion_entry_stamp_attachment')->nullable(); // ختم دخول المرافق

            $table->foreignId('user_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_cases');
    }
};
