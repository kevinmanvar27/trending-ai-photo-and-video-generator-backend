<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ImagePrompt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ImagePromptController extends Controller
{
    /**
     * Get all user prompts
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $query = ImagePrompt::where('user_id', $user->id);

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by file type
            if ($request->has('file_type')) {
                $query->where('file_type', $request->file_type);
            }

            // Filter by output type
            if ($request->has('output_type')) {
                $query->where('output_type', $request->output_type);
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $prompts = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $prompts
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch prompts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific prompt
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $prompt = ImagePrompt::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$prompt) {
                return response()->json([
                    'success' => false,
                    'message' => 'Prompt not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $prompt
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch prompt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new image prompt
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'original_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
                'prompt' => 'required|string|max:1000',
                'output_type' => 'nullable|string|in:image,video',
                'file_type' => 'nullable|string|in:image,video'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();

            // Upload original image
            $image = $request->file('original_image');
            $originalPath = $image->store('prompts/originals', 'public');

            // Create prompt record
            $prompt = ImagePrompt::create([
                'user_id' => $user->id,
                'original_image_path' => $originalPath,
                'prompt' => $request->prompt,
                'file_type' => $request->file_type ?? 'image',
                'output_type' => $request->output_type ?? 'image',
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Prompt created successfully. Processing will begin shortly.',
                'data' => $prompt
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create prompt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process an image with Grok AI
     */
    public function process(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $prompt = ImagePrompt::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$prompt) {
                return response()->json([
                    'success' => false,
                    'message' => 'Prompt not found'
                ], 404);
            }

            if ($prompt->status === 'processing') {
                return response()->json([
                    'success' => false,
                    'message' => 'Prompt is already being processed'
                ], 400);
            }

            // Update status to processing
            $prompt->update(['status' => 'processing']);

            $startTime = microtime(true);

            // Get the image URL
            $imageUrl = asset('storage/' . $prompt->original_image_path);

            // Call Grok Imagine API for image generation
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('GROK_API_KEY'),
                'Content-Type' => 'application/json'
            ])->timeout(env('GROK_TIMEOUT', 180))
            ->post(env('GROK_IMAGINE_API_URL'), [
                'prompt' => $prompt->prompt,
                'model' => env('GROK_IMAGINE_MODEL', 'grok-imagine-image'),
                'size' => env('GROK_IMAGINE_SIZE', '1024x1024'),
                'quality' => env('GROK_IMAGINE_QUALITY', 'high'),
                'n' => 1
            ]);

            if ($response->failed()) {
                $prompt->update([
                    'status' => 'failed',
                    'error_message' => 'API request failed: ' . $response->body()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process image',
                    'error' => $response->body()
                ], 500);
            }

            $result = $response->json();
            
            // Download and save the generated image
            if (isset($result['data'][0]['url'])) {
                $generatedImageUrl = $result['data'][0]['url'];
                $imageContent = file_get_contents($generatedImageUrl);
                
                $filename = 'processed_' . time() . '_' . uniqid() . '.png';
                $processedPath = 'prompts/processed/' . $filename;
                
                Storage::disk('public')->put($processedPath, $imageContent);

                $processingTime = microtime(true) - $startTime;

                $prompt->update([
                    'processed_image_path' => $processedPath,
                    'status' => 'completed',
                    'processing_time' => round($processingTime, 2)
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Image processed successfully',
                    'data' => $prompt->fresh()
                ], 200);
            } else {
                $prompt->update([
                    'status' => 'failed',
                    'error_message' => 'No image URL in response'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process image',
                    'error' => 'No image URL in response'
                ], 500);
            }
        } catch (\Exception $e) {
            if (isset($prompt)) {
                $prompt->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to process image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update prompt status manually
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $prompt = ImagePrompt::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$prompt) {
                return response()->json([
                    'success' => false,
                    'message' => 'Prompt not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:pending,processing,completed,failed',
                'processed_image' => 'nullable|file|max:51200',
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
                if ($prompt->processed_image_path) {
                    Storage::disk('public')->delete($prompt->processed_image_path);
                }

                $file = $request->file('processed_image');
                $processedPath = $file->store('prompts/processed', 'public');
                $data['processed_image_path'] = $processedPath;
            }

            if ($request->has('error_message')) {
                $data['error_message'] = $request->error_message;
            }

            if ($request->has('processing_time')) {
                $data['processing_time'] = $request->processing_time;
            }

            $prompt->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Prompt updated successfully',
                'data' => $prompt
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update prompt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a prompt
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $prompt = ImagePrompt::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$prompt) {
                return response()->json([
                    'success' => false,
                    'message' => 'Prompt not found'
                ], 404);
            }

            // Delete files
            if ($prompt->original_image_path) {
                Storage::disk('public')->delete($prompt->original_image_path);
            }
            if ($prompt->processed_image_path) {
                Storage::disk('public')->delete($prompt->processed_image_path);
            }

            $prompt->delete();

            return response()->json([
                'success' => true,
                'message' => 'Prompt deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete prompt',
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
                'total_prompts' => ImagePrompt::where('user_id', $user->id)->count(),
                'completed' => ImagePrompt::where('user_id', $user->id)->where('status', 'completed')->count(),
                'pending' => ImagePrompt::where('user_id', $user->id)->where('status', 'pending')->count(),
                'processing' => ImagePrompt::where('user_id', $user->id)->where('status', 'processing')->count(),
                'failed' => ImagePrompt::where('user_id', $user->id)->where('status', 'failed')->count(),
                'average_processing_time' => ImagePrompt::where('user_id', $user->id)
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
     * Get recent prompts
     */
    public function recent(Request $request)
    {
        try {
            $user = Auth::user();
            $limit = $request->get('limit', 10);

            $prompts = ImagePrompt::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $prompts
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent prompts',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
