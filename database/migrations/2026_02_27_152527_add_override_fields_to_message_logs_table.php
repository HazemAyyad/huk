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
        Schema::table('message_logs', function (Blueprint $table) {
            $table->boolean('is_override')->default(false);
            $table->unsignedBigInteger('override_by')->nullable();
        });
    }

    public function down()
    {
        Schema::table('message_logs', function (Blueprint $table) {
            $table->dropColumn(['is_override', 'override_by']);
        });
    }
};
