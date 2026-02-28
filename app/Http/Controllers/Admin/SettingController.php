<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Display the settings page
     */
    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'site_title' => 'nullable|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'footer_text' => 'nullable|string|max:255',
            'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'site_favicon' => 'nullable|image|mimes:ico,png|max:512',
            'header_code' => 'nullable|string',
            'footer_code' => 'nullable|string',
            'razorpay_key' => 'nullable|string|max:255',
            'razorpay_secret' => 'nullable|string|max:255',
            'razorpay_enabled' => 'nullable|boolean',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        // Handle file uploads
        if ($request->hasFile('site_logo')) {
            $logoPath = $request->file('site_logo')->store('settings', 'public');
            
            // Delete old logo if exists
            $oldLogo = Setting::get('site_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            
            Setting::set('site_logo', $logoPath, 'image', 'appearance');
        }

        if ($request->hasFile('site_favicon')) {
            $faviconPath = $request->file('site_favicon')->store('settings', 'public');
            
            // Delete old favicon if exists
            $oldFavicon = Setting::get('site_favicon');
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }
            
            Setting::set('site_favicon', $faviconPath, 'image', 'appearance');
        }

        // Save text settings
        $textSettings = [
            'site_title' => ['type' => 'text', 'group' => 'general'],
            'site_description' => ['type' => 'textarea', 'group' => 'general'],
            'footer_text' => ['type' => 'text', 'group' => 'general'],
            'header_code' => ['type' => 'textarea', 'group' => 'appearance'],
            'footer_code' => ['type' => 'textarea', 'group' => 'appearance'],
            'razorpay_key' => ['type' => 'text', 'group' => 'payment'],
            'razorpay_secret' => ['type' => 'password', 'group' => 'payment'],
            'contact_email' => ['type' => 'text', 'group' => 'general'],
            'contact_phone' => ['type' => 'text', 'group' => 'general'],
            'address' => ['type' => 'textarea', 'group' => 'general'],
        ];

        foreach ($textSettings as $key => $config) {
            if ($request->has($key)) {
                Setting::set($key, $request->input($key), $config['type'], $config['group']);
            }
        }

        // Handle razorpay enabled checkbox
        Setting::set('razorpay_enabled', $request->has('razorpay_enabled') ? '1' : '0', 'boolean', 'payment');

        // Clear settings cache
        Setting::clearCache();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully!');
    }

    /**
     * Delete uploaded file (logo/favicon)
     */
    public function deleteFile(Request $request)
    {
        $request->validate([
            'key' => 'required|string|in:site_logo,site_favicon'
        ]);

        $filePath = Setting::get($request->key);
        
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        Setting::where('key', $request->key)->delete();
        Setting::clearCache();

        return response()->json(['success' => true]);
    }
}
