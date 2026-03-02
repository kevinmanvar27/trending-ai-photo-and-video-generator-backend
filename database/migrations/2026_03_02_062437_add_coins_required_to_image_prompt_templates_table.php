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
        Schema::table('image_prompt_templates', function (Blueprint $table) {
            $table->integer('coins_required')->default(0)->after('usage_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('image_prompt_templates', function (Blueprint $table) {
            $table->dropColumn('coins_required');
        });
    }
};
