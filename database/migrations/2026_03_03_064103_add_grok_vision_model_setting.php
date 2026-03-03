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
        // Add grok_vision_model setting with correct default value
        DB::table('settings')->insert([
            'key' => 'grok_vision_model',
            'value' => 'grok-3',
            'type' => 'text',
            'group' => 'api',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Update any existing incorrect model names to grok-3
        DB::table('settings')
            ->where('key', 'grok_vision_model')
            ->whereIn('value', ['grok-vision-beta', 'grok-2-vision-1212', 'grok-beta'])
            ->update([
                'value' => 'grok-3',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the grok_vision_model setting
        DB::table('settings')->where('key', 'grok_vision_model')->delete();
    }
};
