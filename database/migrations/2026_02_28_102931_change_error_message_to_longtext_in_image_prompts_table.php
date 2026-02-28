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
            // Change error_message from TEXT to LONGTEXT to handle large error messages
            $table->longText('error_message')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('image_prompts', function (Blueprint $table) {
            // Revert back to TEXT
            $table->text('error_message')->nullable()->change();
        });
    }
};
