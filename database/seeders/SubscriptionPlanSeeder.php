<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'description' => 'Perfect for individuals getting started',
                'price' => 9.99,
                'duration_type' => 'monthly',
                'duration_value' => 1,
                'coins' => 100,
                'features' => json_encode([
                    'Access to basic features',
                    'Email support',
                    '5GB storage',
                    'Single user'
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'description' => 'Best for professionals and small teams',
                'price' => 29.99,
                'duration_type' => 'monthly',
                'duration_value' => 1,
                'coins' => 500,
                'features' => json_encode([
                    'All Basic features',
                    'Priority email support',
                    '50GB storage',
                    'Up to 5 users',
                    'Advanced analytics',
                    'API access'
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'description' => 'For large organizations with advanced needs',
                'price' => 99.99,
                'duration_type' => 'monthly',
                'duration_value' => 1,
                'coins' => 2000,
                'features' => json_encode([
                    'All Pro features',
                    '24/7 phone & email support',
                    'Unlimited storage',
                    'Unlimited users',
                    'Custom integrations',
                    'Dedicated account manager',
                    'SLA guarantee'
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'Annual Basic',
                'description' => 'Basic plan with annual billing (2 months free)',
                'price' => 99.99,
                'duration_type' => 'yearly',
                'duration_value' => 1,
                'coins' => 1200,
                'features' => json_encode([
                    'Access to basic features',
                    'Email support',
                    '5GB storage',
                    'Single user',
                    'Save 17% with annual billing'
                ]),
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::create($plan);
        }

        $this->command->info('Subscription plans created successfully!');
    }
}
