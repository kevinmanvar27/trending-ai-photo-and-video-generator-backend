<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'site_title',
                'value' => 'Radhika Admin Panel',
                'type' => 'text',
                'group' => 'general'
            ],
            [
                'key' => 'site_description',
                'value' => 'Professional subscription management system',
                'type' => 'textarea',
                'group' => 'general'
            ],
            [
                'key' => 'contact_email',
                'value' => 'admin@radhika.com',
                'type' => 'text',
                'group' => 'general'
            ],
            [
                'key' => 'contact_phone',
                'value' => '',
                'type' => 'text',
                'group' => 'general'
            ],
            [
                'key' => 'address',
                'value' => '',
                'type' => 'textarea',
                'group' => 'general'
            ],
            
            // Payment Settings
            [
                'key' => 'razorpay_enabled',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'payment'
            ],
            [
                'key' => 'razorpay_key',
                'value' => '',
                'type' => 'text',
                'group' => 'payment'
            ],
            [
                'key' => 'razorpay_secret',
                'value' => '',
                'type' => 'password',
                'group' => 'payment'
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
