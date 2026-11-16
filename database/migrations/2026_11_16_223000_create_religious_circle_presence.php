<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('religious_circle_presence', function (Blueprint $table): void {
            $table->foreignId('circle_id')->constrained('religious_circles')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('religious_accounts')->cascadeOnDelete();
            $table->uuid('session_id');
            $table->timestamp('expires_at')->index();
            $table->primary(['circle_id', 'account_id', 'session_id'], 'religious_circle_presence_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('religious_circle_presence');
    }
};
