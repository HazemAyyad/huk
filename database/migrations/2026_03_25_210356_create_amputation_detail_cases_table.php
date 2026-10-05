<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('amputation_detail_cases', function (Blueprint $table) {
            $table->id();

            // ربط
            // $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('telegram_user_id')->constrained()->cascadeOnDelete();

            // بيانات أساسية
            $table->string('full_name');
            $table->string('national_id');

            // بيانات جديدة
            $table->date('birth_date')->nullable();
            $table->integer('age')->nullable();
            $table->enum('gender', ['male','female'])->nullable();

            $table->string('housing_status')->nullable();
            $table->string('marital_status')->nullable();

            $table->date('entry_date')->nullable();
            $table->string('entry_method')->nullable();

            $table->date('amputation_date')->nullable();
            $table->string('amputation_type')->nullable();
            $table->text('injury_reason')->nullable();

            $table->boolean('needs_prosthetic')->default(false);
            $table->boolean('received_help')->default(false);
            $table->string('help_type')->nullable();

            $table->text('health_description')->nullable();
            $table->boolean('has_income')->default(false);

            // موقع
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // صور
            $table->string('person_image')->nullable();
            $table->string('amputation_image')->nullable();

            // مرفقات
            $table->string('guardian_id')->nullable();
            $table->string('guardian_passport')->nullable();
            $table->string('entry_stamp')->nullable();
            $table->string('medical_reports')->nullable();
            $table->string('rent_contract')->nullable();
            $table->string('status')->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amputation_detail_cases');
    }
};
