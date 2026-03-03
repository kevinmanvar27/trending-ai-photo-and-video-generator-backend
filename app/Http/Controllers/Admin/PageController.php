<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    /**
     * Display a listing of pages.
     */
    public function index()
    {
        $pages = Page::orderBy('order')->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.pages.index', compact('pages'));
    }

    /**
     * Show the form for creating a new page.
     */
    public function create()
    {
        return view('admin.pages.create');
    }

    /**
     * Store a newly created page in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
            'content' => 'required|string',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        // Ensure slug is unique
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Page::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $data['order'] = $request->input('order', 0);

        Page::create($data);

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Page created successfully!');
    }

    /**
     * Display the specified page.
     */
    public function show(Page $page)
    {
        return view('admin.pages.show', compact('page'));
    }

    /**
     * Show the form for editing the specified page.
     */
    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    /**
     * Update the specified page in storage.
     */
    public function update(Request $request, Page $page)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug,' . $page->id,
            'content' => 'required|string',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        // Ensure slug is unique (excluding current page)
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Page::where('slug', $data['slug'])->where('id', '!=', $page->id)->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $data['order'] = $request->input('order', 0);

        $page->update($data);

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Page updated successfully!');
    }

    /**
     * Remove the specified page from storage.
     */
    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Page deleted successfully!');
    }

    /**
     * Toggle page status.
     */
    public function toggleStatus($id)
    {
        $page = Page::findOrFail($id);
        $page->is_active = !$page->is_active;
        $page->save();

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Page status updated successfully!');
    }
}
