<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ImagePromptTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TemplateController extends Controller
{
    /**
     * Get all templates
     */
    public function index(Request $request)
    {
        try {
            $query = ImagePromptTemplate::query();

            // Filter by type
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            // Filter by active status
            if ($request->has('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            // Sort by usage count or created date
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $templates = $query->get();

            return response()->json([
                'success' => true,
                'data' => $templates
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch templates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific template
     */
    public function show($id)
    {
        try {
            $template = ImagePromptTemplate::find($id);

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $template
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new template
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'type' => 'required|string|in:image,video',
                'description' => 'nullable|string',
                'prompt' => 'required|string',
                'reference_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                'is_active' => 'nullable|boolean',
                'coins_required' => 'nullable|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->only(['title', 'type', 'description', 'prompt', 'is_active', 'coins_required']);

            // Handle reference image upload
            if ($request->hasFile('reference_image')) {
                $image = $request->file('reference_image');
                $path = $image->store('templates', 'public');
                $data['reference_image_path'] = $path;
            }

            $template = ImagePromptTemplate::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Template created successfully',
                'data' => $template
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a template
     */
    public function update(Request $request, $id)
    {
        try {
            $template = ImagePromptTemplate::find($id);

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'type' => 'sometimes|required|string|in:image,video',
                'description' => 'nullable|string',
                'prompt' => 'sometimes|required|string',
                'reference_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                'is_active' => 'nullable|boolean',
                'coins_required' => 'nullable|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->only(['title', 'type', 'description', 'prompt', 'is_active', 'coins_required']);

            // Handle reference image upload
            if ($request->hasFile('reference_image')) {
                // Delete old image if exists
                if ($template->reference_image_path) {
                    Storage::disk('public')->delete($template->reference_image_path);
                }

                $image = $request->file('reference_image');
                $path = $image->store('templates', 'public');
                $data['reference_image_path'] = $path;
            }

            $template->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Template updated successfully',
                'data' => $template
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a template
     */
    public function destroy($id)
    {
        try {
            $template = ImagePromptTemplate::find($id);

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            // Delete reference image if exists
            if ($template->reference_image_path) {
                Storage::disk('public')->delete($template->reference_image_path);
            }

            $template->delete();

            return response()->json([
                'success' => true,
                'message' => 'Template deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle template active status
     */
    public function toggleActive($id)
    {
        try {
            $template = ImagePromptTemplate::find($id);

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            $template->is_active = !$template->is_active;
            $template->save();

            return response()->json([
                'success' => true,
                'message' => 'Template status updated successfully',
                'data' => $template
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update template status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get popular templates
     */
    public function popular(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            
            $templates = ImagePromptTemplate::where('is_active', true)
                ->orderBy('usage_count', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $templates
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch popular templates',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
