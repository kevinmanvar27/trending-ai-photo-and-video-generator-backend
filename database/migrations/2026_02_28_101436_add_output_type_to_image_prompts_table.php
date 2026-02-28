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
        Schema::table('image_prompts', function (Blueprint $table) {
            $table->string('output_type')->default('image')->after('file_type')->comment('Type of output: image or video');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('image_prompts', function (Blueprint $table) {
            $table->dropColumn('output_type');
        });
    }
};
