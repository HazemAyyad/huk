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
        Schema::table('message_logs', function (Blueprint $table) {

            // احذف اسم الفاعلية النصي
            if (Schema::hasColumn('message_logs', 'event_name')) {
                $table->dropColumn('event_name');
            }

            // أضف الربط بالفاعلية
            $table->foreignId('event_id')
                ->nullable()
                ->after('telegram_user_id')
                ->constrained('events')
                ->nullOnDelete();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
