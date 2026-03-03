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
                'coins' => 100,
                'features' => json_encode([
                    '100 coins for AI generations',
                    'Email support',
                    'Basic templates',
                    'Standard quality output'
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'description' => 'Best for professionals and small teams',
                'price' => 29.99,
                'coins' => 500,
                'features' => json_encode([
                    '500 coins for AI generations',
                    'Priority email support',
                    'Premium templates',
                    'High quality output',
                    'Advanced features',
                    'API access'
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'description' => 'For large organizations with advanced needs',
                'price' => 99.99,
                'coins' => 2500,
                'features' => json_encode([
                    '2500 coins for AI generations',
                    '24/7 phone & email support',
                    'All premium templates',
                    'Ultra quality output',
                    'Custom integrations',
                    'Dedicated account manager',
                    'Priority processing'
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'Starter',
                'description' => 'Try out our AI generation service',
                'price' => 4.99,
                'coins' => 50,
                'features' => json_encode([
                    '50 coins for AI generations',
                    'Email support',
                    'Basic templates',
                    'Standard quality output'
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
