<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('religious_accounts', function (Blueprint $table): void {
            $table->string('username', 30)->nullable()->unique();
            $table->string('google_subject', 255)->nullable()->unique();
            $table->boolean('password_enabled')->default(true);
        });
        Schema::create('religious_password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('religious_password_reset_tokens');
        Schema::table('religious_accounts', function (Blueprint $table): void {
            $table->dropUnique(['username']);
            $table->dropUnique(['google_subject']);
            $table->dropColumn(['username', 'google_subject', 'password_enabled']);
        });
    }
};
