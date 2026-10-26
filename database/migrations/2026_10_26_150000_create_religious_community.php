<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('religious_community_accounts', function (Blueprint $t): void {
            $t->foreignId('account_id')->primary()->constrained('religious_accounts')->cascadeOnDelete();
            $t->boolean('suspended')->default(false);
        });
        Schema::create('religious_circles', function (Blueprint $t): void {
            $t->id();
            $t->string('title', 100);
            $t->string('content_id', 120);
            $t->unsignedBigInteger('goal');
            $t->boolean('published')->default(false);
            $t->timestamps();
        });
        Schema::create('religious_circle_members', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('circle_id')->constrained('religious_circles')->cascadeOnDelete();
            $t->foreignId('account_id')->constrained('religious_accounts')->cascadeOnDelete();
            $t->boolean('active')->default(true);
            $t->unique(['circle_id', 'account_id']);
            $t->timestamps();
        });
        Schema::create('religious_circle_counts', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('circle_id')->constrained('religious_circles')->cascadeOnDelete();
            $t->foreignId('account_id')->constrained('religious_accounts')->cascadeOnDelete();
            $t->uuid('operation_id');
            $t->unsignedInteger('amount');
            $t->timestamp('created_at');
            $t->unique(['account_id', 'operation_id'], 'religious_circle_receipt');
        });
        Schema::create('religious_community_entries', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('account_id')->constrained('religious_accounts')->cascadeOnDelete();
            $t->foreignId('parent_id')->nullable()->constrained('religious_community_entries')->cascadeOnDelete();
            $t->uuid('operation_id');
            $t->string('body', 720);
            $t->string('status', 12)->default('pending');
            $t->timestamps();
            $t->unique(['account_id', 'operation_id'], 'religious_entry_receipt');
            $t->index(['parent_id', 'status', 'id'], 'religious_entry_feed');
        });
        Schema::create('religious_community_likes', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('entry_id')->constrained('religious_community_entries')->cascadeOnDelete();
            $t->foreignId('account_id')->constrained('religious_accounts')->cascadeOnDelete();
            $t->unique(['entry_id', 'account_id']);
        });
        Schema::create('religious_community_blocks', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('account_id')->constrained('religious_accounts')->cascadeOnDelete();
            $t->foreignId('blocked_id')->constrained('religious_accounts')->cascadeOnDelete();
            $t->unique(['account_id', 'blocked_id']);
        });
        Schema::create('religious_community_reports', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('account_id')->constrained('religious_accounts')->cascadeOnDelete();
            $t->string('target_type', 10);
            $t->unsignedBigInteger('target_id');
            $t->string('reason', 180);
            $t->string('status', 12)->default('open');
            $t->timestamps();
            $t->unique(['account_id', 'target_type', 'target_id'], 'religious_report_once');
        });
        Schema::create('religious_publications', function (Blueprint $t): void {
            $t->id();
            $t->string('channel', 10);
            $t->string('kind', 10);
            $t->string('title', 100);
            $t->text('body')->nullable();
            $t->string('content_id', 120)->nullable();
            $t->string('media_path')->nullable();
            $t->string('youtube_id', 11)->nullable();
            $t->string('link', 2048)->nullable();
            $t->string('status', 12)->default('draft');
            $t->timestamp('publish_at')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
            $t->index(['channel', 'status', 'publish_at']);
        });
    }

    public function down(): void
    {
        foreach (['religious_publications', 'religious_community_reports', 'religious_community_blocks', 'religious_community_likes', 'religious_community_entries', 'religious_circle_counts', 'religious_circle_members', 'religious_circles', 'religious_community_accounts'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
