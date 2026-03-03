<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Setting;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Google\Client as GoogleClient;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'referral_code' => 'nullable|string|max:20',
        ]);

        // Generate unique referral code for the new user
        $referralCode = User::generateReferralCode();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'referral_code' => $referralCode,
        ]);

        // Apply referral code if provided and award bonus coins
        $bonusCoinsReceived = 0;
        if ($request->has('referral_code') && !empty($request->referral_code)) {
            $referralApplied = ReferralService::applyReferralCode($user->id, $request->referral_code);
            
            // Award bonus coins to new user if referral was successful
            if ($referralApplied && ReferralService::isReferralSystemEnabled()) {
                $bonusCoinsReceived = ReferralService::getNewUserBonusAmount();
                $user->increment('referral_coins', $bonusCoinsReceived);
                $user->refresh(); // Refresh to get updated coins value
            }
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'referral_code' => $user->referral_code,
                    'referral_coins' => $user->referral_coins,
                ],
                'token' => $token,
                'bonus_coins_received' => $bonusCoinsReceived,
            ],
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if user is suspended
        if ($user->isSuspended()) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been suspended.',
                'reason' => $user->suspension_reason,
            ], 403);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get user profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        
        // Get active subscription
        $activeSubscription = \App\Models\UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('plan')
            ->first();

        // Format subscription data
        $subscriptionData = null;
        if ($activeSubscription && $activeSubscription->isActive()) {
            $daysRemaining = 0;
            if ($activeSubscription->expires_at) {
                $daysRemaining = max(0, now()->diffInDays($activeSubscription->expires_at, false));
            }

            $subscriptionData = [
                'plan_name' => $activeSubscription->plan->name,
                'status' => $activeSubscription->status,
                'expires_at' => $activeSubscription->expires_at,
                'days_remaining' => $daysRemaining,
                'remaining_coins' => $activeSubscription->remaining_coins,
            ];
        }

        // Get referral statistics
        $referralStats = [
            'referral_code' => $user->referral_code,
            'referral_coins' => $user->referral_coins,
            'total_referrals' => $user->referrals()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? null,
                'avatar' => null, // Add avatar URL if you have avatar field
                'created_at' => $user->created_at,
                'subscription' => $subscriptionData,
                'referral' => $referralStats,
            ],
        ]);
    }

    /**
     * Google Login - Verify Google token and create/login user
     */
    public function googleLogin(Request $request)
    {
        // Check if Google login is enabled
        $googleLoginEnabled = Setting::getBool('google_login_enabled', false);
        
        if (!$googleLoginEnabled) {
            return response()->json([
                'success' => false,
                'message' => 'Google login is currently disabled',
            ], 403);
        }

        $request->validate([
            'id_token' => 'required|string',
            'referral_code' => 'nullable|string|max:20',
        ]);

        try {
            // Get Google Client ID from settings
            $googleClientId = Setting::get('google_client_id');
            
            if (empty($googleClientId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google login is not properly configured. Please contact administrator.',
                ], 500);
            }

            // Initialize Google Client
            $client = new \Google\Client(['client_id' => $googleClientId]);
            
            // Verify the ID token
            $payload = $client->verifyIdToken($request->id_token);
            
            if (!$payload) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Google token',
                ], 401);
            }

            // Extract user information from Google payload
            $googleId = $payload['sub'];
            $email = $payload['email'];
            $name = $payload['name'] ?? '';
            $emailVerified = $payload['email_verified'] ?? false;

            // Check if user exists by email
            $user = User::where('email', $email)->first();

            if ($user) {
                // User exists - check if suspended
                if ($user->isSuspended()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Your account has been suspended.',
                        'reason' => $user->suspension_reason,
                    ], 403);
                }

                // Login existing user
                $token = $user->createToken('mobile-app')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'data' => [
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'referral_code' => $user->referral_code,
                            'referral_coins' => $user->referral_coins,
                        ],
                        'token' => $token,
                        'is_new_user' => false,
                    ],
                ]);
            } else {
                // Create new user
                $referralCode = User::generateReferralCode();
                
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make(uniqid()), // Random password for Google users
                    'role' => 'user',
                    'referral_code' => $referralCode,
                    'email_verified_at' => $emailVerified ? now() : null,
                ]);

                // Apply referral code if provided and award bonus coins
                $bonusCoinsReceived = 0;
                if ($request->has('referral_code') && !empty($request->referral_code)) {
                    $referralApplied = ReferralService::applyReferralCode($user->id, $request->referral_code);
                    
                    // Award bonus coins to new user if referral was successful
                    if ($referralApplied && ReferralService::isReferralSystemEnabled()) {
                        $bonusCoinsReceived = ReferralService::getNewUserBonusAmount();
                        $user->increment('referral_coins', $bonusCoinsReceived);
                        $user->refresh();
                    }
                }

                // Create token for new user
                $token = $user->createToken('mobile-app')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'message' => 'Registration successful',
                    'data' => [
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'referral_code' => $user->referral_code,
                            'referral_coins' => $user->referral_coins,
                        ],
                        'token' => $token,
                        'is_new_user' => true,
                        'bonus_coins_received' => $bonusCoinsReceived,
                    ],
                ], 201);
            }

        } catch (\Google\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google authentication failed',
                'error' => $e->getMessage(),
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
