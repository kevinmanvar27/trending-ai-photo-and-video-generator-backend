<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // Check if user is suspended
            if (Auth::user()->isSuspended()) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account has been suspended. Reason: ' . Auth::user()->suspension_reason,
                ]);
            }

            return redirect()->intended(route('home'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        // End current session in activity log
        if (Auth::check()) {
            $user = Auth::user();
            $activeLog = $user->activityLogs()
                ->whereNull('session_end')
                ->latest()
                ->first();

            if ($activeLog) {
                $sessionEnd = now();
                $sessionStart = $activeLog->session_start;
                
                // Calculate duration - ensure it's positive
                $duration = abs($sessionEnd->diffInSeconds($sessionStart));
                
                // Only update if session_end is after session_start
                if ($sessionEnd->gte($sessionStart)) {
                    $activeLog->update([
                        'session_end' => $sessionEnd,
                        'duration' => $duration,
                    ]);

                    // Update user's total time spent
                    $user->increment('total_time_spent', $duration);
                } else {
                    // If somehow session_end is before session_start, just mark as ended with 0 duration
                    \Log::warning("Session end time is before start time for log ID: {$activeLog->id}");
                    $activeLog->update([
                        'session_end' => $sessionEnd,
                        'duration' => 0,
                    ]);
                }
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
