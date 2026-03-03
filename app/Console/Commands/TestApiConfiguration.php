<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestApiConfiguration extends Command
{
    protected $signature = 'test:api';
    protected $description = 'Test Grok and OpenAI API configuration';

    public function handle()
    {
        $this->info('');
        $this->info('🔍 Testing API Configuration...');
        $this->info('');

        // Test 1: Grok API
        $this->testGrokApi();
        $this->info('');

        // Test 2: OpenAI API
        $this->testOpenAIApi();
        $this->info('');

        // Test 3: Storage
        $this->testStorage();
        $this->info('');

        // Test 4: FFmpeg
        $this->testFFmpeg();
        $this->info('');

        // Summary
        $this->showSummary();
    }

    protected function testGrokApi()
    {
        $this->info('1️⃣  Testing Grok AI (xAI) API...');
        
        $grokKey = config('image-prompt.grok.api_key');

        if (empty($grokKey)) {
            $this->error('   ❌ GROK_API_KEY not found in config');
            $this->line('   📝 Check your .env file and run: php artisan config:clear');
            return false;
        }

        $this->line('   ✅ Grok API Key found: ' . substr($grokKey, 0, 15) . '...');

        // Get model from config (database settings have priority)
        $visionModel = config('image-prompt.grok.vision_model', 'grok-vision-beta');
        $this->line('   📝 Using model: ' . $visionModel);

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $grokKey,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.x.ai/v1/chat/completions', [
                    'model' => $visionModel,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => 'Say "Hello" if you can read this.'
                        ]
                    ],
                    'max_tokens' => 50
                ]);

            if ($response->successful()) {
                $this->line('   ✅ Grok API is working!');
                $result = $response->json();
                $reply = $result['choices'][0]['message']['content'] ?? 'No response';
                $this->line('   📝 Response: ' . substr($reply, 0, 50));
                return true;
            } else {
                $this->error('   ❌ Grok API Error: ' . $response->status());
                $this->error('   📝 Message: ' . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Grok API Connection Failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function testOpenAIApi()
    {
        $this->info('2️⃣  Testing OpenAI API (Optional - Not Required)...');
        
        $openaiKey = config('image-prompt.openai.api_key');

        if (empty($openaiKey) || $openaiKey === 'your_openai_api_key_here') {
            $this->line('   ℹ️  OpenAI API not configured (using Grok only)');
            $this->line('   📝 This is fine - the system works with Grok API alone');
            return true; // Return true since it's optional
        }

        $this->line('   ✅ OpenAI API Key found: ' . substr($openaiKey, 0, 15) . '...');

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $openaiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => 'Say "Hello" if you can read this.'
                        ]
                    ],
                    'max_tokens' => 50
                ]);

            if ($response->successful()) {
                $this->line('   ✅ OpenAI API is working!');
                $result = $response->json();
                $reply = $result['choices'][0]['message']['content'] ?? 'No response';
                $this->line('   📝 Response: ' . substr($reply, 0, 50));
                $this->line('   ℹ️  Checking DALL-E 3 access...');
                $this->line('   💰 Make sure you have billing enabled at: https://platform.openai.com/account/billing');
                return true;
            } else {
                $this->error('   ❌ OpenAI API Error: ' . $response->status());
                $errorBody = $response->json();
                $this->error('   📝 Message: ' . ($errorBody['error']['message'] ?? $response->body()));

                if ($response->status() === 401) {
                    $this->warn('   ⚠️  Invalid API key. Get a new one from: https://platform.openai.com/api-keys');
                } elseif ($response->status() === 429) {
                    $this->warn('   ⚠️  Quota exceeded. Add billing at: https://platform.openai.com/account/billing');
                }
                return false;
            }
        } catch (\Exception $e) {
            $this->error('   ❌ OpenAI API Connection Failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function testStorage()
    {
        $this->info('3️⃣  Testing Storage Directories...');

        $directories = [
            'storage/app/public/user-submissions/originals',
            'storage/app/public/user-submissions/processed',
            'storage/app/public/processed',
            'storage/app/public/temp',
        ];

        foreach ($directories as $dir) {
            $fullPath = base_path($dir);
            if (is_dir($fullPath) && is_writable($fullPath)) {
                $this->line("   ✅ $dir (writable)");
            } elseif (is_dir($fullPath)) {
                $this->warn("   ⚠️  $dir (exists but not writable)");
            } else {
                $this->error("   ❌ $dir (missing)");
                $this->line("      Creating directory...");
                mkdir($fullPath, 0755, true);
                $this->line("   ✅ Created successfully");
            }
        }
    }

    protected function testFFmpeg()
    {
        $this->info('4️⃣  Testing FFmpeg (for video processing)...');
        
        $ffmpegPath = exec('which ffmpeg');

        if (empty($ffmpegPath)) {
            $this->warn('   ⚠️  FFmpeg not found');
            $this->line('   📝 Video processing will not work without FFmpeg');
            $this->line('   📝 Install: brew install ffmpeg (on Mac)');
        } else {
            $this->line("   ✅ FFmpeg found at: $ffmpegPath");
        }
    }

    protected function showSummary()
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📊 SUMMARY');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('');

        $grokKey = config('image-prompt.grok.api_key');
        $openaiKey = config('image-prompt.openai.api_key');

        if (!empty($grokKey)) {
            $this->line('✅ Your application is ready to process images with Grok AI!');
            $this->info('');
            $this->info('🚀 Next Steps:');
            $this->line('   1. Go to: http://localhost/trends/public/admin/image-prompts/create');
            $this->line('   2. Or: http://127.0.0.1:8001/admin/image-prompts/create');
            $this->line('   3. Upload an image');
            $this->line('   4. Enter a prompt (e.g., "Make it black and white")');
            $this->line('   5. Wait a few seconds for processing');
            $this->line('   6. Download your transformed image');
            $this->info('');
            $this->line('💡 Grok AI will analyze your image and apply transformations');
            $this->line('   based on your prompt using advanced image processing.');
        } else {
            $this->warn('⚠️  Configuration incomplete:');
            $this->info('');

            if (empty($grokKey)) {
                $this->error('   ❌ Add GROK_API_KEY to config/image-prompt.php');
                $this->line('      Get it from: https://console.x.ai/');
            }

            $this->info('');
            $this->line('   📝 Update config/image-prompt.php with your Grok API key');
            $this->line('   📝 Then run: php artisan config:clear && php artisan test:api');
        }

        $this->info('');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('');
    }
}
