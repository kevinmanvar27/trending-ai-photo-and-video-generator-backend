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

            // Create submission record
            $submission = UserImageSubmission::create([
                'user_id' => auth()->id(),
                'template_id' => $template->id,
                'original_image_path' => $originalPath,
                'status' => 'pending',
            ]);

            // Increment template usage
            $template->incrementUsage();

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
        $submission = UserImageSubmission::with('template')
            ->where('user_id', auth()->id())
            ->orWhereNull('user_id')
            ->findOrFail($id);

        return view('image-submission.show', compact('submission'));
    }

    /**
     * Download the processed image.
     */
    public function download($id)
    {
        $submission = UserImageSubmission::where('user_id', auth()->id())
            ->orWhereNull('user_id')
            ->findOrFail($id);

        if (!$submission->processed_image_path) {
            return back()->with('error', 'Processed image not available yet.');
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

            // Store processed file
            $fileName = uniqid() . '_' . time() . '.png';
            $relativePath = 'user-submissions/processed/' . $fileName;
            
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

            $submission->update([
                'processed_image_path' => $relativePath,
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
