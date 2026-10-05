<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('amputation_detail_cases', function (Blueprint $table) {
            $table->decimal('rent_value', 10, 2)->nullable();
            $table->boolean('needs_companion')->default(false);

            $table->string('companion_full_name')->nullable();
            $table->string('companion_national_id')->nullable();
            $table->date('companion_birth_date')->nullable();
            $table->string('companion_relationship')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('amputation_detail_cases', function (Blueprint $table) {
            $table->dropColumn([
                'rent_value',
                'needs_companion',
                'companion_full_name',
                'companion_national_id',
                'companion_birth_date',
                'companion_relationship',
            ]);
        });
    }
};
