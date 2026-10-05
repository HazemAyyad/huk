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
        Schema::create('solidarity_family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solidarity_housing_id')->constrained('solidarity_housings', 'id')->onDelete('cascade');
            $table->string('name');
            $table->string('national_id')->nullable();
            $table->string('passport_number')->nullable();
            $table->date('birth_date')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('relation')->nullable();
            $table->string('education_level')->nullable();
            $table->string('health_status')->nullable();
            $table->string('disease_type')->nullable();
            $table->boolean('has_disability')->default(false);
            $table->string('disability_type')->nullable(); // physical / mental / both
            $table->text('disability_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solidarity_family_members');
    }
};
