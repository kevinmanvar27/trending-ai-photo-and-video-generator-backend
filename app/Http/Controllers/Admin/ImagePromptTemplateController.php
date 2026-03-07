<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImagePromptTemplate;
use App\Models\UserImageSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImagePromptTemplateController extends Controller
{
    /**
     * Display a listing of templates.
     */
    public function index(Request $request)
    {
        $query = ImagePromptTemplate::withCount('submissions');

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('prompt', 'like', '%' . $request->search . '%');
            });
        }

        $templates = $query->latest()->paginate(12);

        // Return JSON for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.image-templates.partials.template-cards', compact('templates'))->render(),
                'has_more' => $templates->hasMorePages(),
                'next_page' => $templates->currentPage() + 1
            ]);
        }

        return view('admin.image-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new template.
     */
    public function create()
    {
        return view('admin.image-templates.create');
    }

    /**
     * Store a newly created template.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|in:image,video',
            'coins_required' => 'required|integer|min:1|max:1000',
            'description' => 'nullable|string|max:1000',
            'reference_image' => 'nullable|file|mimes:jpeg,jpg,png,gif,mp4,mov,avi,webm|max:51200', // 50MB for videos
            'prompt' => 'required|string|max:2000',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $request->only(['title', 'type', 'coins_required', 'description', 'prompt']);
            $data['is_active'] = $request->has('is_active');

            // Store reference image if provided
            if ($request->hasFile('reference_image')) {
                $data['reference_image_path'] = $request->file('reference_image')
                    ->store('image-templates/references', 'public');
            }

            ImagePromptTemplate::create($data);

            return redirect()
                ->route('admin.image-templates.index')
                ->with('success', 'Template created successfully.');

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to create template: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified template.
     */
    public function show($id)
    {
        $template = ImagePromptTemplate::withCount('submissions')->findOrFail($id);
        $recentSubmissions = $template->submissions()
            ->with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.image-templates.show', compact('template', 'recentSubmissions'));
    }

    /**
     * Show the form for editing the specified template.
     */
    public function edit($id)
    {
        $template = ImagePromptTemplate::findOrFail($id);
        return view('admin.image-templates.edit', compact('template'));
    }

    /**
     * Update the specified template.
     */
    public function update(Request $request, $id)
    {
        $template = ImagePromptTemplate::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|in:image,video',
            'coins_required' => 'required|integer|min:1|max:1000',
            'description' => 'nullable|string|max:1000',
            'reference_image' => 'nullable|file|mimes:jpeg,jpg,png,gif,mp4,mov,avi,webm|max:51200',
            'prompt' => 'required|string|max:2000',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $request->only(['title', 'type', 'coins_required', 'description', 'prompt']);
            $data['is_active'] = $request->has('is_active');

            // Update reference image if provided
            if ($request->hasFile('reference_image')) {
                // Delete old image
                if ($template->reference_image_path && Storage::disk('public')->exists($template->reference_image_path)) {
                    Storage::disk('public')->delete($template->reference_image_path);
                }

                $data['reference_image_path'] = $request->file('reference_image')
                    ->store('image-templates/references', 'public');
            }

            $template->update($data);

            return redirect()
                ->route('admin.image-templates.index')
                ->with('success', 'Template updated successfully.');

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to update template: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified template.
     */
    public function destroy($id)
    {
        try {
            $template = ImagePromptTemplate::findOrFail($id);

            // Delete reference image
            if ($template->reference_image_path && Storage::disk('public')->exists($template->reference_image_path)) {
                Storage::disk('public')->delete($template->reference_image_path);
            }

            $template->delete();

            return redirect()
                ->route('admin.image-templates.index')
                ->with('success', 'Template deleted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete template: ' . $e->getMessage());
        }
    }

    /**
     * Toggle template active status.
     */
    public function toggleStatus($id)
    {
        try {
            $template = ImagePromptTemplate::findOrFail($id);
            $template->update(['is_active' => !$template->is_active]);

            return back()->with('success', 'Template status updated successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    /**
     * View all submissions for a template.
     */
    public function submissions($id)
    {
        $template = ImagePromptTemplate::findOrFail($id);
        $submissions = $template->submissions()
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('admin.image-templates.submissions', compact('template', 'submissions'));
    }
}
