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
        Schema::create('deceaseds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardian_id')->constrained('guardians')->onDelete('cascade');
            $table->string('type')->nullable(); // يتيم الاب / يتيم الابوين
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->date('father_death_date')->nullable();
            $table->date('mother_death_date')->nullable();
            $table->text('father_death_reason')->nullable();
            $table->text('mother_death_reason')->nullable();
            $table->string('father_job')->nullable();
            $table->string('mother_job')->nullable();
            $table->decimal('income', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deceaseds');
    }
};
