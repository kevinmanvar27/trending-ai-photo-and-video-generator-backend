<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class GeminiImageService
{
    protected $apiKey;
    protected $model;
    protected $apiUrl;
    protected $timeout;

    public function __construct()
    {
        $this->apiKey = config('image-prompt.gemini.api_key');
        $this->model = config('image-prompt.gemini.model');
        $this->apiUrl = config('image-prompt.gemini.api_url');
        $this->timeout = config('image-prompt.gemini.timeout', 120);
    }

    /**
     * Process image with Gemini and apply transformations
     */
    public function processImage($imagePath, $prompt)
    {
        if (!$this->apiKey) {
            throw new \Exception('Gemini API key not configured. Please add GEMINI_API_KEY to your .env file.');
        }

        // Step 1: Analyze image with Gemini to understand what needs to be done
        $analysis = $this->analyzeImageWithGemini($imagePath, $prompt);

        // Step 2: Apply image transformations based on the prompt
        $processedPath = $this->applyImageTransformations($imagePath, $prompt, $analysis);

        return $processedPath;
    }

    /**
     * Analyze image with Gemini API
     */
    protected function analyzeImageWithGemini($imagePath, $prompt)
    {
        // Convert image to base64
        $imageData = base64_encode(file_get_contents($imagePath));
        $mimeType = mime_content_type($imagePath);

        // Build Gemini API endpoint
        $endpoint = "{$this->apiUrl}/{$this->model}:generateContent?key={$this->apiKey}";

        // Enhanced prompt for image transformation guidance
        $enhancedPrompt = "Based on this request: '{$prompt}', provide specific image transformation instructions. "
            . "Describe colors, filters, effects, and modifications needed. Be specific about RGB values, "
            . "brightness, contrast, saturation, and any style changes.";

        // Call Gemini API
        $response = Http::timeout($this->timeout)->post($endpoint, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $enhancedPrompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $imageData
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'maxOutputTokens' => 2000,
                'temperature' => 0.7,
            ]
        ]);

        if (!$response->successful()) {
            throw new \Exception('Gemini API request failed: ' . $response->body());
        }

        $result = $response->json();
        return $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    /**
     * Apply image transformations based on prompt keywords
     */
    protected function applyImageTransformations($imagePath, $prompt, $analysis)
    {
        // Load image using Intervention Image v3
        $manager = new ImageManager(new Driver());
        $image = $manager->read($imagePath);
        
        $promptLower = strtolower($prompt);
        $analysisLower = strtolower($analysis);

        // Apply transformations based on prompt keywords
        
        // Color transformations
        if (preg_match('/black\s+and\s+white|grayscale|monochrome/i', $promptLower)) {
            $image = $image->greyscale();
        }
        
        if (preg_match('/sepia|vintage|old\s+photo/i', $promptLower)) {
            $image = $image->greyscale();
        }
        
        if (preg_match('/blur|blurry|soft/i', $promptLower)) {
            $image = $image->blur(15);
        }
        
        if (preg_match('/sharpen|sharp|clear/i', $promptLower)) {
            $image = $image->sharpen(10);
        }
        
        if (preg_match('/bright|brighter|lighten/i', $promptLower)) {
            $image = $image->brightness(30);
        }
        
        if (preg_match('/dark|darker|dim/i', $promptLower)) {
            $image = $image->brightness(-30);
        }
        
        if (preg_match('/contrast|high\s+contrast/i', $promptLower)) {
            $image = $image->contrast(30);
        }
        
        if (preg_match('/invert|negative/i', $promptLower)) {
            $image = $image->invert();
        }
        
        if (preg_match('/flip\s+horizontal|mirror/i', $promptLower)) {
            $image = $image->flip('h');
        }
        
        if (preg_match('/flip\s+vertical|upside\s+down/i', $promptLower)) {
            $image = $image->flip('v');
        }
        
        if (preg_match('/rotate\s+90|turn\s+right/i', $promptLower)) {
            $image = $image->rotate(-90);
        }
        
        if (preg_match('/rotate\s+180/i', $promptLower)) {
            $image = $image->rotate(-180);
        }
        
        if (preg_match('/rotate\s+270|turn\s+left/i', $promptLower)) {
            $image = $image->rotate(-270);
        }
        
        if (preg_match('/pixelate|pixel\s+art/i', $promptLower)) {
            $image = $image->pixelate(12);
        }

        // Save processed image
        $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
        $fileName = Str::uuid() . '.' . $extension;
        $outputPath = storage_path('app/public/processed/' . $fileName);
        
        // Create directory if it doesn't exist
        if (!file_exists(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0755, true);
        }
        
        // Save with Intervention Image v3 syntax
        $image->save($outputPath);

        // Return the full absolute path
        return $outputPath;
    }

    /**
     * Process video with Gemini analysis
     * Extract frames, process them, and reassemble
     */
    public function processVideo($videoPath, $prompt)
    {
        // For video processing, we'll extract key frames, process them, and provide a summary
        // Full video processing requires FFmpeg which is complex
        
        // Extract first frame as thumbnail
        $thumbnailPath = $this->extractVideoThumbnail($videoPath);
        
        if ($thumbnailPath) {
            // Process the thumbnail
            $processedThumbnail = $this->processImage($thumbnailPath, $prompt);
            return $processedThumbnail;
        }
        
        throw new \Exception('Video processing requires FFmpeg. Please install FFmpeg or process videos frame by frame.');
    }

    /**
     * Extract thumbnail from video
     */
    protected function extractVideoThumbnail($videoPath)
    {
        // Check if FFmpeg is available
        $ffmpegPath = exec('which ffmpeg');
        
        if (empty($ffmpegPath)) {
            return null;
        }

        $outputPath = storage_path('app/public/temp/' . Str::uuid() . '.jpg');
        
        if (!file_exists(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0755, true);
        }

        // Extract frame at 1 second
        $command = "{$ffmpegPath} -i " . escapeshellarg($videoPath) . " -ss 00:00:01 -vframes 1 " . escapeshellarg($outputPath) . " 2>&1";
        exec($command, $output, $returnCode);

        if ($returnCode === 0 && file_exists($outputPath)) {
            return $outputPath;
        }

        return null;
    }
}
