<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ImagePromptTemplate;
use App\Models\UserImageSubmission;
use App\Models\UserSubscription;
use App\Jobs\ProcessUserImageJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerationController extends Controller
{
    /**
     * Upload image and start generation
     */
    public function upload(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'template_id' => 'required|exists:image_prompt_templates,id',
                'image' => 'required|file|mimes:jpeg,jpg,png,gif,webp,mp4,mov,avi|max:51200', // 50MB max
            ]);

            $user = $request->user();
            $templateId = $request->template_id;

            // Get template
            $template = ImagePromptTemplate::where('is_active', true)
                ->findOrFail($templateId);

            // Check if user has active subscription
            $subscription = UserSubscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->with('plan')
                ->first();

            if (!$subscription || !$subscription->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You need an active subscription to use templates'
                ], 403);
            }

            // Get coins required for this template
            $coinsRequired = $template->coins_required ?? $this->getCoinsRequired($template->type);

            // Check if user has enough coins
            $remainingCoins = $subscription->remaining_coins;
            if ($remainingCoins < $coinsRequired) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient coins. You need {$coinsRequired} coins but have {$remainingCoins} coins remaining.",
                    'coins_required' => $coinsRequired,
                    'coins_available' => $remainingCoins
                ], 403);
            }

            DB::beginTransaction();

            try {
                // Store the uploaded image
                $originalPath = $request->file('image')
                    ->store('user-submissions/originals', 'public');

                // Create submission record
                $submission = UserImageSubmission::create([
                    'user_id' => $user->id,
                    'template_id' => $template->id,
                    'original_image_path' => $originalPath,
                    'status' => 'pending',
                    'started_at' => now(),
                ]);

                // Deduct coins BEFORE processing
                $subscription->increment('coins_used', $coinsRequired);

                // Increment template usage
                $template->incrementUsage();

                // Generate unique generation ID
                $generationId = 'gen_' . Str::random(16);

                DB::commit();

                // Dispatch job for processing
                if (config('image-prompt.processing.use_queue', false)) {
                    ProcessUserImageJob::dispatch($submission);
                } else {
                    // Process synchronously in background (not recommended for production)
                    dispatch(function () use ($submission) {
                        app(\App\Http\Controllers\ImageSubmissionController::class)
                            ->processImageForApi($submission);
                    })->afterResponse();
                }

                // Get estimated time based on type
                $estimatedTime = $template->type === 'video' ? 120 : 30;

                return response()->json([
                    'success' => true,
                    'data' => [
                        'generation_id' => $generationId,
                        'submission_id' => $submission->id,
                        'template_id' => $template->id,
                        'template_name' => $template->title,
                        'uploaded_image' => asset('storage/' . $originalPath),
                        'status' => 'pending',
                        'applied_prompt' => $template->prompt,
                        'estimated_time' => $estimatedTime,
                        'created_at' => $submission->created_at,
                        'coins_deducted' => $coinsRequired,
                        'remaining_coins' => $subscription->remaining_coins - $coinsRequired,
                    ],
                    'message' => 'Image uploaded successfully. Generation in progress.'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found or inactive'
            ], 404);
            
        } catch (\Exception $e) {
            Log::error('Error uploading image: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get generation status
     */
    public function status(Request $request, $submissionId)
    {
        try {
            $user = $request->user();

            $submission = UserImageSubmission::where('id', $submissionId)
                ->where('user_id', $user->id)
                ->with('template')
                ->firstOrFail();

            // Generate generation ID
            $generationId = 'gen_' . substr(md5($submission->id), 0, 16);

            // Calculate progress based on status
            $progress = 0;
            $message = '';
            $estimatedTimeRemaining = 0;

            switch ($submission->status) {
                case 'pending':
                    $progress = 10;
                    $message = 'Queued for processing...';
                    $estimatedTimeRemaining = $submission->template->type === 'video' ? 120 : 30;
                    break;
                case 'processing':
                    $progress = 65;
                    $message = 'Applying AI enhancements...';
                    $estimatedTimeRemaining = 15;
                    break;
                case 'completed':
                    $progress = 100;
                    $message = 'Generation completed successfully';
                    $estimatedTimeRemaining = 0;
                    break;
                case 'failed':
                    $progress = 0;
                    $message = $submission->error_message ?? 'Generation failed';
                    $estimatedTimeRemaining = 0;
                    break;
            }

            // If completed, return full data
            if ($submission->status === 'completed') {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'generation_id' => $generationId,
                        'submission_id' => $submission->id,
                        'status' => $submission->status,
                        'progress' => $progress,
                        'original_image' => asset('storage/' . $submission->original_image_path),
                        'generated_output' => $submission->processed_image_path 
                            ? asset('storage/' . $submission->processed_image_path) 
                            : null,
                        'thumbnail' => $submission->processed_image_path 
                            ? asset('storage/' . $submission->processed_image_path) 
                            : null,
                        'type' => $submission->output_type ?? $submission->template->type,
                        'template_used' => [
                            'id' => $submission->template->id,
                            'name' => $submission->template->title,
                        ],
                        'completed_at' => $submission->completed_at,
                    ],
                    'message' => 'Generation completed successfully'
                ]);
            }

            // If failed, return error
            if ($submission->status === 'failed') {
                return response()->json([
                    'success' => false,
                    'data' => [
                        'generation_id' => $generationId,
                        'submission_id' => $submission->id,
                        'status' => $submission->status,
                        'error' => $submission->error_message ?? 'Failed to process image. Please try again.',
                        'failed_at' => $submission->completed_at,
                    ],
                    'message' => 'Generation failed'
                ]);
            }

            // Still processing
            return response()->json([
                'success' => true,
                'data' => [
                    'generation_id' => $generationId,
                    'submission_id' => $submission->id,
                    'status' => $submission->status,
                    'progress' => $progress,
                    'message' => $message,
                    'estimated_time_remaining' => $estimatedTimeRemaining,
                ],
                'message' => 'Generation in progress'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Generation not found'
            ], 404);
            
        } catch (\Exception $e) {
            Log::error('Error fetching generation status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve generation status'
            ], 500);
        }
    }

    /**
     * Get generation history
     */
    public function history(Request $request)
    {
        try {
            $user = $request->user();

            // Get query parameters
            $perPage = min($request->get('per_page', 20), 100);
            $status = $request->get('status');
            $type = $request->get('type');
            $sortOrder = $request->get('sort_order', 'desc');

            // Build query
            $query = UserImageSubmission::where('user_id', $user->id)
                ->with('template');

            // Apply filters
            if ($status) {
                $query->where('status', $status);
            }

            if ($type) {
                $query->whereHas('template', function ($q) use ($type) {
                    $q->where('type', $type);
                });
            }

            // Apply sorting
            $query->orderBy('created_at', $sortOrder);

            // Paginate
            $submissions = $query->paginate($perPage);

            // Format data
            $data = $submissions->map(function ($submission) {
                $generationId = 'gen_' . substr(md5($submission->id), 0, 16);
                
                return [
                    'generation_id' => $generationId,
                    'submission_id' => $submission->id,
                    'template' => [
                        'id' => $submission->template->id,
                        'name' => $submission->template->title,
                        'thumbnail' => $submission->template->reference_image_url,
                    ],
                    'original_image' => asset('storage/' . $submission->original_image_path),
                    'generated_output' => $submission->processed_image_path 
                        ? asset('storage/' . $submission->processed_image_path) 
                        : null,
                    'status' => $submission->status,
                    'type' => $submission->output_type ?? $submission->template->type,
                    'created_at' => $submission->created_at,
                    'completed_at' => $submission->completed_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $submissions->currentPage(),
                    'per_page' => $submissions->perPage(),
                    'total' => $submissions->total(),
                    'last_page' => $submissions->lastPage(),
                ],
                'message' => 'Generation history retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching generation history: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve generation history'
            ], 500);
        }
    }

    /**
     * Delete a submission
     */
    public function delete(Request $request, $submissionId)
    {
        try {
            $user = $request->user();

            $submission = UserImageSubmission::where('id', $submissionId)
                ->where('user_id', $user->id)
                ->firstOrFail();

            // Delete files from storage
            if ($submission->original_image_path) {
                Storage::disk('public')->delete($submission->original_image_path);
            }

            if ($submission->processed_image_path) {
                Storage::disk('public')->delete($submission->processed_image_path);
            }

            // Delete submission record
            $submission->delete();

            return response()->json([
                'success' => true,
                'message' => 'Submission deleted successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Submission not found'
            ], 404);
            
        } catch (\Exception $e) {
            Log::error('Error deleting submission: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete submission'
            ], 500);
        }
    }

    /**
     * Get coins required based on template type
     */
    private function getCoinsRequired($type)
    {
        $coinsMap = [
            'image' => 5,
            'video' => 10,
        ];

        return $coinsMap[$type] ?? 5;
    }
}
