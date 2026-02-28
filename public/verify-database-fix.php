<?php
/**
 * Comprehensive Database Connection Verification
 * 
 * This script verifies that:
 * 1. The application connects to the correct database (trends)
 * 2. Settings are being read from the trends database
 * 3. The force_env.php bootstrap is working
 * 4. Cache is properly cleared
 */

// Load Laravel bootstrap with our force_env fix
require __DIR__.'/../bootstrap/force_env.php';
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Start the application
$app->boot();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Fix Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        .warning {
            color: #ffc107;
            font-weight: bold;
        }
        .info {
            color: #17a2b8;
        }
        h1 {
            color: #333;
        }
        h2 {
            color: #666;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .code {
            background: #f4f4f4;
            padding: 10px;
            border-left: 4px solid #007bff;
            font-family: monospace;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h1>🔍 Database Fix Verification Report</h1>
    
    <?php
    // Test 1: Check current database connection
    echo '<div class="test-section">';
    echo '<h2>Test 1: Database Connection</h2>';
    
    try {
        $currentDb = DB::connection()->getDatabaseName();
        
        if ($currentDb === 'trends') {
            echo '<p class="success">✅ SUCCESS: Connected to correct database: ' . $currentDb . '</p>';
        } else {
            echo '<p class="error">❌ FAILED: Connected to wrong database: ' . $currentDb . '</p>';
            echo '<p class="warning">Expected: trends</p>';
        }
    } catch (Exception $e) {
        echo '<p class="error">❌ ERROR: ' . $e->getMessage() . '</p>';
    }
    
    echo '</div>';
    
    // Test 2: Check environment variables
    echo '<div class="test-section">';
    echo '<h2>Test 2: Environment Variables</h2>';
    echo '<table>';
    echo '<tr><th>Variable</th><th>Value</th><th>Status</th></tr>';
    
    $envVars = [
        'DB_CONNECTION' => env('DB_CONNECTION'),
        'DB_HOST' => env('DB_HOST'),
        'DB_PORT' => env('DB_PORT'),
        'DB_DATABASE' => env('DB_DATABASE'),
        'DB_USERNAME' => env('DB_USERNAME'),
    ];
    
    foreach ($envVars as $key => $value) {
        $status = '';
        if ($key === 'DB_DATABASE') {
            $status = ($value === 'trends') 
                ? '<span class="success">✅ Correct</span>' 
                : '<span class="error">❌ Wrong (should be "trends")</span>';
        } else {
            $status = '<span class="info">ℹ️ Info</span>';
        }
        
        echo "<tr><td><strong>{$key}</strong></td><td>{$value}</td><td>{$status}</td></tr>";
    }
    
    echo '</table>';
    echo '</div>';
    
    // Test 3: Check settings table
    echo '<div class="test-section">';
    echo '<h2>Test 3: Settings Table Access</h2>';
    
    try {
        // Clear cache first
        \Illuminate\Support\Facades\Cache::flush();
        echo '<p class="info">ℹ️ Cache cleared</p>';
        
        $settingsCount = DB::table('settings')->count();
        echo '<p class="success">✅ Settings table accessible: ' . $settingsCount . ' records found</p>';
        
        // Get a few sample settings
        $sampleSettings = DB::table('settings')->limit(5)->get();
        
        if ($sampleSettings->count() > 0) {
            echo '<h3>Sample Settings:</h3>';
            echo '<table>';
            echo '<tr><th>Key</th><th>Value</th><th>Group</th></tr>';
            
            foreach ($sampleSettings as $setting) {
                $displayValue = strlen($setting->value) > 50 
                    ? substr($setting->value, 0, 50) . '...' 
                    : $setting->value;
                echo "<tr><td>{$setting->key}</td><td>{$displayValue}</td><td>{$setting->group}</td></tr>";
            }
            
            echo '</table>';
        }
        
    } catch (Exception $e) {
        echo '<p class="error">❌ ERROR: ' . $e->getMessage() . '</p>';
    }
    
    echo '</div>';
    
    // Test 4: Check Setting model
    echo '<div class="test-section">';
    echo '<h2>Test 4: Setting Model Test</h2>';
    
    try {
        $siteTitle = \App\Models\Setting::get('site_title', 'NOT FOUND');
        $siteName = \App\Models\Setting::get('site_name', 'NOT FOUND');
        
        echo '<table>';
        echo '<tr><th>Setting Key</th><th>Value</th></tr>';
        echo "<tr><td>site_title</td><td>{$siteTitle}</td></tr>";
        echo "<tr><td>site_name</td><td>{$siteName}</td></tr>";
        echo '</table>';
        
        if ($siteTitle !== 'NOT FOUND' || $siteName !== 'NOT FOUND') {
            echo '<p class="success">✅ Setting model working correctly</p>';
        } else {
            echo '<p class="warning">⚠️ WARNING: Settings not found in database</p>';
        }
        
    } catch (Exception $e) {
        echo '<p class="error">❌ ERROR: ' . $e->getMessage() . '</p>';
    }
    
    echo '</div>';
    
    // Test 5: Check force_env.php execution
    echo '<div class="test-section">';
    echo '<h2>Test 5: Bootstrap File Check</h2>';
    
    $forceEnvPath = __DIR__.'/../bootstrap/force_env.php';
    
    if (file_exists($forceEnvPath)) {
        echo '<p class="success">✅ force_env.php exists</p>';
        echo '<div class="code">Location: ' . $forceEnvPath . '</div>';
        
        // Check if it's being loaded
        $indexPath = __DIR__.'/index.php';
        $indexContent = file_get_contents($indexPath);
        
        if (strpos($indexContent, "require __DIR__.'/../bootstrap/force_env.php';") !== false) {
            echo '<p class="success">✅ force_env.php is loaded in public/index.php</p>';
        } else {
            echo '<p class="error">❌ force_env.php is NOT loaded in public/index.php</p>';
        }
        
    } else {
        echo '<p class="error">❌ force_env.php does NOT exist</p>';
    }
    
    echo '</div>';
    
    // Final verdict
    echo '<div class="test-section">';
    echo '<h2>📊 Final Verdict</h2>';
    
    $allPassed = true;
    $issues = [];
    
    try {
        if (DB::connection()->getDatabaseName() !== 'trends') {
            $allPassed = false;
            $issues[] = 'Not connected to trends database';
        }
        
        if (env('DB_DATABASE') !== 'trends') {
            $allPassed = false;
            $issues[] = 'DB_DATABASE environment variable is not set to "trends"';
        }
        
        if (DB::table('settings')->count() === 0) {
            $allPassed = false;
            $issues[] = 'Settings table is empty';
        }
        
    } catch (Exception $e) {
        $allPassed = false;
        $issues[] = 'Database connection error: ' . $e->getMessage();
    }
    
    if ($allPassed) {
        echo '<p class="success" style="font-size: 1.2em;">🎉 ALL TESTS PASSED!</p>';
        echo '<p>Your application is now correctly connected to the <strong>trends</strong> database.</p>';
        echo '<p class="info">Next step: Test the admin settings page to ensure saves work correctly.</p>';
    } else {
        echo '<p class="error" style="font-size: 1.2em;">❌ SOME TESTS FAILED</p>';
        echo '<p>Issues found:</p>';
        echo '<ul>';
        foreach ($issues as $issue) {
            echo '<li class="error">' . $issue . '</li>';
        }
        echo '</ul>';
        echo '<p class="warning">Please check the documentation or contact support.</p>';
    }
    
    echo '</div>';
    
    // Action items
    echo '<div class="test-section">';
    echo '<h2>📝 Next Steps</h2>';
    
    if ($allPassed) {
        echo '<ol>';
        echo '<li>✅ Database connection is working</li>';
        echo '<li>🔄 Test the admin settings page: <a href="/trends/admin/settings">Go to Settings</a></li>';
        echo '<li>💾 Try updating a setting and verify it saves to the trends database</li>';
        echo '<li>🗑️ You can now safely delete this verification file if everything works</li>';
        echo '</ol>';
    } else {
        echo '<ol>';
        echo '<li>🔄 <strong>Restart Apache</strong> in XAMPP Control Panel</li>';
        echo '<li>🔄 Clear Laravel cache: <code>php artisan cache:clear</code></li>';
        echo '<li>🔄 Clear config cache: <code>php artisan config:clear</code></li>';
        echo '<li>🔄 Refresh this page after restarting Apache</li>';
        echo '</ol>';
    }
    
    echo '</div>';
    ?>
    
    <div class="test-section" style="background: #e7f3ff; border-left: 4px solid #007bff;">
        <p><strong>ℹ️ Note:</strong> If you just restarted Apache, make sure to:</p>
        <ol>
            <li>Wait a few seconds for Apache to fully restart</li>
            <li>Clear your browser cache (Cmd+Shift+R on macOS)</li>
            <li>Run: <code>php artisan cache:clear && php artisan config:clear</code></li>
        </ol>
    </div>
    
</body>
</html>
