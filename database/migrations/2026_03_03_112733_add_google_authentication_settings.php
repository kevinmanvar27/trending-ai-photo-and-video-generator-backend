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
        // Insert default Google authentication settings
        Setting::updateOrCreate(
            ['key' => 'google_client_id'],
            [
                'value' => '',
                'type' => 'text',
                'group' => 'authentication'
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'google_login_enabled'],
            [
                'value' => '0',
                'type' => 'boolean',
                'group' => 'authentication'
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove Google authentication settings
        Setting::where('key', 'google_client_id')->delete();
        Setting::where('key', 'google_login_enabled')->delete();
    }
};
