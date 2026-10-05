<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orphan_cases', function (Blueprint $table) {
            $table->id();

            $table->foreignId('telegram_user_id')
                  ->constrained()
                  ->onDelete('cascade');

            $table->enum('orphan_status', [
                'father',
                'mother',
                'both'
            ])->nullable();

            $table->string('death_certificate')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orphan_cases');
    }
};
