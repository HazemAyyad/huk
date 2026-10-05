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
        Schema::table('telegram_users', function (Blueprint $table) {
            // 1. جعل chat_id يقبل قيمة فارغة
            $table->string('chat_id')->nullable()->change();

            // 2. إضافة حقل المصدر (بشكل افتراضي يكون 'system')
            // استخدمنا enum لضمان عدم دخول قيم عشوائية
            $table->enum('source', ['bot', 'system'])->default('bot')->after('business_connection_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telegram_users', function (Blueprint $table) {
            $table->string('chat_id')->nullable(false)->change();
            $table->dropColumn('source');
        });
    }
};
