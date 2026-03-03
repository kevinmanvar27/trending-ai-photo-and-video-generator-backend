<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    /**
     * Delete user account (soft delete)
     * User must provide email and password for verification
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = $request->user();

        // Verify email matches the authenticated user
        if ($user->email !== $request->email) {
            return response()->json([
                'success' => false,
                'message' => 'The provided email does not match your account.',
            ], 422);
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided password is incorrect.',
            ], 422);
        }

        // Check if user is admin (optional: prevent admin deletion)
        if ($user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Admin accounts cannot be deleted through this method.',
            ], 403);
        }

        try {
            // Revoke all tokens
            $user->tokens()->delete();

            // Soft delete the user
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Your account has been successfully deleted.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting your account. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
