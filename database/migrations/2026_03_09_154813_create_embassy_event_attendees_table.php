<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('embassy_event_attendees', function (Blueprint $table) {
            $table->id();

            $table->string('type'); // مريض أو مرافق
            $table->string('code')->nullable();
            $table->string('full_name');
            $table->string('national_id')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('attended')->default(false);

            // **تتبع المستخدمين**
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes(); // لتفعيل الحذف المنطقي
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('embassy_event_attendees');
    }
};
