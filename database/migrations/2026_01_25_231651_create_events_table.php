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
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            $table->string('name'); // اسم الفاعلية
            $table->date('event_date')->nullable();
            $table->time('event_time')->nullable();

            $table->enum('aid_type', ['in_kind', 'cash', 'other']);
            $table->decimal('value', 10, 2)->nullable();

            $table->text('extras')->nullable(); // إضافات (كيلو لحمة...)
            $table->string('location')->nullable();

            $table->text('default_message')->nullable(); 
            // نص الرسالة الافتراضي

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
