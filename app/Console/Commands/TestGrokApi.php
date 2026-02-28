<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestGrokApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grok:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Grok Vision API integration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Grok Vision API Test ===');
        $this->newLine();

        // Get configuration
        $apiKey = config('image-prompt.grok.api_key');
        $model = config('image-prompt.grok.model');
        $apiUrl = config('image-prompt.grok.api_url');
        $maxTokens = config('image-prompt.grok.max_tokens');
        $timeout = config('image-prompt.grok.timeout');

        // Validate API key
        if (!$apiKey) {
            $this->error('GROK_API_KEY not found in configuration');
            return 1;
        }

        $this->line("✓ API Key found: " . substr($apiKey, 0, 10) . "...");
        $this->line("✓ Model: {$model}");
        $this->line("✓ API URL: {$apiUrl}");
        $this->newLine();

        // Create a simple test image (1x1 red pixel PNG)
        $testImageBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFBQIAX8jx0gAAAABJRU5ErkJggg==';

        $this->info('Testing API connection...');

        // Test prompt
        $prompt = "Describe this image in one sentence.";

        try {
            // Make request to Grok API (OpenAI-compatible format)
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
                                    'text' => $prompt
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
                    'max_tokens' => (int) $maxTokens,
                    'temperature' => 0.7,
                ]);

            $this->newLine();
            $this->line("API Response Status: " . $response->status());

            if ($response->successful()) {
                $result = $response->json();
                
                $this->newLine();
                $this->info('✓ SUCCESS! API is working correctly.');
                $this->newLine();
                
                // Extract the text response (OpenAI-compatible format)
                if (isset($result['choices'][0]['message']['content'])) {
                    $text = $result['choices'][0]['message']['content'];
                    $this->line("Extracted Text Response:");
                    $this->line("─────────────────────────");
                    $this->line($text);
                    $this->line("─────────────────────────");
                } else {
                    $this->warn('Unexpected response structure');
                    $this->line(json_encode($result, JSON_PRETTY_PRINT));
                }
                
                return 0;
                
            } else {
                $this->newLine();
                $this->error('✗ FAILED! API returned error.');
                $this->newLine();
                $this->line("Response Body:");
                $this->line($response->body());
                return 1;
            }

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('✗ EXCEPTION: ' . $e->getMessage());
            return 1;
        }
    }
}
