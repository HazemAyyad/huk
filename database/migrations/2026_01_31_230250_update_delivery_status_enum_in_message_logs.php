<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            ALTER TABLE message_logs
            MODIFY delivery_status
            ENUM('pending','sent','failed','manual')
            NOT NULL DEFAULT 'pending'
        ");
    }

    public function down()
    {
        DB::statement("
            ALTER TABLE message_logs
            MODIFY delivery_status
            ENUM('pending','sent','failed')
            NOT NULL DEFAULT 'pending'
        ");
    }

};
