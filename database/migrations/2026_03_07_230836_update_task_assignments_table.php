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
        Schema::table('task_assignments', function (Blueprint $table) {

            // ✅ إضافة العمود الجديد بدون الاعتماد على user_id
            $table->unsignedBigInteger('telegram_user_id')->nullable()->after('event_id');

            // ✅ ربط المفتاح الخارجي مع جدول telegram_users
            $table->foreign('telegram_user_id')
                ->references('id')
                ->on('telegram_users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_assignments', function (Blueprint $table) {
            $table->dropForeign(['telegram_user_id']);
            $table->dropColumn('telegram_user_id');
        });
    }
};