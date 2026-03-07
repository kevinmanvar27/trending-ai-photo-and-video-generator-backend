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
        Schema::table('user_image_submissions', function (Blueprint $table) {
            $table->integer('coins_used')->default(0)->after('processing_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_image_submissions', function (Blueprint $table) {
            $table->dropColumn('coins_used');
        });
    }
};
