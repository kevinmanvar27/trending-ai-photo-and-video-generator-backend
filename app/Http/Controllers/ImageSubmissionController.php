<?php

namespace App\Http\Controllers;

use App\Models\ImagePromptTemplate;
use App\Models\UserImageSubmission;
use App\Services\GrokImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ImageSubmissionController extends Controller
{
    protected $imageService;

    public function __construct(GrokImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Display available templates for users.
     */
    public function index()
    {
        $templates = ImagePromptTemplate::where('is_active', true)
            ->latest()
            ->get();

        return view('image-submission.index', compact('templates'));
    }

    /**
     * Show the upload form for a specific template.
     */
    public function create($templateId)
    {
        $template = ImagePromptTemplate::where('is_active', true)
            ->findOrFail($templateId);

        return view('image-submission.create', compact('template'));
    }

    /**
     * Store user's image submission.
     */
    public function store(Request $request, $templateId)
    {
        $template = ImagePromptTemplate::where('is_active', true)
            ->findOrFail($templateId);

        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,jpg,png,gif|max:10240', // 10MB
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Store the uploaded image
            $originalPath = $request->file('image')
                ->store('user-submissions/originals', 'public');

            // Create submission record (allow guest users)
            $submission = UserImageSubmission::create([
                'user_id' => auth()->check() ? auth()->id() : null,
                'template_id' => $template->id,
                'original_image_path' => $originalPath,
                'status' => 'pending',
            ]);

            // Increment template usage
            $template->incrementUsage();

            // Store submission ID in session for guest users
            if (!auth()->check()) {
                session()->push('guest_submissions', $submission->id);
            }

            // Process the image with the template's prompt
            if (config('image-prompt.processing.use_queue', false)) {
                \App\Jobs\ProcessUserImageJob::dispatch($submission);
            } else {
                $this->processImage($submission);
            }

            return redirect()
                ->route('image-submission.show', $submission->id)
                ->with('success', 'Image uploaded successfully and is being processed.');

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to upload image: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the submission result.
     */
    public function show($id)
    {
        $submission = UserImageSubmission::with('template')->findOrFail($id);
        
        // Authorization check
        if (auth()->check()) {
            // Authenticated users can only view their own submissions
            if ($submission->user_id !== auth()->id()) {
                abort(403, 'Unauthorized access to this submission.');
            }
        } else {
            // Guest users can only view submissions they created (tracked in session)
            $guestSubmissions = session()->get('guest_submissions', []);
            if ($submission->user_id !== null || !in_array($submission->id, $guestSubmissions)) {
                abort(403, 'Unauthorized access to this submission.');
            }
        }

        // Get site settings for the view
        $siteTitle = \App\Models\Setting::get('site_title', config('app.name', 'AI Image Effects'));
        $footerText = \App\Models\Setting::get('footer_text', 'All rights reserved.');

        return view('image-submission.show', compact('submission', 'siteTitle', 'footerText'));
    }

    /**
     * Download the processed image.
     */
    public function download($id)
    {
        $submission = UserImageSubmission::findOrFail($id);
        
        // Authorization check
        if (auth()->check()) {
            // Authenticated users can only download their own submissions
            if ($submission->user_id !== auth()->id()) {
                abort(403, 'Unauthorized access to this submission.');
            }
        } else {
            // Guest users can only download submissions they created (tracked in session)
            $guestSubmissions = session()->get('guest_submissions', []);
            if ($submission->user_id !== null || !in_array($submission->id, $guestSubmissions)) {
                abort(403, 'Unauthorized access to this submission.');
            }
        }

        if (!$submission->processed_image_path) {
            return back()->with('error', 'Processed file not available yet.');
        }

        $filePath = storage_path('app/public/' . $submission->processed_image_path);

        if (!file_exists($filePath)) {
            return back()->with('error', 'File not found.');
        }

        return response()->download($filePath);
    }

    /**
     * Process the image with Grok AI (generates modified images).
     */
    private function processImage(UserImageSubmission $submission)
    {
        try {
            $startTime = now();
            $submission->update([
                'status' => 'processing',
                'started_at' => now()
            ]);

            $imagePath = storage_path('app/public/' . $submission->original_image_path);
            
            if (!file_exists($imagePath)) {
                throw new \Exception('Original image file not found.');
            }

            // Get the template's prompt
            $prompt = $submission->template->prompt;
            $mimeType = mime_content_type($imagePath);

            // Detect if video or image
            if (str_starts_with($mimeType, 'video/')) {
                $result = $this->imageService->processVideo($imagePath, $prompt);
            } else {
                $result = $this->imageService->processImage($imagePath, $prompt);
            }

            // Check if processing was successful
            if (!$result['success']) {
                throw new \Exception($result['error'] ?? 'Image processing failed');
            }

            // Determine output type (image or video)
            $outputType = $result['type'] ?? 'image';
            $isVideo = ($outputType === 'video');

            // Store processed file
            $fileExtension = $isVideo ? '.mp4' : '.png';
            $fileName = uniqid() . '_' . time() . $fileExtension;
            $relativePath = 'user-submissions/processed/' . $fileName;
            
            // Download and save the generated file
            if ($isVideo) {
                // Handle video output
                if (!empty($result['video_base64'])) {
                    Storage::disk('public')->put($relativePath, base64_decode($result['video_base64']));
                } elseif (!empty($result['video_url'])) {
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
                    Storage::disk('public')->put($relativePath, base64_decode($result['image_base64']));
                } elseif (!empty($result['image_url'])) {
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

            $submission->update([
                'processed_image_path' => $relativePath,
                'output_type' => $outputType,
                'status' => 'completed',
                'processing_time' => $processingTime,
                'completed_at' => now()
            ]);

        } catch (\Exception $e) {
            $submission->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now()
            ]);
        }
    }
}
