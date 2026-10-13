<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('religious_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email')->unique();
            $table->string('password');
            $table->unsignedBigInteger('quran_revision')->default(0);
            $table->timestamps();
        });
        Schema::create('quran_sync_entities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('religious_account_id')->constrained()->cascadeOnDelete();
            $table->string('entity_key', 80);
            $table->json('value')->nullable();
            $table->boolean('deleted')->default(false);
            $table->unsignedBigInteger('revision');
            $table->unique(['religious_account_id', 'entity_key']);
        });
        Schema::create('quran_sync_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('religious_account_id')->constrained()->cascadeOnDelete();
            $table->uuid('operation_id');
            $table->boolean('conflicted')->default(false);
            $table->unique(['religious_account_id', 'operation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quran_sync_operations');
        Schema::dropIfExists('quran_sync_entities');
        Schema::dropIfExists('religious_accounts');
    }
};
