<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    /**
     * Show delete account form (Public - No authentication required)
     */
    public function showDeleteForm()
    {
        return view('auth.delete-account');
    }

    /**
     * Delete user account (soft delete)
     * User provides email and password for verification
     * NO LOGIN REQUIRED - Public access
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        // Check if user exists
        if (!$user) {
            return back()->withErrors([
                'email' => 'No account found with this email address.',
            ])->withInput($request->only('email'));
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'The provided password is incorrect.',
            ])->withInput($request->only('email'));
        }

        // Check if user is already soft deleted
        if ($user->trashed()) {
            return back()->withErrors([
                'email' => 'This account has already been deleted.',
            ])->withInput($request->only('email'));
        }

        // Optional: Prevent admin deletion
        if ($user->role === 'admin') {
            return back()->withErrors([
                'error' => 'Admin accounts cannot be deleted through this method. Please contact support.',
            ])->withInput($request->only('email'));
        }

        try {
            // If user is currently logged in, end their session
            if (Auth::check() && Auth::id() === $user->id) {
                // End current session in activity log
                $activeLog = $user->activityLogs()
                    ->whereNull('session_end')
                    ->latest()
                    ->first();

                if ($activeLog) {
                    $duration = now()->diffInSeconds($activeLog->session_start);
                    $activeLog->update([
                        'session_end' => now(),
                        'duration' => $duration,
                    ]);

                    // Update user's total time spent
                    $user->increment('total_time_spent', $duration);
                }

                // Logout the user
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            // Soft delete the user
            $user->delete();

            return redirect()->route('login')->with('success', 'Your account has been successfully deleted. We\'re sorry to see you go.');
        } catch (\Exception $e) {
            \Log::error('Account deletion failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'An error occurred while deleting your account. Please try again or contact support.',
            ])->withInput($request->only('email'));
        }
    }
}
