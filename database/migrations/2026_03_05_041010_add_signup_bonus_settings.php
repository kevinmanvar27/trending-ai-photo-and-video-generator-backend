<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add signup bonus settings
        Setting::updateOrCreate(
            ['key' => 'signup_bonus_coins'],
            [
                'value' => '0',
                'type' => 'number',
                'group' => 'referral'
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'signup_bonus_enabled'],
            [
                'value' => '0',
                'type' => 'boolean',
                'group' => 'referral'
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove signup bonus settings
        Setting::where('key', 'signup_bonus_coins')->delete();
        Setting::where('key', 'signup_bonus_enabled')->delete();
    }
};
