<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_source_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->nullable()->constrained('app_accounts')->nullOnDelete();
            $table->uuid('request_id');
            $table->string('locale', 30);
            $table->string('provider_key', 80);
            $table->string('status', 20)->default('queued');
            $table->unsignedInteger('revision')->default(1);
            $table->longText('artifact')->nullable();
            $table->string('sha256', 64)->nullable();
            $table->json('summary')->nullable();
            $table->string('error_code', 80)->nullable();
            $table->unsignedBigInteger('reviewer_id')->nullable();
            $table->timestamps();
            $table->unique(['account_id', 'request_id']);
            $table->index(['locale', 'status']);
            $table->foreign('locale')->references('locale')->on('app_languages');
        });
        Schema::table('app_languages', function (Blueprint $table) {
            $table->unsignedBigInteger('source_import_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('app_languages', fn (Blueprint $table) => $table->dropColumn('source_import_id'));
        Schema::dropIfExists('app_source_imports');
    }
};
