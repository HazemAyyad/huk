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
        Schema::table('telegram_users', function (Blueprint $table) {
            $table->string('disease_type')->nullable()->after('amputation_type');
        });
    }

    public function down()
    {
        Schema::table('telegram_users', function (Blueprint $table) {
            $table->dropColumn('disease_type');
        });
    }

};
