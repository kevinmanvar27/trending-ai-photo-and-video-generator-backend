<?php

namespace App\Console\Commands;

use App\Models\UserActivityLog;
use Illuminate\Console\Command;

class FixActivityLogDurations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity:fix-durations {--sync-time : Also sync user total_time_spent after fixing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix negative or incorrect durations in activity logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing activity log durations and timestamps...');

        // Get all activity logs with session_end set
        $logs = UserActivityLog::whereNotNull('session_end')->get();
        
        $fixed = 0;
        $swapped = 0;
        $skipped = 0;

        foreach ($logs as $log) {
            $needsUpdate = false;
            $changes = [];
            
            if ($log->session_start && $log->session_end) {
                // Check if session_end is before session_start
                if ($log->session_end->lt($log->session_start)) {
                    $this->warn("Log ID {$log->id}: session_end is before session_start. Swapping...");
                    $this->line("  Before: Start={$log->session_start}, End={$log->session_end}");
                    
                    // Swap the times
                    $temp = $log->session_start;
                    $log->session_start = $log->session_end;
                    $log->session_end = $temp;
                    
                    $this->line("  After:  Start={$log->session_start}, End={$log->session_end}");
                    $swapped++;
                    $needsUpdate = true;
                    $changes[] = 'swapped timestamps';
                }
                
                // Recalculate duration
                $correctDuration = abs($log->session_end->diffInSeconds($log->session_start));
                
                // Check if duration needs updating
                if ($log->getRawOriginal('duration') != $correctDuration) {
                    $this->line("Log ID {$log->id}: Updating duration from {$log->getRawOriginal('duration')} to {$correctDuration} seconds");
                    $log->duration = $correctDuration;
                    $needsUpdate = true;
                    $changes[] = 'fixed duration';
                }
                
                if ($needsUpdate) {
                    $log->save();
                    $fixed++;
                    $this->info("✓ Fixed log ID {$log->id}: " . implode(', ', $changes));
                } else {
                    $skipped++;
                }
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("  - Fixed: {$fixed} records");
        $this->info("  - Swapped timestamps: {$swapped} records");
        $this->info("  - Skipped (already correct): {$skipped} records");
        $this->info('Done!');
        
        // Optionally sync user time spent
        if ($this->option('sync-time')) {
            $this->newLine();
            $this->info('Syncing user time spent...');
            $this->call('users:sync-time-spent');
        }

        return 0;
    }
}
