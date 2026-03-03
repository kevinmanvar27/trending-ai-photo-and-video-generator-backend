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
        Schema::table('subscription_plans', function (Blueprint $table) {
            // Remove duration fields (coins field already exists)
            $table->dropColumn(['duration_type', 'duration_value']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            // Add back duration fields
            $table->enum('duration_type', ['daily', 'weekly', 'monthly', 'yearly'])->after('price');
            $table->integer('duration_value')->default(1)->after('duration_type');
        });
    }
};
