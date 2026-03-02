<?php

namespace App\Console\Commands;

use App\Models\UserActivityLog;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CloseStaleActivitySessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity:close-stale {--hours=24 : Hours of inactivity before closing session}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close stale activity sessions that have been open for too long';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');
        $cutoffTime = Carbon::now()->subHours($hours);
        
        $this->info("Closing sessions that started before: {$cutoffTime->toDateTimeString()}");

        // Find all open sessions older than cutoff time
        $staleSessions = UserActivityLog::whereNull('session_end')
            ->where('session_start', '<', $cutoffTime)
            ->get();

        if ($staleSessions->isEmpty()) {
            $this->info('No stale sessions found.');
            return 0;
        }

        $closed = 0;
        foreach ($staleSessions as $session) {
            // Use the last updated time as session end, or cutoff time
            $sessionEnd = $session->updated_at->gt($session->session_start) 
                ? $session->updated_at 
                : $cutoffTime;
            
            $duration = abs($sessionEnd->diffInSeconds($session->session_start));
            
            $session->update([
                'session_end' => $sessionEnd,
                'duration' => $duration,
            ]);
            
            // Update user's total time spent
            if ($session->user) {
                $session->user->increment('total_time_spent', $duration);
            }
            
            $closed++;
            $this->line("Closed session ID {$session->id} for user {$session->user_id} (duration: " . gmdate('H:i:s', $duration) . ")");
        }

        $this->info("Successfully closed {$closed} stale sessions.");
        return 0;
    }
}
