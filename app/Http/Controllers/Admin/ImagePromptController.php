<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImagePrompt;
use App\Services\GrokImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImagePromptController extends Controller
{
    protected $grokService;

    public function __construct(GrokImageService $grokService)
    {
        $this->grokService = $grokService;
    }
    /**
     * Display a listing of image prompts.
     */
    public function index()
    {
        $imagePrompts = ImagePrompt::with('user')
            ->latest()
            ->paginate(15);

        return view('admin.image-prompts.index', compact('imagePrompts'));
    }

    /**
     * Show the form for creating a new image prompt.
     */
    public function create()
    {
        return view('admin.image-prompts.create');
    }

    /**
     * Store a newly created image prompt in storage.
     */
    public function store(Request $request)
    {
        $maxSize = config('image-prompt.upload.max_size', 51200);
        $allowedImages = implode(',', config('image-prompt.upload.allowed_image_types', ['jpeg', 'jpg', 'png', 'gif']));
        $allowedVideos = implode(',', config('image-prompt.upload.allowed_video_types', ['mp4', 'mov', 'avi']));
        $allowedTypes = $allowedImages . ',' . $allowedVideos;

        $validator = Validator::make($request->all(), [
            'image' => "required|file|mimes:{$allowedTypes}|max:{$maxSize}",
            'prompt' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Determine file type
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $fileType = in_array(strtolower($extension), ['mp4', 'mov', 'avi']) ? 'video' : 'image';

            // Store the original file
            $storagePath = config('image-prompt.upload.storage_path', 'image-prompts');
            $originalPath = $file->store($storagePath . '/originals', 'public');

            // Create the image prompt record
            $imagePrompt = ImagePrompt::create([
                'user_id' => auth()->id(),
                'original_image_path' => $originalPath,
                'prompt' => $request->prompt,
                'file_type' => $fileType,
                'status' => 'pending',
            ]);

            // Process the image/video with the prompt
            if (config('image-prompt.processing.use_queue', false)) {
                \App\Jobs\ProcessImagePromptJob::dispatch($imagePrompt);
            } else {
                $this->processImageWithPrompt($imagePrompt);
            }

            return redirect()
                ->route('admin.image-prompts.show', $imagePrompt->id)
                ->with('success', 'Image uploaded successfully and is being processed.');

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to upload image: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified image prompt.
     */
    public function show($id)
    {
        $imagePrompt = ImagePrompt::with('user')->findOrFail($id);
        return view('admin.image-prompts.show', compact('imagePrompt'));
    }

    /**
     * Remove the specified image prompt from storage.
     */
    public function destroy($id)
    {
        try {
            $imagePrompt = ImagePrompt::findOrFail($id);

            // Delete files from storage
            if (Storage::disk('public')->exists($imagePrompt->original_image_path)) {
                Storage::disk('public')->delete($imagePrompt->original_image_path);
            }

            if ($imagePrompt->processed_image_path && Storage::disk('public')->exists($imagePrompt->processed_image_path)) {
                Storage::disk('public')->delete($imagePrompt->processed_image_path);
            }

            // Delete the record
            $imagePrompt->delete();

            return redirect()
                ->route('admin.image-prompts.index')
                ->with('success', 'Image prompt deleted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete image prompt: ' . $e->getMessage());
        }
    }

    /**
     * Reprocess an image prompt.
     */
    public function reprocess($id)
    {
        try {
            $imagePrompt = ImagePrompt::findOrFail($id);
            
            // Reset status
            $imagePrompt->update([
                'status' => 'pending',
                'error_message' => null,
                'processing_time' => null,
            ]);

            // Process again
            if (config('image-prompt.processing.use_queue', false)) {
                \App\Jobs\ProcessImagePromptJob::dispatch($imagePrompt);
            } else {
                $this->processImageWithPrompt($imagePrompt);
            }

            return back()->with('success', 'Image is being reprocessed.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reprocess image: ' . $e->getMessage());
        }
    }

    /**
     * Process the image/video with the given prompt using Grok AI.
     */
    private function processImageWithPrompt(ImagePrompt $imagePrompt)
    {
        try {
            $startTime = now();
            $imagePrompt->update(['status' => 'processing']);

            // Get the full path to the file
            $filePath = storage_path('app/public/' . $imagePrompt->original_image_path);
            
            if (!file_exists($filePath)) {
                throw new \Exception('Original file not found.');
            }

            $prompt = $imagePrompt->prompt;
            $mimeType = mime_content_type($filePath);

            // Process based on file type
            if ($imagePrompt->file_type === 'video' || str_starts_with($mimeType, 'video/')) {
                // Video files as input are not supported yet
                $result = $this->grokService->processVideo($filePath, $prompt);
            } else {
                // Process image using Grok Imagine (will auto-detect if video generation is needed)
                $result = $this->grokService->processImage($filePath, $prompt);
            }

            // Check if processing was successful
            if (!$result['success']) {
                throw new \Exception($result['error'] ?? 'Processing failed');
            }

            // Determine output type (image or video)
            $outputType = $result['type'] ?? 'image';
            $isVideo = ($outputType === 'video');

            // Store processed file
            $storagePath = config('image-prompt.upload.storage_path', 'image-prompts');
            $fileExtension = $isVideo ? '.mp4' : '.png';
            $fileName = uniqid() . '_' . time() . $fileExtension;
            $relativePath = $storagePath . '/processed/' . $fileName;
            
            // Download and save the generated file
            if ($isVideo) {
                // Handle video output
                if (!empty($result['video_base64'])) {
                    // Save from base64 data
                    Storage::disk('public')->put($relativePath, base64_decode($result['video_base64']));
                } elseif (!empty($result['video_url'])) {
                    // Download from URL
                    $videoContent = file_get_contents($result['video_url']);
                    if ($videoContent === false) {
                        throw new \Exception('Failed to download generated video from URL');
                    }
                    Storage::disk('public')->put($relativePath, $videoContent);
                } else {
                    throw new \Exception('No video data returned from Grok Video API');
                }
            } else {
                // Handle image output
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
            }

            $processingTime = now()->diffInSeconds($startTime);

            $imagePrompt->update([
                'processed_image_path' => $relativePath,
                'output_type' => $outputType,
                'status' => 'completed',
                'processing_time' => $processingTime,
            ]);

        } catch (\Exception $e) {
            $imagePrompt->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Download the processed image.
     */
    public function download($id)
    {
        $imagePrompt = ImagePrompt::findOrFail($id);

        if (!$imagePrompt->processed_image_path) {
            return back()->with('error', 'No processed image available.');
        }

        $filePath = storage_path('app/public/' . $imagePrompt->processed_image_path);

        if (!file_exists($filePath)) {
            return back()->with('error', 'Processed file not found.');
        }

        return response()->download($filePath);
    }
}
