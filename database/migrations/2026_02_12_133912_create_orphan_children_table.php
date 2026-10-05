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
        Schema::create('orphan_children', function (Blueprint $table) {
            $table->id();

            $table->foreignId('family_member_id')
                ->constrained()
                ->onDelete('cascade');

            $table->date('birth_date')->nullable();
            $table->integer('age')->nullable();
            $table->enum('gender', ['male','female'])->nullable();
            $table->boolean('lives_with_guardian')->nullable();
            $table->string('education_level')->nullable();
            $table->string('study_type')->nullable();
            $table->boolean('has_chronic_disease')->nullable();
            $table->boolean('has_disability')->nullable();

            $table->string('passport')->nullable();
            $table->string('entry_stamp')->nullable();
            $table->string('birth_certificate')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orphan_children');
    }
};
