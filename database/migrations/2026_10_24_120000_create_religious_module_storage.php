<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('religious_module_heads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('religious_account_id')->constrained('religious_accounts')->cascadeOnDelete();
            $table->string('stream', 16);
            $table->unsignedBigInteger('revision')->default(0);
            $table->unique(['religious_account_id', 'stream'], 'religious_module_owner_stream');
        });
        Schema::create('religious_personal_entities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('religious_account_id')->constrained('religious_accounts')->cascadeOnDelete();
            $table->string('entity_key', 64);
            $table->json('value')->nullable();
            $table->boolean('deleted')->default(false);
            $table->unsignedBigInteger('revision');
            $table->unique(['religious_account_id', 'entity_key'], 'religious_personal_owner_key');
        });
        Schema::create('religious_personal_operations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('religious_account_id')->constrained('religious_accounts')->cascadeOnDelete();
            $table->uuid('operation_id');
            $table->string('entity_key', 64);
            $table->char('payload_hash', 64);
            $table->boolean('conflicted');
            $table->unique(['religious_account_id', 'operation_id'], 'religious_personal_owner_operation');
        });
        Schema::create('religious_activity_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('religious_account_id')->constrained('religious_accounts')->cascadeOnDelete();
            $table->uuid('event_id');
            $table->uuid('device_id');
            $table->uuid('session_id');
            $table->string('subject_id', 120);
            $table->uuid('epoch');
            $table->string('kind', 16);
            $table->unsignedInteger('amount');
            $table->date('local_day');
            $table->smallInteger('utc_offset_minutes');
            $table->dateTime('occurred_at', 3);
            $table->uuid('reverses')->nullable();
            $table->json('payload');
            $table->char('payload_hash', 64);
            $table->unsignedBigInteger('revision');
            $table->unique(['religious_account_id', 'event_id'], 'religious_activity_owner_event');
            $table->unique(['religious_account_id', 'revision'], 'religious_activity_owner_revision');
            $table->unique(['religious_account_id', 'reverses'], 'religious_activity_owner_undo');
            $table->index(['religious_account_id', 'session_id', 'epoch', 'subject_id'], 'religious_activity_session');
            $table->index(['religious_account_id', 'local_day', 'kind'], 'religious_activity_day');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('religious_activity_events');
        Schema::dropIfExists('religious_personal_operations');
        Schema::dropIfExists('religious_personal_entities');
        Schema::dropIfExists('religious_module_heads');
    }
};
