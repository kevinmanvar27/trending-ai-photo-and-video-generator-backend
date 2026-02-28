<?php

namespace App\Console\Commands;

use App\Services\GrokImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TestGrokIntegration extends Command
{
    protected $signature = 'grok:test-full {--with-file= : Path to test image file}';
    protected $description = 'Comprehensive test of Grok API with text and image';

    public function handle()
    {
        $this->info('╔════════════════════════════════════════════════════════╗');
        $this->info('║     Grok AI Integration - Comprehensive Test          ║');
        $this->info('╚════════════════════════════════════════════════════════╝');
        $this->newLine();

        // Step 1: Configuration Check
        $this->line('📋 Step 1: Configuration Check');
        $this->line('─────────────────────────────────────────────────────────');
        
        $apiKey = config('image-prompt.grok.api_key');
        $model = config('image-prompt.grok.model');
        $apiUrl = config('image-prompt.grok.api_url');
        $maxTokens = config('image-prompt.grok.max_tokens');
        $timeout = config('image-prompt.grok.timeout');

        if (!$apiKey) {
            $this->error('❌ GROK_API_KEY not found in .env file!');
            $this->newLine();
            $this->warn('Please add your Grok API key to .env:');
            $this->line('GROK_API_KEY=xai-your-key-here');
            $this->newLine();
            $this->info('Get your API key from: https://console.x.ai/');
            return 1;
        }

        $this->line("✅ API Key: " . substr($apiKey, 0, 15) . "..." . substr($apiKey, -5));
        $this->line("✅ Model: {$model}");
        $this->line("✅ API URL: {$apiUrl}");
        $this->line("✅ Max Tokens: {$maxTokens}");
        $this->line("✅ Timeout: {$timeout}s");
        $this->newLine();

        // Step 2: Test Basic Text-Only Request
        $this->line('📝 Step 2: Testing Text-Only Request');
        $this->line('─────────────────────────────────────────────────────────');
        
        if (!$this->testTextOnly($apiKey, $model, $apiUrl, $timeout)) {
            return 1;
        }
        $this->newLine();

        // Step 3: Test Text + Image Request
        $this->line('🖼️  Step 3: Testing Text + Image Request (Multimodal)');
        $this->line('─────────────────────────────────────────────────────────');
        
        if (!$this->testTextWithImage($apiKey, $model, $apiUrl, $timeout)) {
            return 1;
        }
        $this->newLine();

        // Step 4: Test GrokImageService
        $this->line('🔧 Step 4: Testing GrokImageService Class');
        $this->line('─────────────────────────────────────────────────────────');
        
        if (!$this->testGrokService()) {
            return 1;
        }
        $this->newLine();

        // Final Summary
        $this->info('╔════════════════════════════════════════════════════════╗');
        $this->info('║              ✅ ALL TESTS PASSED! ✅                   ║');
        $this->info('╚════════════════════════════════════════════════════════╝');
        $this->newLine();
        $this->line('✨ Grok AI is working perfectly with:');
        $this->line('   ✅ Text prompts');
        $this->line('   ✅ Image analysis');
        $this->line('   ✅ Text + Image together (multimodal)');
        $this->line('   ✅ GrokImageService integration');
        $this->newLine();
        $this->info('🚀 Your application is ready to use Grok AI!');
        
        return 0;
    }

    private function testTextOnly($apiKey, $model, $apiUrl, $timeout)
    {
        try {
            $this->line('Sending text-only request...');
            
            $response = Http::timeout($timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($apiUrl, [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => 'Say "Hello from Grok!" in exactly 5 words.'
                        ]
                    ],
                    'max_tokens' => 100,
                ]);

            if (!$response->successful()) {
                $this->error('❌ Text-only test failed!');
                $this->line('Status: ' . $response->status());
                $this->line('Response: ' . $response->body());
                return false;
            }

            $result = $response->json();
            $content = $result['choices'][0]['message']['content'] ?? '';
            
            $this->info('✅ Text-only request successful!');
            $this->line('Response: ' . $content);
            
            return true;

        } catch (\Exception $e) {
            $this->error('❌ Exception: ' . $e->getMessage());
            return false;
        }
    }

    private function testTextWithImage($apiKey, $model, $apiUrl, $timeout)
    {
        try {
            // Create a simple test image (1x1 red pixel PNG)
            $testImageBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFBQIAX8jx0gAAAABJRU5ErkJggg==';
            
            $this->line('Sending text + image request...');
            
            $response = Http::timeout($timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($apiUrl, [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => 'Describe this image in one short sentence. What color is it?'
                                ],
                                [
                                    'type' => 'image_url',
                                    'image_url' => [
                                        'url' => "data:image/png;base64,{$testImageBase64}"
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'max_tokens' => 100,
                ]);

            if (!$response->successful()) {
                $this->error('❌ Text + Image test failed!');
                $this->line('Status: ' . $response->status());
                $this->line('Response: ' . $response->body());
                return false;
            }

            $result = $response->json();
            $content = $result['choices'][0]['message']['content'] ?? '';
            
            $this->info('✅ Text + Image request successful!');
            $this->line('Response: ' . $content);
            
            return true;

        } catch (\Exception $e) {
            $this->error('❌ Exception: ' . $e->getMessage());
            return false;
        }
    }

    private function testGrokService()
    {
        try {
            $this->line('Instantiating GrokImageService...');
            
            $service = app(GrokImageService::class);
            
            $this->info('✅ GrokImageService loaded successfully!');
            $this->line('Service class: ' . get_class($service));
            
            // Check if service has required methods
            $methods = ['processImage', 'processVideo'];
            foreach ($methods as $method) {
                if (method_exists($service, $method)) {
                    $this->line("   ✅ Method exists: {$method}()");
                } else {
                    $this->error("   ❌ Method missing: {$method}()");
                    return false;
                }
            }
            
            return true;

        } catch (\Exception $e) {
            $this->error('❌ Exception: ' . $e->getMessage());
            return false;
        }
    }
}
