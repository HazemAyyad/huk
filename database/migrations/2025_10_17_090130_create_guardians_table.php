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
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->string('case_code')->unique();
            $table->string('full_name');
            $table->string('national_id')->unique();
            $table->string('phone')->nullable();
            $table->string('phone2')->nullable();
            $table->string('passport_number')->nullable();
            $table->date('birth_date')->nullable();
            $table->tinyInteger('age')->nullable();
            $table->string('relation')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();

            $table->foreignId('governorate_id')->nullable()->constrained("governorates", "id")->cascadeOnDelete();
            $table->foreignId('city_id')->nullable()->constrained("cities", "id")->cascadeOnDelete();
            $table->foreignId('area_id')->nullable()->constrained("areas", "id")->cascadeOnDelete();

            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('housing_status', ['rent', 'own', 'hosted'])->nullable();
            $table->decimal('rent_value', 10, 2)->nullable();
            $table->enum('marital_status', ['married', 'widow', 'single', 'divorced'])->nullable();
            $table->string('health_status')->nullable();
            $table->integer('family_count')->nullable();
            $table->date('entry_date')->nullable();
            $table->string('entry_method')->nullable();

            $table->string('image')->nullable();
            $table->longText('qr_code')->nullable();

            $table->string('guardian_id')->nullable();
            $table->string('guardian_passport')->nullable();
            $table->string('guardian_entry_stamp')->nullable();
            $table->string('medical_reports')->nullable();
            $table->string('rent_contract')->nullable();
            $table->string('death_certificate')->nullable();

            $table->text('case_description')->nullable();
            $table->text('researcher_opinion')->nullable();
            $table->text('family_reason')->nullable();
            $table->enum('family_deserves', ['yes', 'no'])->default('no');
            $table->foreignId('researcher_id')->nullable()->constrained('users', 'id')->cascadeOnDelete();

            $table->boolean('is_reviewed')->default(false);
            $table->boolean('is_sent_for_attendance')->default(false);
            $table->boolean('is_attended')->default(false);
            $table->boolean('is_benefited')->default(false);

            $table->foreignId('user_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};
