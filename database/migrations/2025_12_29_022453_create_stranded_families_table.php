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
        Schema::create('stranded_families', function (Blueprint $table) {
            $table->id();
            $table->string('case_code')->unique();

            // بيانات رب الأسرة (الأب)
            $table->string('head_full_name'); // 1- اسم رب الأسرة رباعي
            $table->string('head_national_id', 9)->unique(); // 2- رقم الهوية
            $table->string('head_main_phone', 11); // 3- رقم هاتف مصري معتمد (واتس اب)
            $table->string('head_additional_phone', 11)->nullable(); // 4- رقم هاتف إضافي
            $table->string('head_passport_number')->nullable(); // 5- رقم جواز السفر
            $table->date('head_birth_date')->nullable(); // 6- تاريخ الميلاد
            $table->integer('head_age')->nullable(); // 7- العمر (تلقائي من تاريخ الميلاد)
            $table->string('detailed_address'); // 9- العنوان بالتفصيل

            $table->enum('housing_status', ['إيجار', 'ملك', 'مستضاف']); // 11- حالة السكن
            $table->decimal('rent_value', 10, 2)->nullable(); // 12- قيمة الإيجار
            $table->string('social_status')->nullable(); // 13- الحالة الاجتماعية (نص حر أو enum إذا محدد)
            $table->string('head_health_status')->nullable(); // 14- الحالة الصحية لرأس الأسرة

            $table->foreignId('governorate_id')->nullable()->constrained("governorates", "id")->cascadeOnDelete();
            $table->foreignId('city_id')->nullable()->constrained("cities", "id")->cascadeOnDelete();
            $table->foreignId('area_id')->nullable()->constrained("areas", "id")->cascadeOnDelete();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('image')->nullable();
            $table->longText('qr_code')->nullable();

            // هل يعمل رب الأسرة؟
            $table->boolean('head_is_employed')->default(false); // 15- هل رب الأسرة يعمل (نعم/لا)
            $table->string('head_job')->nullable(); // المهنة
            $table->decimal('head_income', 10, 2)->nullable(); // قيمة الدخل

            $table->date('entry_date_to_egypt')->nullable(); // 16- تاريخ دخول مصر (للأسرة أو رب الأسرة)
            $table->string('entry_method')->nullable(); // 17- آلية دخول مصر

            // بيانات الزوجة
            $table->string('wife_name')->nullable(); // 18- اسم الزوجة
            $table->string('wife_phone', 11)->nullable(); // 19- رقم هاتف الزوجة
            $table->string('wife_national_id', 14)->nullable(); // 20- رقم هوية الزوجة

            // الوضع الاقتصادي (إضافي إذا كان هناك دخل آخر)
            $table->text('economic_notes')->nullable();

            // خاص بالإدارة
            $table->text('case_description')->nullable(); // وصف الحالة
            $table->text('researcher_opinion')->nullable(); // رأي الباحث
            $table->boolean('deserves_support')->nullable(); // هل تستحق الأسرة (نعم/لا)
            $table->text('deserve_reason')->nullable(); // السبب
            $table->string('researcher_name')->nullable(); // اسم الباحث
            $table->foreignId('researcher_id')->nullable()->constrained('users', 'id')->cascadeOnDelete();

            // مرفقات رب الأسرة
            $table->string('head_id_attachment')->nullable(); // هوية رب الأسرة
            $table->string('head_passport_attachment')->nullable(); // جواز سفر رب الأسرة
            $table->string('head_entry_stamp_attachment')->nullable(); // ختم دخول رب الأسرة
            $table->string('head_medical_reports_attachment')->nullable(); // تقارير طبية (يمكن JSON إذا متعددة)
            $table->string('rent_contract_attachment')->nullable(); // عقد الإيجار

            // مرفقات الزوجة
            $table->string('wife_id_attachment')->nullable(); // هوية الزوجة
            $table->string('wife_passport_attachment')->nullable(); // جواز سفر الزوجة
            $table->string('wife_entry_stamp_attachment')->nullable(); // ختم دخول الزوجة

            $table->foreignId('user_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stranded_families');
    }
};
