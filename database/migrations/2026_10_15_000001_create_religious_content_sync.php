<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('religious_accounts', function (Blueprint $table): void {
            $table->unsignedBigInteger('content_revision')->default(0);
        });
        Schema::create('religious_content_sync_entities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('religious_account_id')->constrained('religious_accounts')->cascadeOnDelete();
            $table->string('entity_key', 120);
            $table->json('value')->nullable();
            $table->boolean('deleted')->default(false);
            $table->unsignedBigInteger('revision');
            $table->unique(['religious_account_id', 'entity_key'], 'religious_content_owner_key');
        });
        Schema::create('religious_content_sync_operations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('religious_account_id')->constrained('religious_accounts')->cascadeOnDelete();
            $table->uuid('operation_id');
            $table->boolean('conflicted')->default(false);
            $table->unique(['religious_account_id', 'operation_id'], 'religious_content_owner_operation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('religious_content_sync_operations');
        Schema::dropIfExists('religious_content_sync_entities');
        Schema::table('religious_accounts', fn (Blueprint $table) => $table->dropColumn('content_revision'));
    }
};
