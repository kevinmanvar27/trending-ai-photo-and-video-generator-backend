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

        // Award signup bonus to ALL new users (regardless of referral code)
        $signupBonusReceived = ReferralService::awardSignupBonus($user->id);
        if ($signupBonusReceived > 0) {
            $user->refresh(); // Refresh to get updated coins value
        }

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
                'signup_bonus_received' => $signupBonusReceived,
                'bonus_coins_received' => $bonusCoinsReceived,
                'total_coins_received' => $signupBonusReceived + $bonusCoinsReceived,
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
        // Log incoming request
        \Log::info('=== GOOGLE LOGIN REQUEST ===', [
            'timestamp' => now(),
            'ip' => $request->ip(),
            'has_id_token' => $request->has('id_token'),
            'id_token_length' => $request->has('id_token') ? strlen($request->id_token) : 0,
        ]);

        // Check if Google login is enabled
        $googleLoginEnabled = Setting::getBool('google_login_enabled', false);
        
        \Log::info('Google login enabled check', ['enabled' => $googleLoginEnabled]);
        
        if (!$googleLoginEnabled) {
            \Log::warning('Google login is disabled');
            return response()->json([
                'success' => false,
                'message' => 'Google login is currently disabled',
            ], 403);
        }

        try {
            $request->validate([
                'id_token' => 'required|string',
                'referral_code' => 'nullable|string|max:20',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', ['errors' => $e->errors()]);
            throw $e;
        }

        try {
            // Get Google Client ID from settings
            $googleClientId = Setting::get('google_client_id');
            
            \Log::info('Google Client ID', [
                'client_id' => $googleClientId,
                'is_empty' => empty($googleClientId),
            ]);
            
            if (empty($googleClientId)) {
                \Log::error('Google Client ID not configured');
                return response()->json([
                    'success' => false,
                    'message' => 'Google login is not properly configured. Please contact administrator.',
                ], 500);
            }

            // Initialize Google Client
            $client = new \Google\Client(['client_id' => $googleClientId]);
            
            \Log::info('Attempting token verification...');
            
            // Verify the ID token
            $payload = $client->verifyIdToken($request->id_token);
            
            if (!$payload) {
                \Log::error('Token verification failed', [
                    'token_preview' => substr($request->id_token, 0, 50) . '...',
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Google token',
                ], 401);
            }

            \Log::info('Token verified successfully', [
                'email' => $payload['email'] ?? 'unknown',
                'sub' => $payload['sub'] ?? 'unknown',
            ]);

            // Extract user information from Google payload
            $googleId = $payload['sub'];
            $email = $payload['email'];
            $name = $payload['name'] ?? '';
            $emailVerified = $payload['email_verified'] ?? false;

            // Check if user exists by email
            $user = User::where('email', $email)->first();

            if ($user) {
                \Log::info('Existing user found', ['user_id' => $user->id, 'email' => $email]);
                
                // User exists - check if suspended
                if ($user->isSuspended()) {
                    \Log::warning('User is suspended', ['user_id' => $user->id]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Your account has been suspended.',
                        'reason' => $user->suspension_reason,
                    ], 403);
                }

                // Login existing user
                $token = $user->createToken('mobile-app')->plainTextToken;

                \Log::info('User logged in successfully', ['user_id' => $user->id]);

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
                \Log::info('Creating new user', ['email' => $email, 'name' => $name]);
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

                // Award signup bonus to ALL new users (regardless of referral code)
                $signupBonusReceived = ReferralService::awardSignupBonus($user->id);
                if ($signupBonusReceived > 0) {
                    $user->refresh(); // Refresh to get updated coins value
                }

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

                \Log::info('New user created successfully', [
                    'user_id' => $user->id,
                    'email' => $email,
                    'signup_bonus' => $signupBonusReceived,
                    'referral_bonus' => $bonusCoinsReceived,
                ]);

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
                        'signup_bonus_received' => $signupBonusReceived,
                        'bonus_coins_received' => $bonusCoinsReceived,
                        'total_coins_received' => $signupBonusReceived + $bonusCoinsReceived,
                    ],
                ], 201);
            }

        } catch (\Google\Exception $e) {
            \Log::error('Google Exception in googleLogin', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Google authentication failed',
                'error' => $e->getMessage(),
            ], 401);
        } catch (\Exception $e) {
            \Log::error('Exception in googleLogin', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Authentication error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
