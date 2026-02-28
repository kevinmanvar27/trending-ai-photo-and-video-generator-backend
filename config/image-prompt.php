<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Grok (xAI) API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Grok API settings here. You can get an API key from
    | https://console.x.ai/
    |
    | Grok is used for intelligent image analysis and transformation guidance.
    | The system combines Grok's AI with PHP image processing for actual modifications.
    |
    */

    'grok' => [
        'api_key' => 'xai-kPc611NdCJlutFkMIJZpQ3NtNRyzfGlWTfEF0FovCOBe9T4yjb8ugNfS1SLyqdthH8AFB8tWCF41nf9P',
        
        // Grok Vision API (for image analysis)
        'vision_api_url' => env('GROK_VISION_API_URL', 'https://api.x.ai/v1/chat/completions'),
        'vision_model' => env('GROK_VISION_MODEL', 'grok-2-vision-1212'),
        
        // Grok Imagine API (for image generation)
        'imagine_api_url' => env('GROK_IMAGINE_API_URL', 'https://api.x.ai/v1/images/generations'),
        'imagine_model' => env('GROK_IMAGINE_MODEL', 'grok-imagine-image'),
        'imagine_size' => env('GROK_IMAGINE_SIZE', '1024x1024'),
        'imagine_quality' => 'high',
        
        // Grok Video API (for video generation from images)
        // NOTE: This API is experimental and may require specific image URL formats
        // See VIDEO_API_STATUS.md for current status and troubleshooting
        'video_api_url' => env('GROK_VIDEO_API_URL', 'https://api.x.ai/v1/videos/generations'),
        'video_model' => env('GROK_VIDEO_MODEL', 'grok-imagine-video'),
        'video_duration' => env('GROK_VIDEO_DURATION', 5), // seconds
        'video_fps' => env('GROK_VIDEO_FPS', 24),
        
        'max_tokens' => (int) env('GROK_MAX_TOKENS', 2000),
        'timeout' => (int) env('GROK_TIMEOUT', 180), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your OpenAI API settings here. You can get an API key from
    | https://platform.openai.com/api-keys
    |
    | OpenAI is used for generating transformed images using DALL-E 3.
    |
    */

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'dall-e-3'),
        'size' => env('OPENAI_IMAGE_SIZE', '1024x1024'),
        'quality' => env('OPENAI_IMAGE_QUALITY', 'standard'), // standard or hd
        'timeout' => (int) env('OPENAI_TIMEOUT', 120), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Configure file upload settings including allowed types and size limits.
    |
    */

    'upload' => [
        'max_size' => (int) env('IMAGE_PROMPT_MAX_SIZE', 51200), // KB (50MB default)
        'allowed_image_types' => ['jpeg', 'jpg', 'png', 'gif'],
        'allowed_video_types' => ['mp4', 'mov', 'avi'],
        'storage_disk' => 'public',
        'storage_path' => 'image-prompts',
    ],

    /*
    |--------------------------------------------------------------------------
    | Processing Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how images are processed.
    |
    */

    'processing' => [
        'use_queue' => env('IMAGE_PROMPT_USE_QUEUE', false),
        'queue_name' => env('IMAGE_PROMPT_QUEUE', 'default'),
        'retry_attempts' => (int) env('IMAGE_PROMPT_RETRY_ATTEMPTS', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific features.
    |
    */

    'features' => [
        'allow_video_upload' => env('IMAGE_PROMPT_ALLOW_VIDEO', true),
        'auto_delete_after_days' => env('IMAGE_PROMPT_AUTO_DELETE_DAYS', null) ? (int) env('IMAGE_PROMPT_AUTO_DELETE_DAYS', null) : null, // null = never
        'enable_public_access' => env('IMAGE_PROMPT_PUBLIC_ACCESS', false),
    ],

];
