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
            $table->string('output_type')->default('image')->after('processed_image_path');
            $table->longText('error_message')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_image_submissions', function (Blueprint $table) {
            $table->dropColumn('output_type');
            $table->text('error_message')->nullable()->change();
        });
    }
};
