<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Helpers\EnvHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

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
            'grok_api_key' => 'nullable|string|max:255',
            'grok_vision_api_url' => 'nullable|url|max:500',
            'grok_vision_model' => 'nullable|string|max:100',
            'grok_imagine_api_url' => 'nullable|url|max:500',
            'grok_video_api_url' => 'nullable|url|max:500',
            'grok_timeout' => 'nullable|integer|min:30|max:600',
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

        // Save text settings to database
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
            'grok_api_key' => ['type' => 'password', 'group' => 'api'],
            'grok_vision_api_url' => ['type' => 'text', 'group' => 'api'],
            'grok_vision_model' => ['type' => 'text', 'group' => 'api'],
            'grok_imagine_api_url' => ['type' => 'text', 'group' => 'api'],
            'grok_video_api_url' => ['type' => 'text', 'group' => 'api'],
            'grok_timeout' => ['type' => 'number', 'group' => 'api'],
        ];

        foreach ($textSettings as $key => $config) {
            if ($request->has($key)) {
                Setting::set($key, $request->input($key), $config['type'], $config['group']);
            }
        }

        // Handle razorpay enabled checkbox
        Setting::set('razorpay_enabled', $request->has('razorpay_enabled') ? '1' : '0', 'boolean', 'payment');

        // Prepare data for .env file update (map settings to ENV variables)
        $envData = [];
        
        // Map Grok API settings to .env variables
        if ($request->has('grok_api_key')) {
            $envData['GROK_API_KEY'] = $request->input('grok_api_key');
        }
        if ($request->has('grok_vision_api_url')) {
            $envData['GROK_VISION_API_URL'] = $request->input('grok_vision_api_url');
        }
        if ($request->has('grok_vision_model')) {
            $envData['GROK_VISION_MODEL'] = $request->input('grok_vision_model');
        }
        if ($request->has('grok_imagine_api_url')) {
            $envData['GROK_IMAGINE_API_URL'] = $request->input('grok_imagine_api_url');
        }
        if ($request->has('grok_video_api_url')) {
            $envData['GROK_VIDEO_API_URL'] = $request->input('grok_video_api_url');
        }
        if ($request->has('grok_timeout')) {
            $envData['GROK_TIMEOUT'] = $request->input('grok_timeout');
        }

        // Update .env file with API settings
        if (!empty($envData)) {
            try {
                EnvHelper::updateMultipleEnv($envData);
                
                // Clear config cache to reload .env values
                Artisan::call('config:clear');
            } catch (\Exception $e) {
                // Log error but don't fail the entire update
                \Log::error('Failed to update .env file: ' . $e->getMessage());
            }
        }

        // Clear settings cache
        Setting::clearCache();
        
        // Also clear config cache to ensure new API keys are loaded
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
        } catch (\Exception $e) {
            \Log::warning('Failed to clear cache: ' . $e->getMessage());
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully! (Database & .env file updated). Cache cleared.');
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

    /**
     * Test the Grok API key
     */
    public function testApiKey(Request $request)
    {
        try {
            // Get API key from request or database
            $apiKey = $request->input('api_key');
            
            if (empty($apiKey)) {
                $apiKey = Setting::get('grok_api_key');
            }
            
            if (empty($apiKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No API key provided. Please enter an API key first.'
                ], 400);
            }
            
            if ($apiKey === 'your_openai_api_key_here') {
                return response()->json([
                    'success' => false,
                    'message' => 'API key is set to placeholder value. Please enter your actual API key from https://console.x.ai/'
                ], 400);
            }
            
            // Test the API key with a simple request (use text-only model for testing)
            $apiUrl = Setting::get('grok_vision_api_url', config('image-prompt.grok.vision_api_url'));
            // Use grok-beta for testing as it's a text-only model that always works
            $testModel = 'grok-beta';
            
            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($apiUrl, [
                    'model' => $testModel,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => 'Test connection'
                        ]
                    ],
                    'max_tokens' => 10
                ]);
            
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => '✓ API key is valid and working! Connection successful.'
                ]);
            }
            
            // Check for specific error types
            $errorData = $response->json();
            $errorMessage = 'API request failed';
            
            if (isset($errorData['error']['message'])) {
                $errorMessage = $errorData['error']['message'];
            } elseif (isset($errorData['error'])) {
                $errorMessage = is_string($errorData['error']) ? $errorData['error'] : json_encode($errorData['error']);
            }
            
            return response()->json([
                'success' => false,
                'message' => '✗ API key test failed: ' . $errorMessage,
                'status' => $response->status()
            ], $response->status());
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '✗ Error testing API key: ' . $e->getMessage()
            ], 500);
        }
    }
}
