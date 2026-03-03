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

            Log::info('Starting image/video generation', [
                'submission_id' => $this->submission->id,
                'template_id' => $this->submission->template_id,
                'output_type' => $this->submission->output_type
            ]);

            // Get the original image path
            $imagePath = storage_path('app/public/' . $this->submission->original_image_path);
            
            if (!file_exists($imagePath)) {
                throw new \Exception('Original image file not found at: ' . $imagePath);
            }

            // Get the transformation prompt from the template
            $prompt = $this->submission->template->prompt;

            Log::info('Calling Grok API for generation', [
                'prompt' => $prompt,
                'image_path' => $imagePath,
                'output_type' => $this->submission->output_type
            ]);

            // Determine which Grok service to use based on output type
            if ($this->submission->output_type === 'video') {
                // Generate video from image
                $result = $grokService->generateVideoFromImage($imagePath, $prompt);
            } else {
                // Transform image (default)
                $result = $grokService->transformImage($imagePath, $prompt);
            }

            if (!$result['success']) {
                throw new \Exception($result['error'] ?? 'Generation failed');
            }

            Log::info('Generation successful', [
                'output_type' => $result['type'] ?? $this->submission->output_type,
                'has_url' => !empty($result['image_url'] ?? $result['video_url']),
                'has_base64' => !empty($result['image_base64'] ?? $result['video_base64'])
            ]);

            // Determine file extension based on output type
            $extension = ($result['type'] ?? $this->submission->output_type) === 'video' ? '.mp4' : '.png';
            $processedFileName = 'submissions/processed/' . Str::uuid() . $extension;
            
            // Get the appropriate data based on type
            $base64Data = $result['image_base64'] ?? $result['video_base64'] ?? null;
            $url = $result['image_url'] ?? $result['video_url'] ?? null;
            
            if (!empty($base64Data)) {
                // Save from base64 data
                Storage::disk('public')->put($processedFileName, base64_decode($base64Data));
            } elseif (!empty($url)) {
                // Download from URL
                $content = file_get_contents($url);
                if ($content === false) {
                    throw new \Exception('Failed to download generated content from URL');
                }
                Storage::disk('public')->put($processedFileName, $content);
            } else {
                throw new \Exception('No data returned from Grok API');
            }

            $processingTime = now()->diffInSeconds($startTime);

            Log::info('Content saved successfully', [
                'processed_path' => $processedFileName,
                'processing_time' => $processingTime
            ]);

            // Update submission with success
            $this->submission->update([
                'processed_image_path' => $processedFileName,
                'status' => 'completed',
                'completed_at' => now(),
                'processing_time' => $processingTime,
                'error_message' => null, // Clear any previous errors
            ]);

        } catch (\Exception $e) {
            Log::error('Generation failed', [
                'submission_id' => $this->submission->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->submission->update([
                'status' => 'failed',
                'error_message' => substr($e->getMessage(), 0, 1000), // Limit error message length
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
