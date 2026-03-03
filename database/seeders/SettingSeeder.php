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
            
            // API Settings
            [
                'key' => 'grok_api_key',
                'value' => '',
                'type' => 'password',
                'group' => 'api'
            ],
            [
                'key' => 'grok_vision_api_url',
                'value' => 'https://api.x.ai/v1/chat/completions',
                'type' => 'text',
                'group' => 'api'
            ],
            [
                'key' => 'grok_vision_model',
                'value' => 'grok-3',
                'type' => 'text',
                'group' => 'api'
            ],
            [
                'key' => 'grok_imagine_api_url',
                'value' => 'https://api.x.ai/v1/images/generations',
                'type' => 'text',
                'group' => 'api'
            ],
            [
                'key' => 'grok_imagine_model',
                'value' => 'grok-imagine-image',
                'type' => 'text',
                'group' => 'api'
            ],
            [
                'key' => 'grok_imagine_size',
                'value' => '1024x1024',
                'type' => 'text',
                'group' => 'api'
            ],
            [
                'key' => 'grok_imagine_quality',
                'value' => 'high',
                'type' => 'text',
                'group' => 'api'
            ],
            [
                'key' => 'grok_video_api_url',
                'value' => 'https://api.x.ai/v1/videos/generations',
                'type' => 'text',
                'group' => 'api'
            ],
            [
                'key' => 'grok_video_model',
                'value' => 'grok-imagine-video',
                'type' => 'text',
                'group' => 'api'
            ],
            [
                'key' => 'grok_video_duration',
                'value' => '5',
                'type' => 'number',
                'group' => 'api'
            ],
            [
                'key' => 'grok_video_fps',
                'value' => '24',
                'type' => 'number',
                'group' => 'api'
            ],
            [
                'key' => 'grok_max_tokens',
                'value' => '2000',
                'type' => 'number',
                'group' => 'api'
            ],
            [
                'key' => 'grok_timeout',
                'value' => '180',
                'type' => 'number',
                'group' => 'api'
            ],
            
            // Footer Settings
            [
                'key' => 'footer_description',
                'value' => 'Transform your media with AI-powered effects',
                'type' => 'textarea',
                'group' => 'footer'
            ],
            [
                'key' => 'footer_email',
                'value' => 'support@example.com',
                'type' => 'text',
                'group' => 'footer'
            ],
            [
                'key' => 'footer_phone',
                'value' => '+1 (555) 123-4567',
                'type' => 'text',
                'group' => 'footer'
            ],
            [
                'key' => 'footer_address',
                'value' => '123 AI Street, Tech City, TC 12345',
                'type' => 'textarea',
                'group' => 'footer'
            ],
            [
                'key' => 'footer_facebook_url',
                'value' => '',
                'type' => 'url',
                'group' => 'footer'
            ],
            [
                'key' => 'footer_twitter_url',
                'value' => '',
                'type' => 'url',
                'group' => 'footer'
            ],
            [
                'key' => 'footer_instagram_url',
                'value' => '',
                'type' => 'url',
                'group' => 'footer'
            ],
            [
                'key' => 'footer_youtube_url',
                'value' => '',
                'type' => 'url',
                'group' => 'footer'
            ],
            [
                'key' => 'footer_linkedin_url',
                'value' => '',
                'type' => 'url',
                'group' => 'footer'
            ],
            [
                'key' => 'footer_tiktok_url',
                'value' => '',
                'type' => 'url',
                'group' => 'footer'
            ],
            [
                'key' => 'footer_privacy_url',
                'value' => '',
                'type' => 'url',
                'group' => 'footer'
            ],
            [
                'key' => 'footer_terms_url',
                'value' => '',
                'type' => 'url',
                'group' => 'footer'
            ],
            [
                'key' => 'footer_contact_url',
                'value' => '',
                'type' => 'url',
                'group' => 'footer'
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
