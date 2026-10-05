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
        Schema::create('solidarity_housings', function (Blueprint $table) {
            $table->id();
            $table->string('case_code')->unique();
            $table->string('full_name');
            $table->string('national_id')->unique();
            $table->string('primary_phone')->nullable();
            $table->string('secondary_phone')->nullable();
            $table->string('passport_number')->nullable();
            $table->date('birth_date')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->foreignId('governorate_id')->nullable()->constrained("governorates", "id")->cascadeOnDelete();
            $table->foreignId('city_id')->nullable()->constrained("cities", "id")->cascadeOnDelete();
            $table->foreignId('area_id')->nullable()->constrained("areas", "id")->cascadeOnDelete();
            $table->text('address')->nullable();
            $table->string('location')->nullable(); // e.g. "lat,lng" or text
            $table->enum('housing_status', ['rent', 'own', 'hosted'])->nullable();
            $table->decimal('rent_value', 12, 2)->nullable();
            $table->string('marital_status')->nullable();
            $table->string('health_status')->nullable();
            $table->date('entry_date')->nullable();
            $table->string('entry_method')->nullable();

            // medical
            $table->date('amputation_date')->nullable();
            $table->string('amputation_place')->nullable();
            $table->string('injury_cause')->nullable();
            $table->string('amputated_part')->nullable();
            $table->boolean('needs_prosthetic')->default(false);
            $table->boolean('received_aid_from_haq')->default(false);
            $table->string('aid_type')->nullable();
            $table->text('health_description')->nullable();

            // companion
            $table->boolean('has_companion')->default(false);
            $table->string('companion_name')->nullable();
            $table->string('companion_national_id')->nullable();
            $table->string('companion_phone')->nullable();

            // family & economic
            $table->unsignedSmallInteger('family_members_count')->nullable();
            $table->text('family_composition')->nullable();
            $table->boolean('has_fixed_income')->default(false);
            $table->decimal('monthly_income', 12, 2)->nullable();
            $table->string('income_source')->nullable();
            $table->text('economic_status')->nullable();

            $table->text('notes')->nullable();

            $table->string('image')->nullable();
            $table->longText('qr_code')->nullable();

            $table->string('case_id')->nullable();
            $table->string('case_passport')->nullable();
            $table->string('case_entry_stamp')->nullable();
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
        Schema::dropIfExists('solidarity_housings');
    }
};
