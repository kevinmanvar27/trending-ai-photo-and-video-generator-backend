<?php

/**
 * Web Test Page - Check Database Connection
 * Access via: http://localhost/trends/test-db-connection.php
 */

// Force environment variables
require __DIR__.'/../bootstrap/force_env.php';

// Load Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        .test { margin: 20px 0; padding: 15px; border-left: 4px solid #2196F3; background: #E3F2FD; }
        .success { border-left-color: #4CAF50; background: #E8F5E9; }
        .error { border-left-color: #f44336; background: #FFEBEE; }
        .label { font-weight: bold; color: #555; }
        .value { color: #000; font-family: monospace; background: #f9f9f9; padding: 2px 6px; border-radius: 3px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #4CAF50; color: white; }
        tr:hover { background: #f5f5f5; }
        .icon { font-size: 20px; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Database Connection Test</h1>
        
        <?php
        try {
            // Test 1: Environment Variable
            $envDb = env('DB_DATABASE');
            $envClass = ($envDb === 'trends') ? 'success' : 'error';
            echo "<div class='test {$envClass}'>";
            echo "<span class='icon'>" . (($envDb === 'trends') ? '✅' : '❌') . "</span>";
            echo "<span class='label'>Environment Variable (env):</span> ";
            echo "<span class='value'>{$envDb}</span>";
            echo "</div>";
            
            // Test 2: Config
            $configDb = config('database.connections.mysql.database');
            $configClass = ($configDb === 'trends') ? 'success' : 'error';
            echo "<div class='test {$configClass}'>";
            echo "<span class='icon'>" . (($configDb === 'trends') ? '✅' : '❌') . "</span>";
            echo "<span class='label'>Laravel Config:</span> ";
            echo "<span class='value'>{$configDb}</span>";
            echo "</div>";
            
            // Test 3: Actual Connection
            $actualDb = DB::connection()->getDatabaseName();
            $actualClass = ($actualDb === 'trends') ? 'success' : 'error';
            echo "<div class='test {$actualClass}'>";
            echo "<span class='icon'>" . (($actualDb === 'trends') ? '✅' : '❌') . "</span>";
            echo "<span class='label'>Actual Database Connected:</span> ";
            echo "<span class='value'>{$actualDb}</span>";
            echo "</div>";
            
            // Test 4: Settings Count
            $settingsCount = App\Models\Setting::count();
            echo "<div class='test success'>";
            echo "<span class='icon'>✅</span>";
            echo "<span class='label'>Settings in Database:</span> ";
            echo "<span class='value'>{$settingsCount} records</span>";
            echo "</div>";
            
            // Test 5: Show Settings
            echo "<h2>📋 Settings from '{$actualDb}' Database</h2>";
            $settings = App\Models\Setting::orderBy('group')->orderBy('key')->get();
            
            if ($settings->count() > 0) {
                echo "<table>";
                echo "<tr><th>#</th><th>Key</th><th>Value</th><th>Type</th><th>Group</th></tr>";
                foreach ($settings as $index => $setting) {
                    $value = $setting->value ?: '<em>(empty)</em>';
                    if (strlen($value) > 60) {
                        $value = substr($value, 0, 57) . '...';
                    }
                    echo "<tr>";
                    echo "<td>" . ($index + 1) . "</td>";
                    echo "<td><strong>{$setting->key}</strong></td>";
                    echo "<td>{$value}</td>";
                    echo "<td><span style='background:#e3f2fd;padding:2px 8px;border-radius:3px;font-size:11px;'>{$setting->type}</span></td>";
                    echo "<td><span style='background:#fff3e0;padding:2px 8px;border-radius:3px;font-size:11px;'>{$setting->group}</span></td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            
            // Final Status
            if ($actualDb === 'trends') {
                echo "<div style='margin-top:30px;padding:20px;background:#4CAF50;color:white;border-radius:8px;text-align:center;'>";
                echo "<h2 style='margin:0;color:white;border:none;'>🎉 SUCCESS!</h2>";
                echo "<p style='margin:10px 0 0 0;'>Your application is correctly connected to the <strong>trends</strong> database!</p>";
                echo "</div>";
            } else {
                echo "<div style='margin-top:30px;padding:20px;background:#f44336;color:white;border-radius:8px;text-align:center;'>";
                echo "<h2 style='margin:0;color:white;border:none;'>⚠️ WARNING!</h2>";
                echo "<p style='margin:10px 0 0 0;'>Connected to <strong>{$actualDb}</strong> database instead of <strong>trends</strong>!</p>";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='test error'>";
            echo "<span class='icon'>❌</span>";
            echo "<span class='label'>Error:</span> ";
            echo "<span class='value'>" . htmlspecialchars($e->getMessage()) . "</span>";
            echo "</div>";
        }
        ?>
        
        <div style="margin-top:30px;padding:15px;background:#f9f9f9;border-radius:5px;font-size:13px;">
            <strong>ℹ️ Info:</strong> This test page uses the same bootstrap as your application.<br>
            If this shows "trends" database, your admin panel will also use "trends" database.
        </div>
        
        <div style="margin-top:15px;text-align:center;">
            <a href="/trends/admin/settings" style="display:inline-block;padding:12px 24px;background:#2196F3;color:white;text-decoration:none;border-radius:5px;font-weight:bold;">
                Go to Admin Settings →
            </a>
        </div>
    </div>
</body>
</html>
