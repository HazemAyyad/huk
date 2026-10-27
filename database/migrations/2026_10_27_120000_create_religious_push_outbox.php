<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('religious_push_state', function (Blueprint $table): void {
            $table->unsignedTinyInteger('id')->primary();
            $table->timestamp('activated_at');
        });
        Schema::create('religious_push_outbox', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('publication_id')->unique()->constrained('religious_publications')->cascadeOnDelete();
            $table->string('status', 16)->default('pending')->index();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->uuid('claim')->nullable();
            $table->timestamp('available_at');
            $table->timestamp('claimed_at')->nullable();
            $table->string('provider_receipt', 255)->nullable();
            $table->string('failure', 32)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('religious_push_outbox');
        Schema::dropIfExists('religious_push_state');
    }
};
