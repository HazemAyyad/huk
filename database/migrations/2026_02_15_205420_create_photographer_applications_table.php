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
        Schema::create('photographer_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photographer_ad_id')->constrained()->cascadeOnDelete();

            $table->string('full_name');
            $table->string('national_id')->index();
            $table->string('passport')->nullable();
            $table->integer('age')->nullable();

            $table->text('camera_equipment')->nullable();
            $table->string('phone_egypt');
            $table->string('phone_egypt_2')->nullable();
            $table->string('whatsapp_palestine')->nullable();

            $table->integer('family_in_egypt')->nullable();
            $table->integer('family_in_gaza')->nullable();

            $table->string('entry_reason')->nullable();
            $table->date('entry_date')->nullable();

            $table->text('address')->nullable();
            $table->string('education')->nullable();
            $table->string('work_in_gaza')->nullable();

            $table->boolean('has_experience')->default(false);
            $table->text('experience_details')->nullable();

            $table->text('case_details')->nullable();

            $table->enum('status', ['pending','approved','rejected'])->default('pending');

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photographer_applications');
    }
};
