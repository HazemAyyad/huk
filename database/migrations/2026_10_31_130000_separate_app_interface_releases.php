<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_languages', function (Blueprint $table) {
            $table->unsignedBigInteger('ui_release_id')->nullable();
        });
        // Every release supported before this migration included the interface.
        // Keep its exact immutable artifact and current publication state.
        DB::table('app_languages')->whereNotNull('release_id')
            ->update(['ui_release_id' => DB::raw('release_id')]);
    }

    public function down(): void
    {
        Schema::table('app_languages', function (Blueprint $table) {
            $table->dropColumn('ui_release_id');
        });
    }
};
