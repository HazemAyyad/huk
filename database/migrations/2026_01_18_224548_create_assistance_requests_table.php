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
        Schema::create('assistance_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('assistance_type')->nullable();
            
            // بيانات المستفيد
            $table->string('beneficiary_name')->nullable();
            $table->string('beneficiary_nid', 9)->nullable();
            
            // بيانات الزوج/الزوجة (اختياري)
            $table->string('spouse_name')->nullable();
            $table->string('spouse_nid', 9)->nullable();
            
            // بيانات الأسرة
            $table->date('birth_date')->nullable();
            $table->integer('family_members_count')->default(1);
            
            $table->date('request_date');
            $table->date('assistance_date')->nullable();
            
            // بيانات إضافية
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            
            // حالة الطلب
            $table->enum('status', ['new', 'under_review', 'approved', 'rejected', 'completed'])
                  ->default('new');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assistance_requests');
    }
};
