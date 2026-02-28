<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReplicateService
{
    protected $apiKey;
    protected $timeout;
    protected $baseUrl = 'https://api.replicate.com/v1';

    public function __construct()
    {
        $this->apiKey = config('image-prompt.replicate.api_key');
        $this->timeout = config('image-prompt.replicate.timeout', 300);
    }

    /**
     * Process image with instruct-pix2pix model
     * Best for prompts like "change boy to girl", "make it sunset", etc.
     */
    public function processImage($imagePath, $prompt)
    {
        if (!$this->apiKey) {
            throw new \Exception('Replicate API key not configured. Please add REPLICATE_API_KEY to your .env file.');
        }

        // Get the model version
        $model = config('image-prompt.replicate.models.image_edit');

        // Convert image to data URL
        $imageData = base64_encode(file_get_contents($imagePath));
        $mimeType = mime_content_type($imagePath);
        $imageUrl = "data:{$mimeType};base64,{$imageData}";

        // Create prediction
        $response = Http::withHeaders([
            'Authorization' => 'Token ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])
        ->timeout($this->timeout)
        ->post("{$this->baseUrl}/predictions", [
            'version' => $model,
            'input' => [
                'image' => $imageUrl,
                'prompt' => $prompt,
                'num_inference_steps' => 50,
                'guidance_scale' => 7.5,
                'image_guidance_scale' => 1.5,
            ]
        ]);

        if (!$response->successful()) {
            throw new \Exception('Replicate API request failed: ' . $response->body());
        }

        $prediction = $response->json();
        $predictionId = $prediction['id'];

        // Poll for completion
        return $this->waitForCompletion($predictionId);
    }

    /**
     * Process video with Deforum model
     */
    public function processVideo($videoPath, $prompt)
    {
        if (!$this->apiKey) {
            throw new \Exception('Replicate API key not configured.');
        }

        // For video, we need to upload to a temporary URL first
        // or use Replicate's file upload endpoint
        $model = config('image-prompt.replicate.models.video_transform');

        // Upload video to public storage temporarily
        $publicPath = 'temp/' . Str::uuid() . '.' . pathinfo($videoPath, PATHINFO_EXTENSION);
        Storage::disk('public')->put($publicPath, file_get_contents($videoPath));
        $videoUrl = asset('storage/' . $publicPath);

        // Create prediction
        $response = Http::withHeaders([
            'Authorization' => 'Token ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])
        ->timeout($this->timeout)
        ->post("{$this->baseUrl}/predictions", [
            'version' => $model,
            'input' => [
                'video' => $videoUrl,
                'prompt' => $prompt,
                'num_frames' => 100,
                'fps' => 15,
            ]
        ]);

        if (!$response->successful()) {
            // Clean up temp file
            Storage::disk('public')->delete($publicPath);
            throw new \Exception('Replicate API request failed: ' . $response->body());
        }

        $prediction = $response->json();
        $predictionId = $prediction['id'];

        // Poll for completion
        $result = $this->waitForCompletion($predictionId);

        // Clean up temp file
        Storage::disk('public')->delete($publicPath);

        return $result;
    }

    /**
     * Wait for prediction to complete
     */
    protected function waitForCompletion($predictionId)
    {
        $maxAttempts = 60; // 5 minutes max (60 * 5 seconds)
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            sleep(5); // Wait 5 seconds between checks

            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $this->apiKey,
            ])
            ->get("{$this->baseUrl}/predictions/{$predictionId}");

            if (!$response->successful()) {
                throw new \Exception('Failed to check prediction status');
            }

            $prediction = $response->json();
            $status = $prediction['status'];

            if ($status === 'succeeded') {
                // Return the output URL(s)
                return [
                    'status' => 'success',
                    'output' => $prediction['output'], // URL or array of URLs
                ];
            }

            if ($status === 'failed' || $status === 'canceled') {
                throw new \Exception('Prediction failed: ' . ($prediction['error'] ?? 'Unknown error'));
            }

            $attempt++;
        }

        throw new \Exception('Prediction timed out after 5 minutes');
    }

    /**
     * Download output file from URL and save to storage
     */
    public function downloadOutput($outputUrl, $storagePath)
    {
        $response = Http::timeout(120)->get($outputUrl);

        if (!$response->successful()) {
            throw new \Exception('Failed to download output file');
        }

        // Determine file extension from content type or URL
        $contentType = $response->header('Content-Type');
        $extension = $this->getExtensionFromContentType($contentType);
        
        if (!$extension) {
            $extension = pathinfo(parse_url($outputUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
        }

        $fileName = $storagePath . '/' . Str::uuid() . '.' . $extension;
        Storage::disk('public')->put($fileName, $response->body());

        return $fileName;
    }

    /**
     * Get file extension from content type
     */
    protected function getExtensionFromContentType($contentType)
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'video/mp4' => 'mp4',
            'video/quicktime' => 'mov',
            'video/x-msvideo' => 'avi',
        ];

        return $map[$contentType] ?? null;
    }
}
