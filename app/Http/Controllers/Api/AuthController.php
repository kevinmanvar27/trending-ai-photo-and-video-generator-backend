<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'user' => $user,
                'token' => $token,
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
        $user = $request->user()->load('activeSubscription.plan');

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        // Build validation rules
        $rules = [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ];

        // Validate the request
        $request->validate($rules);

        try {
            // Update name if provided
            if ($request->has('name') && $request->name !== null) {
                $user->name = $request->name;
            }

            // Update email if provided
            if ($request->has('email') && $request->email !== null) {
                $user->email = $request->email;
            }

            // Update password if provided
            if ($request->has('password') && $request->password !== null) {
                $user->password = Hash::make($request->password);
            }

            // Save the user
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete user account (GET method)
     */
    public function deleteAccount(Request $request)
    {
        try {
            $user = $request->user();
            
            // Store user info before deletion
            $userName = $user->name;
            $userEmail = $user->email;
            
            // Delete all user's tokens
            $user->tokens()->delete();
            
            // Delete user's related data (optional - uncomment if needed)
            // $user->contacts()->delete();
            // $user->activitySessions()->delete();
            // $user->subscriptions()->delete();
            // $user->imageSubmissions()->delete();
            
            // Delete the user
            $user->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully',
                'data' => [
                    'deleted_user' => [
                        'name' => $userName,
                        'email' => $userEmail,
                    ],
                ],
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete user account via GET with email and password
     */
    public function deleteAccountViaCredentials(Request $request)
    {
        // Get email and password from query parameters
        $email = $request->query('email');
        $password = $request->query('password');

        // Validate required parameters
        if (!$email || !$password) {
            return response()->json([
                'success' => false,
                'message' => 'Email and password are required',
                'error' => 'Please provide both email and password parameters in the URL'
            ], 400);
        }

        try {
            // Find user by email
            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'error' => 'No user found with the provided email address'
                ], 404);
            }

            // Verify password
            if (!Hash::check($password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                    'error' => 'The provided password is incorrect'
                ], 401);
            }

            // Check if user is suspended
            if ($user->isSuspended()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is suspended',
                    'error' => 'Cannot delete a suspended account. Please contact support.',
                    'reason' => $user->suspension_reason
                ], 403);
            }

            // Store user info before deletion
            $userName = $user->name;
            $userEmail = $user->email;
            $userId = $user->id;

            // Delete all user's tokens
            $user->tokens()->delete();

            // Delete the user
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully',
                'data' => [
                    'deleted_user' => [
                        'id' => $userId,
                        'name' => $userName,
                        'email' => $userEmail,
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
