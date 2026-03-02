<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    /**
     * Sensitive keys that should NEVER be exposed to clients
     */
    private $sensitiveKeys = [
        'razorpay_secret',
        'stripe_secret',
        'aws_secret_key',
        'database_password',
        'mail_password',
        'jwt_secret',
        'grok_api_key',
        'openai_api_key',
        'anthropic_api_key',
        'api_secret',
        'secret_key'
    ];

    /**
     * Get all settings (excluding sensitive data)
     */
    public function index()
    {
        try {
            $settings = Setting::whereNotIn('key', $this->sensitiveKeys)->get();
            
            return response()->json([
                'success' => true,
                'data' => $settings
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get settings by group (excluding sensitive data)
     */
    public function getByGroup($group)
    {
        try {
            $settings = Setting::where('group', $group)
                ->whereNotIn('key', $this->sensitiveKeys)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $settings
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific setting by key (excluding sensitive data)
     */
    public function show($key)
    {
        try {
            // Prevent accessing sensitive keys
            if (in_array($key, $this->sensitiveKeys)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access to this setting is restricted'
                ], 403);
            }

            $setting = Setting::where('key', $key)->first();
            
            if (!$setting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Setting not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $setting
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch setting',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create or update a setting
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'key' => 'required|string|max:255',
                'value' => 'required',
                'type' => 'nullable|string|in:text,number,boolean,json',
                'group' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $setting = Setting::set(
                $request->key,
                $request->value,
                $request->type ?? 'text',
                $request->group ?? 'general'
            );

            return response()->json([
                'success' => true,
                'message' => 'Setting saved successfully',
                'data' => $setting
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save setting',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update multiple settings at once
     */
    public function bulkUpdate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'settings' => 'required|array',
                'settings.*.key' => 'required|string',
                'settings.*.value' => 'required',
                'settings.*.type' => 'nullable|string',
                'settings.*.group' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updated = [];
            foreach ($request->settings as $settingData) {
                $setting = Setting::set(
                    $settingData['key'],
                    $settingData['value'],
                    $settingData['type'] ?? 'text',
                    $settingData['group'] ?? 'general'
                );
                $updated[] = $setting;
            }

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully',
                'data' => $updated
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a setting
     */
    public function destroy($key)
    {
        try {
            $setting = Setting::where('key', $key)->first();
            
            if (!$setting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Setting not found'
                ], 404);
            }

            $setting->delete();
            Setting::clearCache();

            return response()->json([
                'success' => true,
                'message' => 'Setting deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete setting',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear settings cache
     */
    public function clearCache()
    {
        try {
            Setting::clearCache();

            return response()->json([
                'success' => true,
                'message' => 'Settings cache cleared successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment configuration (only public keys for Flutter app)
     */
    public function getPaymentConfig()
    {
        try {
            $config = [
                'razorpay' => [
                    'enabled' => Setting::get('razorpay_enabled', '0') === '1',
                    'key_id' => Setting::get('razorpay_key', ''), // Only public key
                    // razorpay_secret is NEVER sent to client
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $config
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
