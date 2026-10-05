<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ramadan_beneficiaries', function (Blueprint $table) {

            // فقط تعديل nullable بدون إعادة تعريف unique
            $table->string('full_name')->nullable()->change();
            $table->string('national_id')->nullable()->change();
            $table->string('code')->nullable()->change();
            $table->integer('family_members_count')->nullable()->default(1)->change();
            $table->integer('daily_meals')->nullable()->change();

        });
    }

    public function down()
    {
        Schema::table('ramadan_beneficiaries', function (Blueprint $table) {

            $table->string('full_name')->nullable(false)->change();
            $table->string('national_id')->nullable(false)->change();
            $table->string('code')->nullable()->change();
            $table->integer('family_members_count')->default(1)->change();
            $table->integer('daily_meals')->nullable(false)->change();

        });
    }
};