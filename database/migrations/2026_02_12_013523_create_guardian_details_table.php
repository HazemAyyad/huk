<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardian_details', function (Blueprint $table) {
            $table->id();

            // ربط مع telegram_users
            $table->foreignId('telegram_user_id')
                  ->constrained()
                  ->onDelete('cascade');

            // بيانات ولي الأمر الإضافية
            $table->date('birth_date')->nullable();
            $table->integer('age')->nullable();
            $table->string('relationship_to_case')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('housing_status')->nullable();
            $table->decimal('monthly_income', 10, 2)->nullable();

            // الموقع
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // المرفقات
            $table->string('personal_photo')->nullable();
            $table->string('id_document')->nullable();
            $table->string('passport')->nullable();
            $table->string('entry_stamp')->nullable();
            $table->string('medical_reports')->nullable();
            $table->string('rent_contract')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardian_details');
    }
};
