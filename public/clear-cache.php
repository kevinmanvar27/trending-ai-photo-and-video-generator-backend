<?php
/**
 * Emergency Cache Clear Script
 * Upload this to your public folder and visit: https://trends.rektech.work/clear-cache.php
 * DELETE THIS FILE after use for security!
 */

// Security: Only allow from specific IP or use a secret key
$secret = 'clear-cache-2025'; // Change this!
if (!isset($_GET['secret']) || $_GET['secret'] !== $secret) {
    die('Access denied. Use: ?secret=' . $secret);
}

echo "<h1>Clearing Laravel Cache on Hostinger</h1>";
echo "<pre>";

// Change to Laravel root directory
$laravelRoot = dirname(__DIR__);
chdir($laravelRoot);

echo "Current directory: " . getcwd() . "\n\n";

// Clear config cache
echo "1. Clearing config cache...\n";
exec('php artisan config:clear 2>&1', $output1, $return1);
echo implode("\n", $output1) . "\n";
echo "Status: " . ($return1 === 0 ? "✓ Success" : "✗ Failed") . "\n\n";

// Clear application cache
echo "2. Clearing application cache...\n";
exec('php artisan cache:clear 2>&1', $output2, $return2);
echo implode("\n", $output2) . "\n";
echo "Status: " . ($return2 === 0 ? "✓ Success" : "✗ Failed") . "\n\n";

// Clear route cache
echo "3. Clearing route cache...\n";
exec('php artisan route:clear 2>&1', $output3, $return3);
echo implode("\n", $output3) . "\n";
echo "Status: " . ($return3 === 0 ? "✓ Success" : "✗ Failed") . "\n\n";

// Clear view cache
echo "4. Clearing view cache...\n";
exec('php artisan view:clear 2>&1', $output4, $return4);
echo implode("\n", $output4) . "\n";
echo "Status: " . ($return4 === 0 ? "✓ Success" : "✗ Failed") . "\n\n";

// Delete bootstrap cache files manually
echo "5. Deleting bootstrap cache files...\n";
$cacheFiles = [
    'bootstrap/cache/config.php',
    'bootstrap/cache/routes-v7.php',
    'bootstrap/cache/services.php',
    'bootstrap/cache/packages.php',
];

foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "✓ Deleted: $file\n";
        } else {
            echo "✗ Failed to delete: $file\n";
        }
    } else {
        echo "- Not found: $file\n";
    }
}

echo "\n6. Checking current config values...\n";
exec('php artisan tinker --execute="echo \'GROK_VISION_MODEL: \' . env(\'GROK_VISION_MODEL\') . PHP_EOL;"', $output5);
echo implode("\n", $output5) . "\n";

echo "\n</pre>";
echo "<h2 style='color: green;'>✓ Cache clearing completed!</h2>";
echo "<p><strong>IMPORTANT:</strong> Delete this file now for security!</p>";
echo "<p>Test your API key again in the admin panel.</p>";
