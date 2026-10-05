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
        Schema::table('orphan_children', function (Blueprint $table) {
            // تم تغيير الاسم إلى personal_photo
            $table->string('personal_photo')->nullable()->after('gender');
        });
    }

    public function down(): void
    {
        Schema::table('orphan_children', function (Blueprint $table) {
            // تم تغيير الاسم هنا أيضاً للحذف
            $table->dropColumn('personal_photo');
        });
    }
};
