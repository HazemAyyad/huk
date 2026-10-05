<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('message_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('attended_by')->nullable();
            $table->unsignedBigInteger('excused_by')->nullable();
        });
    }

    public function down()
    {
        Schema::table('message_logs', function (Blueprint $table) {
            $table->dropColumn(['attended_by','excused_by']);
        });
    }
};
