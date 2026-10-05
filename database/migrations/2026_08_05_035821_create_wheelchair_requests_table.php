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
        Schema::create('wheelchair_requests', function (Blueprint $table) {

            $table->id();

            $table->string('full_name');

            $table->string('national_id')->unique();

            $table->string('phone');

            $table->string('whatsapp')->nullable();

            $table->string('personal_photo');

            $table->string('disability_photo');

            $table->string('passport_photo');

            $table->string('entry_stamp_photo');

            $table->string('medical_report')->nullable();

            $table->integer('age');

            $table->string('address');

            $table->date('entry_date');

            $table->text('medical_condition');

            $table->text('injury_reason');

            $table->boolean('has_wheelchair');

            $table->text('wheelchair_status')->nullable();

            $table->boolean('received_before');

            $table->string('organization_name')->nullable();

            $table->text('notes')->nullable();
            $table->enum('status', [
                'pending',
                'approved',
                'rejected'
            ])->default('pending');

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            $table->text('rejection_reason')->nullable();

            $table->boolean('received_wheelchair')->default(false);

            $table->date('delivered_at')->nullable();
            
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wheelchair_requests');
    }
};
