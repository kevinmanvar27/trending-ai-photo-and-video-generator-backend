<?php

namespace App\Jobs;

use App\Models\ImagePrompt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessImagePromptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 180; // 3 minutes
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ImagePrompt $imagePrompt
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $startTime = now();
            $this->imagePrompt->update(['status' => 'processing']);

            // Get the API key from environment
            $apiKey = config('image-prompt.grok.api_key');
            
            if (!$apiKey) {
                throw new \Exception('Grok API key not configured. Please add GROK_API_KEY to your .env file.');
            }

            // Get the full path to the image
            $imagePath = storage_path('app/public/' . $this->imagePrompt->original_image_path);
            
            if (!file_exists($imagePath)) {
                throw new \Exception('Original image file not found.');
            }

            // Use GrokImageService for image processing
            $grokService = app(\App\Services\GrokImageService::class);
            $prompt = $this->imagePrompt->prompt;
            $mimeType = mime_content_type($imagePath);

            // Process based on file type
            if ($this->imagePrompt->file_type === 'video' || str_starts_with($mimeType, 'video/')) {
                // Process video
                $result = $grokService->processVideo($imagePath, $prompt);
            } else {
                // Process image using Grok Imagine
                $result = $grokService->processImage($imagePath, $prompt);
            }

            // Check if processing was successful
            if (!$result['success']) {
                throw new \Exception($result['error'] ?? 'Image processing failed');
            }

            // Store processed file
            $storagePath = config('image-prompt.upload.storage_path', 'image-prompts');
            $fileName = uniqid() . '_' . time() . '.png';
            $relativePath = $storagePath . '/processed/' . $fileName;
            
            // Download and save the generated image
            if (!empty($result['image_base64'])) {
                // Save from base64 data
                Storage::disk('public')->put($relativePath, base64_decode($result['image_base64']));
            } elseif (!empty($result['image_url'])) {
                // Download from URL
                $imageContent = file_get_contents($result['image_url']);
                if ($imageContent === false) {
                    throw new \Exception('Failed to download generated image from URL');
                }
                Storage::disk('public')->put($relativePath, $imageContent);
            } else {
                throw new \Exception('No image data returned from Grok Imagine');
            }

            $processingTime = now()->diffInSeconds($startTime);

            $this->imagePrompt->update([
                'processed_image_path' => $relativePath,
                'status' => 'completed',
                'processing_time' => $processingTime,
            ]);

        } catch (\Exception $e) {
            $this->imagePrompt->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            // Re-throw the exception to mark job as failed
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $this->imagePrompt->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);
    }
}
