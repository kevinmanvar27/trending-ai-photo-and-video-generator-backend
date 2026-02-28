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
        User::create([
            'name' => 'Admin',
            'email' => 'admin@radhika.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // Create sample user
        User::create([
            'name' => 'Test User',
            'email' => 'user@radhika.com',
            'password' => Hash::make('user123'),
            'role' => 'user',
        ]);

        $this->command->info('Admin user created: admin@radhika.com / admin123');
        $this->command->info('Test user created: user@radhika.com / user123');
    }
}
