<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class PurgeDeletedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:purge-deleted {--days=90 : Number of days after which to permanently delete soft-deleted users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete soft-deleted users after specified number of days (default: 90 days)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        
        if ($days < 1) {
            $this->error('Days must be at least 1');
            return 1;
        }

        $cutoffDate = Carbon::now()->subDays($days);
        
        $this->info("Searching for soft-deleted users older than {$days} days (before {$cutoffDate->toDateTimeString()})...");

        // Get users to be permanently deleted
        $usersToDelete = User::onlyTrashed()
            ->where('deleted_at', '<', $cutoffDate)
            ->get();

        if ($usersToDelete->isEmpty()) {
            $this->info('No users found for permanent deletion.');
            return 0;
        }

        $this->info("Found {$usersToDelete->count()} user(s) to permanently delete:");
        
        // Display users to be deleted
        $this->table(
            ['ID', 'Name', 'Email', 'Deleted At', 'Days Ago'],
            $usersToDelete->map(function ($user) {
                return [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->deleted_at->toDateTimeString(),
                    $user->deleted_at->diffInDays(Carbon::now()) . ' days'
                ];
            })
        );

        // Ask for confirmation in interactive mode
        if (!$this->option('no-interaction')) {
            if (!$this->confirm('Do you want to permanently delete these users? This action cannot be undone.')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        // Permanently delete users
        $deletedCount = 0;
        foreach ($usersToDelete as $user) {
            try {
                // Delete related data first
                $user->tokens()->forceDelete();
                $user->subscriptions()->forceDelete();
                $user->activityLogs()->forceDelete();
                
                // Permanently delete the user
                $user->forceDelete();
                $deletedCount++;
                
                $this->line("✓ Permanently deleted: {$user->email}");
            } catch (\Exception $e) {
                $this->error("✗ Failed to delete {$user->email}: {$e->getMessage()}");
            }
        }

        $this->info("\nCompleted! Permanently deleted {$deletedCount} user(s).");
        
        return 0;
    }
}
