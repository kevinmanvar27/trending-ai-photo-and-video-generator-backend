<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display a listing of active pages.
     */
    public function index()
    {
        try {
            $pages = Page::active()->ordered()->get(['id', 'title', 'slug', 'meta_description', 'order']);
            
            return response()->json([
                'success' => true,
                'message' => 'Pages retrieved successfully',
                'data' => $pages
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified page by slug or ID.
     */
    public function show($identifier)
    {
        try {
            // Try to find by slug first, then by ID
            $page = Page::active()
                ->where('slug', $identifier)
                ->orWhere('id', $identifier)
                ->first();

            if (!$page) {
                return response()->json([
                    'success' => false,
                    'message' => 'Page not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Page retrieved successfully',
                'data' => [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'content' => $page->content,
                    'meta_description' => $page->meta_description,
                    'meta_keywords' => $page->meta_keywords,
                    'order' => $page->order,
                    'created_at' => $page->created_at,
                    'updated_at' => $page->updated_at
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve page',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
