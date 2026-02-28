<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;

class CheckGrokApiKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grok:check-api-key';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and display the current Grok API key configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Grok API Key Configuration...');
        $this->newLine();

        // Check database setting
        try {
            $dbKey = Setting::get('grok_api_key');
            if ($dbKey && !empty($dbKey)) {
                $maskedKey = $this->maskApiKey($dbKey);
                $this->info("✓ Database Setting: {$maskedKey}");
                
                if ($dbKey === 'your_openai_api_key_here') {
                    $this->error('  ⚠ WARNING: API key is set to placeholder value!');
                    $this->warn('  Please update it in Admin Settings with your actual API key from https://console.x.ai/');
                }
            } else {
                $this->warn('✗ Database Setting: Not set or empty');
            }
        } catch (\Exception $e) {
            $this->error('✗ Database Setting: Error - ' . $e->getMessage());
        }

        // Check environment variable
        $envKey = env('GROK_API_KEY');
        if ($envKey && !empty($envKey)) {
            $maskedKey = $this->maskApiKey($envKey);
            $this->info("✓ Environment Variable (GROK_API_KEY): {$maskedKey}");
            
            if ($envKey === 'your_openai_api_key_here') {
                $this->error('  ⚠ WARNING: API key is set to placeholder value!');
            }
        } else {
            $this->warn('✗ Environment Variable (GROK_API_KEY): Not set or empty');
        }

        // Check config
        $configKey = config('image-prompt.grok.api_key');
        if ($configKey && !empty($configKey)) {
            $maskedKey = $this->maskApiKey($configKey);
            $this->info("✓ Config (image-prompt.grok.api_key): {$maskedKey}");
            
            if ($configKey === 'your_openai_api_key_here') {
                $this->error('  ⚠ WARNING: API key is set to placeholder value!');
            }
        } else {
            $this->warn('✗ Config (image-prompt.grok.api_key): Not set or empty');
        }

        $this->newLine();
        $this->info('Priority Order: Database > Environment > Config');
        $this->newLine();

        // Check which one will be used
        try {
            $dbKey = Setting::get('grok_api_key');
            if ($dbKey && !empty($dbKey) && $dbKey !== 'your_openai_api_key_here') {
                $this->info('✓ Currently Using: Database Setting');
                return Command::SUCCESS;
            }
        } catch (\Exception $e) {
            // Continue to next check
        }

        if ($envKey && !empty($envKey) && $envKey !== 'your_openai_api_key_here') {
            $this->info('✓ Currently Using: Environment Variable');
            return Command::SUCCESS;
        }

        if ($configKey && !empty($configKey) && $configKey !== 'your_openai_api_key_here') {
            $this->info('✓ Currently Using: Config');
            return Command::SUCCESS;
        }

        $this->error('✗ No valid API key found!');
        $this->warn('Please set your Grok API key in Admin Settings or .env file');
        $this->warn('Get your API key from: https://console.x.ai/');
        
        return Command::FAILURE;
    }

    /**
     * Mask API key for display
     */
    private function maskApiKey($key)
    {
        if (strlen($key) <= 8) {
            return str_repeat('*', strlen($key));
        }
        
        return substr($key, 0, 4) . str_repeat('*', strlen($key) - 8) . substr($key, -4);
    }
}
