<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReferralSettingsController extends Controller
{
    /**
     * Get all referral settings
     */
    public function getReferralSettings()
    {
        try {
            $settings = [
                'referral_coins_per_referral' => (int) Setting::get('referral_coins_per_referral', 100),
                'referral_bonus_for_new_user' => (int) Setting::get('referral_bonus_for_new_user', 50),
                'referral_system_enabled' => Setting::getBool('referral_system_enabled', true),
            ];

            return response()->json([
                'success' => true,
                'data' => $settings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch referral settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update referral coins per referral
     */
    public function updateReferralCoins(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coins' => 'required|integer|min:0|max:10000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            Setting::set('referral_coins_per_referral', $request->coins, 'number', 'referral');

            return response()->json([
                'success' => true,
                'message' => 'Referral coins updated successfully',
                'data' => [
                    'referral_coins_per_referral' => (int) $request->coins
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update referral coins',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update new user bonus coins
     */
    public function updateNewUserBonus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bonus' => 'required|integer|min:0|max:10000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            Setting::set('referral_bonus_for_new_user', $request->bonus, 'number', 'referral');

            return response()->json([
                'success' => true,
                'message' => 'New user bonus updated successfully',
                'data' => [
                    'referral_bonus_for_new_user' => (int) $request->bonus
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update new user bonus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update all referral settings at once
     */
    public function updateAllSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'referral_coins_per_referral' => 'required|integer|min:0|max:10000',
            'referral_bonus_for_new_user' => 'required|integer|min:0|max:10000',
            'referral_system_enabled' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            Setting::set('referral_coins_per_referral', $request->referral_coins_per_referral, 'number', 'referral');
            Setting::set('referral_bonus_for_new_user', $request->referral_bonus_for_new_user, 'number', 'referral');
            Setting::set('referral_system_enabled', $request->referral_system_enabled ? '1' : '0', 'boolean', 'referral');

            return response()->json([
                'success' => true,
                'message' => 'All referral settings updated successfully',
                'data' => [
                    'referral_coins_per_referral' => (int) $request->referral_coins_per_referral,
                    'referral_bonus_for_new_user' => (int) $request->referral_bonus_for_new_user,
                    'referral_system_enabled' => (bool) $request->referral_system_enabled
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update referral settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle referral system on/off
     */
    public function toggleReferralSystem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'enabled' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            Setting::set('referral_system_enabled', $request->enabled ? '1' : '0', 'boolean', 'referral');

            return response()->json([
                'success' => true,
                'message' => 'Referral system ' . ($request->enabled ? 'enabled' : 'disabled') . ' successfully',
                'data' => [
                    'referral_system_enabled' => (bool) $request->enabled
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle referral system',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
