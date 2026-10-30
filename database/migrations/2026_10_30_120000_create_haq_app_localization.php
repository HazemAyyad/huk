<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Aggregate identity, without moving credentials or merging equal emails.
        Schema::create('app_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('religious_account_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->boolean('association_enabled')->default(false);
            $table->boolean('religious_enabled')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('app_admin_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_account_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('active')->default(true);
            $table->json('permissions');
            $table->json('locales'); // [] means all supported locales.
            $table->timestamps();
        });
        Schema::create('app_languages', function (Blueprint $table) {
            $table->string('locale', 30)->primary();
            $table->boolean('enabled')->default(false);
            $table->unsignedBigInteger('revision')->default(1);
            $table->unsignedBigInteger('release_id')->nullable();
            $table->timestamps();
        });
        Schema::create('app_translation_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 30);
            $table->string('resource_key', 190);
            $table->json('value');
            $table->string('status', 20)->default('draft');
            $table->unsignedBigInteger('revision')->default(1);
            $table->unsignedBigInteger('editor_id');
            $table->unsignedBigInteger('reviewer_id')->nullable();
            $table->timestamps();
            $table->unique(['locale', 'resource_key']);
            $table->foreign('locale')->references('locale')->on('app_languages');
        });
        Schema::create('app_content_releases', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 30);
            $table->longText('payload'); // Immutable exact UTF-8 bytes, signed at publication.
            $table->string('sha256', 64);
            $table->string('key_id', 80);
            $table->text('signature');
            $table->unsignedBigInteger('publisher_id');
            $table->timestamp('created_at');
        });
        Schema::create('app_admin_audit', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('action', 80);
            $table->string('subject', 190);
            $table->json('details');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        foreach (['app_admin_audit', 'app_content_releases', 'app_translation_drafts', 'app_languages', 'app_admin_memberships', 'app_accounts'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
