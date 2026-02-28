<?php

/**
 * Force Environment Variables
 * 
 * This file ensures the correct database is used by overriding
 * system environment variables BEFORE Laravel bootstraps.
 * 
 * Include this at the very top of public/index.php
 */

// Force the correct database from .env file
$envFile = __DIR__ . '/../.env';

if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    
    // Parse .env file and extract DB_DATABASE
    if (preg_match('/^DB_DATABASE=(.*)$/m', $envContent, $matches)) {
        $dbDatabase = trim($matches[1]);
        
        // Force this value into environment
        putenv("DB_DATABASE={$dbDatabase}");
        $_ENV['DB_DATABASE'] = $dbDatabase;
        $_SERVER['DB_DATABASE'] = $dbDatabase;
    }
    
    // Also ensure DB_CONNECTION is set
    if (preg_match('/^DB_CONNECTION=(.*)$/m', $envContent, $matches)) {
        $dbConnection = trim($matches[1]);
        putenv("DB_CONNECTION={$dbConnection}");
        $_ENV['DB_CONNECTION'] = $dbConnection;
        $_SERVER['DB_CONNECTION'] = $dbConnection;
    }
    
    // DB_HOST
    if (preg_match('/^DB_HOST=(.*)$/m', $envContent, $matches)) {
        $dbHost = trim($matches[1]);
        putenv("DB_HOST={$dbHost}");
        $_ENV['DB_HOST'] = $dbHost;
        $_SERVER['DB_HOST'] = $dbHost;
    }
    
    // DB_PORT
    if (preg_match('/^DB_PORT=(.*)$/m', $envContent, $matches)) {
        $dbPort = trim($matches[1]);
        putenv("DB_PORT={$dbPort}");
        $_ENV['DB_PORT'] = $dbPort;
        $_SERVER['DB_PORT'] = $dbPort;
    }
    
    // DB_USERNAME
    if (preg_match('/^DB_USERNAME=(.*)$/m', $envContent, $matches)) {
        $dbUsername = trim($matches[1]);
        putenv("DB_USERNAME={$dbUsername}");
        $_ENV['DB_USERNAME'] = $dbUsername;
        $_SERVER['DB_USERNAME'] = $dbUsername;
    }
    
    // DB_PASSWORD
    if (preg_match('/^DB_PASSWORD=(.*)$/m', $envContent, $matches)) {
        $dbPassword = trim($matches[1]);
        putenv("DB_PASSWORD={$dbPassword}");
        $_ENV['DB_PASSWORD'] = $dbPassword;
        $_SERVER['DB_PASSWORD'] = $dbPassword;
    }
}
