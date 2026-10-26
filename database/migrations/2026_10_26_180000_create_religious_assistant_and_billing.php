<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('religious_billing_accounts', function (Blueprint $t): void {
            $t->foreignId('account_id')->primary()->constrained('religious_accounts')->cascadeOnDelete();
            $t->uuid('store_token')->unique();
        });
        Schema::create('religious_subscriptions', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('account_id')->constrained('religious_accounts')->cascadeOnDelete();
            $t->string('platform', 10);
            $t->string('product', 200);
            $t->string('purchase_hash', 64)->unique();
            $t->string('original_hash', 64)->nullable()->index();
            $t->text('proof'); // Encrypted with the application's key; never sent in profile responses.
            $t->string('state', 30);
            $t->boolean('active')->default(false);
            $t->timestamp('expires_at')->nullable();
            $t->timestamp('verified_at');
            $t->timestamps();
            $t->index(['account_id', 'active']);
        });
        Schema::create('religious_assistant_budgets', function (Blueprint $t): void {
            $t->string('month', 7)->primary();
            $t->unsignedBigInteger('reserved')->default(0);
            $t->unsignedBigInteger('spent')->default(0);
            $t->boolean('halted')->default(false);
        });
        Schema::create('religious_assistant_requests', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('account_id')->constrained('religious_accounts')->cascadeOnDelete();
            $t->uuid('operation_id');
            $t->string('question_hash', 64);
            $t->text('question')->nullable(); // Encrypted, and erased when its owner deletes the history row.
            $t->text('answer')->nullable();
            $t->text('sources');
            $t->string('month', 7);
            $t->string('day', 10);
            $t->string('status', 20);
            $t->string('error_code', 40)->nullable();
            $t->unsignedInteger('reserved');
            $t->unsignedInteger('cost')->nullable();
            $t->unsignedInteger('input_tokens')->nullable();
            $t->unsignedInteger('output_tokens')->nullable();
            $t->boolean('hidden')->default(false);
            $t->timestamps();
            $t->unique(['account_id', 'operation_id'], 'religious_assistant_receipt');
            $t->index(['account_id', 'month', 'status'], 'religious_assistant_quota');
        });
        Schema::create('religious_billing_events', function (Blueprint $t): void {
            $t->string('id', 100)->primary();
            $t->timestamp('created_at');
        });
    }

    public function down(): void
    {
        foreach (['religious_billing_events', 'religious_assistant_requests', 'religious_assistant_budgets', 'religious_subscriptions', 'religious_billing_accounts'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
