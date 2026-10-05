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
            $table->unsignedInteger('spam_count')->default(0)->after('blocked_reason');
            $table->timestamp('last_message_at')->nullable()->after('spam_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telegram_users', function (Blueprint $table) {
            // التراجع عن الإضافة بحذف الأعمدة في حال عمل Rollback
            $table->dropColumn(['spam_count', 'last_message_at']);
        });
    }
};
