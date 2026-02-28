<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestGeminiApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gemini:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Gemini Vision API integration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Gemini Vision API Test ===');
        $this->newLine();

        // Get configuration
        $apiKey = config('image-prompt.gemini.api_key');
        $model = config('image-prompt.gemini.model');
        $baseUrl = config('image-prompt.gemini.api_url');
        $maxTokens = config('image-prompt.gemini.max_tokens');
        $timeout = config('image-prompt.gemini.timeout');

        // Validate API key
        if (!$apiKey) {
            $this->error('GEMINI_API_KEY not found in configuration');
            return 1;
        }

        $this->line("✓ API Key found: " . substr($apiKey, 0, 10) . "...");
        $this->line("✓ Model: {$model}");
        $this->line("✓ Base URL: {$baseUrl}");
        $this->newLine();

        // Create a simple test image (1x1 red pixel PNG)
        $testImageBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFBQIAX8jx0gAAAABJRU5ErkJggg==';

        $this->info('Testing API connection...');

        // Build API URL
        $apiUrl = "{$baseUrl}/{$model}:generateContent?key={$apiKey}";

        // Test prompt
        $prompt = "Describe this image in one sentence.";

        try {
            // Make request to Gemini API
            $response = Http::timeout($timeout)
                ->post($apiUrl, [
                    'contents' => [[
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => 'image/png',
                                    'data' => $testImageBase64
                                ]
                            ]
                        ]
                    ]],
                    'generationConfig' => [
                        'maxOutputTokens' => $maxTokens
                    ]
                ]);

            $this->newLine();
            $this->line("API Response Status: " . $response->status());

            if ($response->successful()) {
                $result = $response->json();
                
                $this->newLine();
                $this->info('✓ SUCCESS! API is working correctly.');
                $this->newLine();
                
                // Extract the text response
                if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                    $text = $result['candidates'][0]['content']['parts'][0]['text'];
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
