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
        Schema::table('telegram_users', function (Illuminate\Database\Schema\Blueprint $table) {
            // إضافة الحقل الجديد
            $table->boolean('is_processing')->default(false)->after('is_completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telegram_users', function (Illuminate\Database\Schema\Blueprint $table) {
            // حذف الحقل في حال التراجع عن العملية
            $table->dropColumn('is_processing');
        });
    }
};
