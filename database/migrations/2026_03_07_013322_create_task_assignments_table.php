<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assignments', function (Blueprint $table) {
            $table->id();
            
            // الربط مع الفاعلية
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            
            // الربط مع المستخدم (المكلف)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // الوظيفة أو الدور
            $table->string('role'); 
            
            // الحالة (يفضل استخدام string أو enum)
            $table->string('status')->default('pending'); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignments');
    }
};