<?php
/**
 * Check Database Settings
 * Upload to public folder and visit: https://trends.rektech.work/check-settings.php
 * DELETE THIS FILE after use!
 */

$secret = 'check-settings-2025';
if (!isset($_GET['secret']) || $_GET['secret'] !== $secret) {
    die('Access denied. Use: ?secret=' . $secret);
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<h1>Checking Grok Settings</h1>";
echo "<pre>";

try {
    $settings = \App\Models\Setting::whereIn('key', [
        'grok_api_key',
        'grok_vision_model',
        'grok_vision_api_url',
        'grok_imagine_model',
        'grok_video_model'
    ])->get();
    
    echo "Database Settings:\n";
    echo str_repeat("=", 50) . "\n";
    
    if ($settings->isEmpty()) {
        echo "No Grok settings found in database.\n";
        echo "✓ This is good - will use .env values\n";
    } else {
        foreach ($settings as $setting) {
            $value = $setting->value;
            if ($setting->key === 'grok_api_key') {
                $value = substr($value, 0, 15) . '...';
            }
            echo "{$setting->key}: {$value}\n";
        }
        
        echo "\n⚠️ Database settings override .env!\n";
        echo "To use .env values, delete these from database.\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "\n.env Values:\n";
    echo str_repeat("=", 50) . "\n";
    echo "GROK_VISION_MODEL: " . env('GROK_VISION_MODEL', 'NOT SET') . "\n";
    echo "GROK_API_KEY: " . substr(env('GROK_API_KEY', 'NOT SET'), 0, 15) . "...\n";
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "\nConfig Values (Final):\n";
    echo str_repeat("=", 50) . "\n";
    echo "vision_model: " . config('image-prompt.grok.vision_model') . "\n";
    echo "vision_api_url: " . config('image-prompt.grok.vision_api_url') . "\n";
    echo "imagine_model: " . config('image-prompt.grok.imagine_model') . "\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
echo "<p><strong>IMPORTANT:</strong> Delete this file now!</p>";
