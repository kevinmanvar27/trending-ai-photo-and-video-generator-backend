<?php

namespace App\Jobs;

use App\Models\UserImageSubmission;
use App\Services\GrokImageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProcessUserImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Increased timeout for image generation (can take 30-60 seconds)
    public $timeout = 300;
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public UserImageSubmission $submission
    ) {}

    /**
     * Execute the job.
     */
    public function handle(GrokImageService $grokService): void
    {
        try {
            $startTime = now();
            $this->submission->update(['status' => 'processing']);

            Log::info('Starting image transformation', [
                'submission_id' => $this->submission->id,
                'template_id' => $this->submission->template_id
            ]);

            // Get the original image path
            $imagePath = storage_path('app/public/' . $this->submission->original_image_path);
            
            if (!file_exists($imagePath)) {
                throw new \Exception('Original image file not found at: ' . $imagePath);
            }

            // Get the transformation prompt from the template
            $prompt = $this->submission->template->prompt;

            Log::info('Calling Grok Imagine for transformation', [
                'prompt' => $prompt,
                'image_path' => $imagePath
            ]);

            // Use Grok Imagine to transform the image
            $result = $grokService->transformImage($imagePath, $prompt);

            if (!$result['success']) {
                throw new \Exception($result['error'] ?? 'Image transformation failed');
            }

            Log::info('Transformation successful', [
                'image_url' => $result['image_url'] ?? 'none',
                'has_base64' => !empty($result['image_base64'])
            ]);

            // Save the generated image
            $processedFileName = 'user-submissions/processed/' . Str::uuid() . '.png';
            
            if (!empty($result['image_base64'])) {
                // Save from base64 data
                Storage::disk('public')->put($processedFileName, base64_decode($result['image_base64']));
            } elseif (!empty($result['image_url'])) {
                // Download from URL
                $imageContent = file_get_contents($result['image_url']);
                if ($imageContent === false) {
                    throw new \Exception('Failed to download generated image from URL');
                }
                Storage::disk('public')->put($processedFileName, $imageContent);
            } else {
                throw new \Exception('No image data returned from Grok Imagine');
            }

            $processingTime = now()->diffInSeconds($startTime);

            Log::info('Image saved successfully', [
                'processed_path' => $processedFileName,
                'processing_time' => $processingTime
            ]);

            // Update submission with success
            $this->submission->update([
                'processed_image_path' => $processedFileName,
                'status' => 'completed',
                'processing_time' => $processingTime,
                'error_message' => null, // Clear any previous errors
            ]);

        } catch (\Exception $e) {
            Log::error('Image transformation failed', [
                'submission_id' => $this->submission->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->submission->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessUserImageJob failed permanently', [
            'submission_id' => $this->submission->id,
            'error' => $exception->getMessage()
        ]);

        $this->submission->update([
            'status' => 'failed',
            'error_message' => 'Job failed after ' . $this->tries . ' attempts: ' . $exception->getMessage(),
        ]);
    }
}
