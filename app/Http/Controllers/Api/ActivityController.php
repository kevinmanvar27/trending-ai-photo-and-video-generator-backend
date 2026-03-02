<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Start a new session
     */
    public function startSession(Request $request)
    {
        $user = $request->user();
        
        // Close any existing open sessions for this user
        $existingLog = UserActivityLog::where('user_id', $user->id)
            ->whereNull('session_end')
            ->latest()
            ->first();
            
        if ($existingLog) {
            // Close the existing session
            $sessionEnd = now();
            $duration = abs($sessionEnd->diffInSeconds($existingLog->session_start));
            
            $existingLog->update([
                'session_end' => $sessionEnd,
                'duration' => $duration,
            ]);
            
            $user->increment('total_time_spent', $duration);
        }

        // Create new session
        $activity = UserActivityLog::create([
            'user_id' => $user->id,
            'session_start' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $request->input('device_type', 'mobile'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Session started',
            'data' => [
                'session_id' => $activity->id,
                'session_start' => $activity->session_start->toIso8601String(),
            ],
        ]);
    }

    /**
     * End a session
     */
    public function endSession(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:user_activity_logs,id',
        ]);

        $activity = UserActivityLog::findOrFail($request->session_id);

        if ($activity->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        // Check if session is already ended
        if ($activity->session_end) {
            return response()->json([
                'success' => false,
                'message' => 'Session already ended',
                'data' => [
                    'duration' => $activity->duration,
                    'session_end' => $activity->session_end->toIso8601String(),
                ],
            ], 400);
        }

        $sessionEnd = now();
        $sessionStart = $activity->session_start;
        
        // Calculate duration - ensure it's positive
        $duration = abs($sessionEnd->diffInSeconds($sessionStart));
        
        // Validate that session_end is after session_start
        if ($sessionEnd->lt($sessionStart)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid session end time',
            ], 400);
        }
        
        $activity->update([
            'session_end' => $sessionEnd,
            'duration' => $duration,
        ]);

        // Update user's total time spent
        $request->user()->increment('total_time_spent', $duration);

        return response()->json([
            'success' => true,
            'message' => 'Session ended',
            'data' => [
                'duration' => $duration,
                'session_start' => $activity->session_start->toIso8601String(),
                'session_end' => $activity->session_end->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get user activity history
     */
    public function history(Request $request)
    {
        $activities = $request->user()
            ->activityLogs()
            ->latest('session_start')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }
}
