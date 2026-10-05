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
        Schema::create('family_breadwinners', function (Blueprint $table) {
            $table->id();
            $table->string('case_code')->unique();

            // بيانات الزوجة (الأم)
            $table->string('wife_full_name'); // 1- اسم الزوجة رباعي
            $table->string('wife_national_id', 9)->unique(); // 2- رقم الهوية
            $table->string('wife_main_phone', 11); // 3- رقم هاتف مصري معتمد (مفعل واتس اب)
            $table->string('wife_additional_phone', 11)->nullable(); // 4- رقم هاتف إضافي
            $table->string('wife_passport_number')->nullable(); // 5- رقم جواز السفر
            $table->date('wife_birth_date')->nullable(); // 6- تاريخ الميلاد
            $table->integer('wife_age')->nullable(); // 7- العمر (يُحسب تلقائياً في Model)

            $table->string('detailed_address'); // 9- العنوان بالتفصيل (محافظة + منطقة/حي)
            $table->string('housing_status'); // 11- حالة السكن
            $table->decimal('rent_value', 10, 2)->nullable(); // 12- قيمة الإيجار
            $table->string('social_status'); // 13- الحالة الاجتماعية
            $table->string('wife_health_status')->nullable(); // 14- الحالة الصحية
            $table->date('entry_date_to_egypt')->nullable(); // 15- تاريخ دخول مصر
            $table->string('entry_method')->nullable(); // 16- آلية دخول مصر

            $table->foreignId('governorate_id')->nullable()->constrained("governorates", "id")->cascadeOnDelete();
            $table->foreignId('city_id')->nullable()->constrained("cities", "id")->cascadeOnDelete();
            $table->foreignId('area_id')->nullable()->constrained("areas", "id")->cascadeOnDelete();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->string('image')->nullable();
            $table->longText('qr_code')->nullable();

            // بيانات الزوج
            $table->string('husband_name')->nullable(); // 17- اسم الزوج
            $table->string('husband_national_id', 14)->nullable(); // 18- رقم الهوية
            $table->string('husband_location')->nullable(); // 19- مكان تواجده

            // الوضع الاقتصادي
            $table->boolean('has_fixed_income')->default(false); // هل يوجد مصدر دخل ثابت (نعم/لا)
            $table->decimal('income_value', 10, 2)->nullable(); // قيمة الدخل
            $table->string('income_source')->nullable(); // الجهة

            // ملاحظات
            $table->text('notes')->nullable(); // اضافة ملاحظات

            // خاص بالإدارة
            $table->text('case_description')->nullable(); // وصف الحالة
            $table->text('researcher_opinion')->nullable(); // رأي الباحث
            $table->boolean('deserves_support')->nullable(); // هل تستحق الأسرة (نعم/لا)
            $table->text('deserve_reason')->nullable(); // السبب إذا نعم أو لا
            $table->string('researcher_name')->nullable(); // اسم الباحث
            $table->foreignId('researcher_id')->nullable()->constrained('users', 'id')->cascadeOnDelete();

            // مرفقات الزوجة/الأم
            $table->string('wife_id_attachment')->nullable(); // هوية الأم
            $table->string('wife_passport_attachment')->nullable(); // جواز سفر الأم
            $table->string('wife_entry_stamp_attachment')->nullable(); // ختم الدخول للأم
            $table->string('wife_medical_report_attachment')->nullable(); // التقرير الطبي للأم
            $table->string('rent_contract_attachment')->nullable(); // عقد الإيجار
            $table->string('divorce_certificate_attachment')->nullable(); // شهادة الطلاق
            $table->string('death_certificate_attachment')->nullable(); // شهادة الوفاة (إذا أرملة)

            $table->foreignId('user_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_breadwinners');
    }
};
