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
        Schema::create('message_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('telegram_user_id')
                ->constrained('telegram_users')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('sent_by')->nullable(); 
            // admin id (users table)

            $table->string('sender_type')->default('admin'); 
            // admin | system

            $table->text('message');

            $table->string('event_name')->nullable(); 
            // اسم الفاعلية

            $table->enum('message_type', [
                'invitation',      // دعوة حضور
                'attended',        // تم الحضور
                'received',        // تم الاستلام
                'general'          // عامة
            ])->default('general');

            $table->enum('delivery_status', [
                'pending',
                'sent',
                'failed'
            ])->default('pending');

            $table->timestamp('sent_at')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_logs');
    }
};
