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
        Schema::create('orphans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardian_id')->constrained('guardians')->onDelete('cascade');
            $table->string('name');
            $table->string('national_id')->nullable();
            $table->string('passport')->nullable();
            $table->date('birth_date')->nullable();
            $table->tinyInteger('age')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->boolean('lives_with_guardian')->default(true);
            $table->string('phone')->nullable();
            $table->string('education_stage')->nullable();
            $table->string('education_grade')->nullable();
            $table->enum('study_type', ['online', 'onsite'])->nullable();
            $table->boolean('has_chronic_disease')->default(false);
            $table->text('disease_description')->nullable();
            $table->boolean('has_disability')->default(false);
            $table->string('disability_type')->nullable(); // حركي - ذهني - كلاهما
            $table->string('physical_disability_type')->nullable();
            $table->string('mental_disability_type')->nullable();
            $table->boolean('receives_other_sponsorships')->default(false);
            $table->text('other_sponsors')->nullable();
            $table->text('notes')->nullable();

            $table->string('orphan_passport')->nullable();
            $table->string('orphan_entry_stamp')->nullable();
            $table->string('orphan_birth_certificate')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orphans');
    }
};
