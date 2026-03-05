<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@radhika.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'referral_code' => User::generateReferralCode(),
        ]);

        // Create sample user
        $testUser = User::create([
            'name' => 'Test User',
            'email' => 'user@radhika.com',
            'password' => Hash::make('user123'),
            'role' => 'user',
            'referral_code' => User::generateReferralCode(),
        ]);

        $this->command->info('Admin user created: admin@radhika.com / admin123');
        $this->command->info('Admin referral code: ' . $admin->referral_code);
        $this->command->info('Test user created: user@radhika.com / user123');
        $this->command->info('Test user referral code: ' . $testUser->referral_code);
    }
}
