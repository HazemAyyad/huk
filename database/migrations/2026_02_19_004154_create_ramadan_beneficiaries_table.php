<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ramadan_beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('national_id')->unique();
            $table->string('code')->nullable();
            $table->integer('family_members_count')->default(1);
            $table->integer('daily_meals'); // عدد الوجبات اليومية
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ramadan_beneficiaries');
    }
};
