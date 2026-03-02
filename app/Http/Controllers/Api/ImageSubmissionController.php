<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserImageSubmission;
use App\Models\ImagePromptTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ImageSubmissionController extends Controller
{
    /**
     * Get all user submissions
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $query = UserImageSubmission::where('user_id', $user->id);

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by output type
            if ($request->has('output_type')) {
                $query->where('output_type', $request->output_type);
            }

            // Filter by template
            if ($request->has('template_id')) {
                $query->where('template_id', $request->template_id);
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $submissions = $query->with('template')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $submissions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch submissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific submission
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $submission = UserImageSubmission::where('user_id', $user->id)
                ->where('id', $id)
                ->with('template')
                ->first();

            if (!$submission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Submission not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $submission
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch submission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new submission
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'template_id' => 'required|exists:image_prompt_templates,id',
                'original_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
                'output_type' => 'required|string|in:image,video'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $template = ImagePromptTemplate::find($request->template_id);

            if (!$template->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'This template is not available'
                ], 400);
            }

            // Check if user has active subscription
            $activeSubscription = $user->activeSubscription;
            
            if (!$activeSubscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'You need an active subscription to use templates'
                ], 403);
            }

            // Check if template requires coins
            $coinsRequired = $template->coins_required ?? 0;
            
            if ($coinsRequired > 0) {
                // Check if user has enough coins
                if (!$activeSubscription->hasEnoughCoins($coinsRequired)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient coins. You need ' . $coinsRequired . ' coins but have ' . $activeSubscription->remaining_coins . ' coins remaining.',
                        'coins_required' => $coinsRequired,
                        'coins_available' => $activeSubscription->remaining_coins
                    ], 403);
                }

                // Deduct coins from user's subscription
                if (!$activeSubscription->useCoins($coinsRequired)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to deduct coins. Please try again.'
                    ], 500);
                }
            }

            // Upload original image
            $image = $request->file('original_image');
            $originalPath = $image->store('submissions/originals', 'public');

            // Create submission
            $submission = UserImageSubmission::create([
                'user_id' => $user->id,
                'template_id' => $template->id,
                'original_image_path' => $originalPath,
                'output_type' => $request->output_type,
                'status' => 'pending',
                'started_at' => now()
            ]);

            // Increment template usage
            $template->incrementUsage();

            return response()->json([
                'success' => true,
                'message' => 'Submission created successfully',
                'data' => $submission,
                'coins_deducted' => $coinsRequired,
                'remaining_coins' => $activeSubscription->remaining_coins
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create submission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update submission status (for processing)
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $submission = UserImageSubmission::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$submission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Submission not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:pending,processing,completed,failed',
                'processed_image' => 'nullable|file|max:51200', // 50MB max for videos
                'error_message' => 'nullable|string',
                'processing_time' => 'nullable|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = ['status' => $request->status];

            // Handle processed file upload
            if ($request->hasFile('processed_image')) {
                // Delete old processed file if exists
                if ($submission->processed_image_path) {
                    Storage::disk('public')->delete($submission->processed_image_path);
                }

                $file = $request->file('processed_image');
                $processedPath = $file->store('submissions/processed', 'public');
                $data['processed_image_path'] = $processedPath;
            }

            if ($request->has('error_message')) {
                $data['error_message'] = $request->error_message;
            }

            if ($request->has('processing_time')) {
                $data['processing_time'] = $request->processing_time;
            }

            if ($request->status === 'completed') {
                $data['completed_at'] = now();
            }

            $submission->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Submission updated successfully',
                'data' => $submission
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update submission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a submission
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $submission = UserImageSubmission::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$submission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Submission not found'
                ], 404);
            }

            // Delete files
            if ($submission->original_image_path) {
                Storage::disk('public')->delete($submission->original_image_path);
            }
            if ($submission->processed_image_path) {
                Storage::disk('public')->delete($submission->processed_image_path);
            }

            $submission->delete();

            return response()->json([
                'success' => true,
                'message' => 'Submission deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete submission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user statistics
     */
    public function statistics()
    {
        try {
            $user = Auth::user();

            $stats = [
                'total_submissions' => UserImageSubmission::where('user_id', $user->id)->count(),
                'completed' => UserImageSubmission::where('user_id', $user->id)->where('status', 'completed')->count(),
                'pending' => UserImageSubmission::where('user_id', $user->id)->where('status', 'pending')->count(),
                'processing' => UserImageSubmission::where('user_id', $user->id)->where('status', 'processing')->count(),
                'failed' => UserImageSubmission::where('user_id', $user->id)->where('status', 'failed')->count(),
                'images_generated' => UserImageSubmission::where('user_id', $user->id)
                    ->where('output_type', 'image')
                    ->where('status', 'completed')
                    ->count(),
                'videos_generated' => UserImageSubmission::where('user_id', $user->id)
                    ->where('output_type', 'video')
                    ->where('status', 'completed')
                    ->count(),
                'average_processing_time' => UserImageSubmission::where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->whereNotNull('processing_time')
                    ->avg('processing_time')
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent submissions
     */
    public function recent(Request $request)
    {
        try {
            $user = Auth::user();
            $limit = $request->get('limit', 10);

            $submissions = UserImageSubmission::where('user_id', $user->id)
                ->with('template')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $submissions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent submissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
