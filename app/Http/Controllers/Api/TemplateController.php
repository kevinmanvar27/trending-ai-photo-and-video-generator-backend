<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ImagePromptTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TemplateController extends Controller
{
    /**
     * Get all active templates
     */
    public function index(Request $request)
    {
        try {
            $templates = ImagePromptTemplate::where('is_active', true)
                ->latest()
                ->get()
                ->map(function ($template) {
                    return [
                        'id' => $template->id,
                        'name' => $template->title,
                        'description' => $template->description,
                        'category' => $this->getCategoryFromType($template->type),
                        'thumbnail' => $template->reference_image_url,
                        'prompt' => $template->prompt,
                        'type' => $template->type,
                        'coins_required' => $template->coins_required ?? $this->getCoinsRequired($template->type),
                        'is_active' => $template->is_active,
                        'usage_count' => $template->usage_count ?? 0,
                        'created_at' => $template->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $templates,
                'message' => 'Templates retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching templates: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve templates'
            ], 500);
        }
    }

    /**
     * Get template details by ID
     */
    public function show(Request $request, $id)
    {
        try {
            $template = ImagePromptTemplate::where('is_active', true)
                ->findOrFail($id);

            // Get sample outputs from recent submissions
            $sampleOutputs = $template->submissions()
                ->where('status', 'completed')
                ->whereNotNull('processed_image_path')
                ->latest()
                ->limit(3)
                ->get()
                ->map(function ($submission) {
                    return $submission->processed_image_url;
                })
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $template->id,
                    'name' => $template->title,
                    'description' => $template->description,
                    'category' => $this->getCategoryFromType($template->type),
                    'thumbnail' => $template->reference_image_url,
                    'prompt' => $template->prompt,
                    'type' => $template->type,
                    'coins_required' => $template->coins_required ?? $this->getCoinsRequired($template->type),
                    'settings' => [
                        'resolution' => '1024x1024',
                        'style' => 'realistic'
                    ],
                    'sample_outputs' => $sampleOutputs,
                    'is_active' => $template->is_active,
                    'usage_count' => $template->usage_count ?? 0,
                ],
                'message' => 'Template details retrieved successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found'
            ], 404);
            
        } catch (\Exception $e) {
            Log::error('Error fetching template: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve template details'
            ], 500);
        }
    }

    /**
     * Get category from template type
     */
    private function getCategoryFromType($type)
    {
        $categoryMap = [
            'image' => 'portrait',
            'video' => 'video',
        ];

        return $categoryMap[$type] ?? 'general';
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
