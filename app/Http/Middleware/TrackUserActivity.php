<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\UserActivityLog;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Update last activity
            $user->update(['last_activity_at' => now()]);
            
            // Track session if not already tracked
            $sessionId = session()->getId();
            $existingLog = UserActivityLog::where('user_id', $user->id)
                ->whereNull('session_end')
                ->latest()
                ->first();
            
            if (!$existingLog) {
                UserActivityLog::create([
                    'user_id' => $user->id,
                    'session_start' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'device_type' => $this->getDeviceType($request),
                ]);
            }
        }

        return $next($request);
    }

    /**
     * Detect device type from user agent
     */
    private function getDeviceType(Request $request): string
    {
        $userAgent = strtolower($request->userAgent());
        
        if (str_contains($userAgent, 'mobile') || str_contains($userAgent, 'android')) {
            return 'android';
        } elseif (str_contains($userAgent, 'iphone') || str_contains($userAgent, 'ipad')) {
            return 'ios';
        }
        
        return 'web';
    }
}
