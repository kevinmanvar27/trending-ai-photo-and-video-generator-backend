<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class GenerateReferralCodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generate referral codes for existing users who don't have one
        $users = User::whereNull('referral_code')->get();

        foreach ($users as $user) {
            $user->update([
                'referral_code' => User::generateReferralCode(),
            ]);
        }

        $this->command->info('Generated referral codes for ' . $users->count() . ' users.');
    }
}
