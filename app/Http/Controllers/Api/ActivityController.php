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

        $duration = now()->diffInSeconds($activity->session_start);
        $activity->update([
            'session_end' => now(),
            'duration' => $duration,
        ]);

        // Update user's total time spent
        $request->user()->increment('total_time_spent', $duration);

        return response()->json([
            'success' => true,
            'message' => 'Session ended',
            'data' => [
                'duration' => $duration,
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
