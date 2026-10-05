<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // رسالة الدعوة
            $table->text('invitation_message')
                  ->nullable()
                  ->after('default_message');

            // تحديد هل الفعالية أرشيف
            $table->boolean('is_archived')
                  ->default(false)
                  ->after('location');
            
            $table->boolean('is_fixed')
                  ->default(false)
                  ->after('is_archived');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['invitation_message', 'is_archived']);
        });
    }
};