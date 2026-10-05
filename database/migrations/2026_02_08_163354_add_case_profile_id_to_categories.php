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
        Schema::table('categories', function (Blueprint $table) {
            // أضف nullable() لكي يسمح بإنشاء العمود حتى لو لم تكن هناك بيانات حالياً
            $table->foreignId('case_profile_id')
                ->after('id')
                ->nullable() 
                ->constrained()
                ->nullOnDelete(); 
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            //
        });
    }
};
