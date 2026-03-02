<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Console\Command;

class SyncUserTimeSpent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:sync-time-spent {--user-id= : Sync specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync total_time_spent for users from their activity logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Syncing user time spent from activity logs...');
        
        $userId = $this->option('user-id');
        
        if ($userId) {
            $users = User::where('id', $userId)->get();
            if ($users->isEmpty()) {
                $this->error("User with ID {$userId} not found.");
                return 1;
            }
        } else {
            $users = User::all();
        }

        $updated = 0;
        $skipped = 0;

        foreach ($users as $user) {
            // Calculate total time from activity logs
            $totalSeconds = UserActivityLog::where('user_id', $user->id)
                ->whereNotNull('session_end')
                ->sum('duration');
            
            $currentTotal = $user->total_time_spent ?? 0;
            
            if ($currentTotal != $totalSeconds) {
                $user->update(['total_time_spent' => $totalSeconds]);
                
                $formattedOld = gmdate('H:i:s', $currentTotal);
                $formattedNew = gmdate('H:i:s', $totalSeconds);
                
                $this->line("✓ User ID {$user->id} ({$user->name}): {$formattedOld} → {$formattedNew}");
                $updated++;
            } else {
                $this->line("  User ID {$user->id} ({$user->name}): Already synced ({$user->formatted_time_spent})");
                $skipped++;
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("  - Updated: {$updated} users");
        $this->info("  - Skipped (already synced): {$skipped} users");
        $this->info('Done!');

        return 0;
    }
}
