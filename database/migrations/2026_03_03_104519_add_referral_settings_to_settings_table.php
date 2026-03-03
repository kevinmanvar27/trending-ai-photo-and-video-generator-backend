<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert default referral settings
        DB::table('settings')->insert([
            [
                'key' => 'referral_coins_per_referral',
                'value' => '100',
                'type' => 'number',
                'group' => 'referral',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'referral_bonus_for_new_user',
                'value' => '50',
                'type' => 'number',
                'group' => 'referral',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'referral_system_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'referral',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove referral settings
        DB::table('settings')->whereIn('key', [
            'referral_coins_per_referral',
            'referral_bonus_for_new_user',
            'referral_system_enabled',
        ])->delete();
    }
};
