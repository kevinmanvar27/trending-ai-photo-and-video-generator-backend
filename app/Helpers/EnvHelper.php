<?php

namespace App\Helpers;

class EnvHelper
{
    /**
     * Update or create an environment variable in the .env file
     *
     * @param string $key
     * @param string|null $value
     * @return bool
     */
    public static function updateEnv($key, $value)
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return false;
        }

        $envContent = file_get_contents($envPath);
        
        // Escape special characters in value
        $value = self::escapeEnvValue($value);
        
        // Check if key exists
        $pattern = "/^{$key}=.*/m";
        
        if (preg_match($pattern, $envContent)) {
            // Update existing key
            $envContent = preg_replace(
                $pattern,
                "{$key}={$value}",
                $envContent
            );
        } else {
            // Add new key at the end
            $envContent .= "\n{$key}={$value}";
        }

        // Write back to file
        return file_put_contents($envPath, $envContent) !== false;
    }

    /**
     * Update multiple environment variables at once
     *
     * @param array $data
     * @return bool
     */
    public static function updateMultipleEnv(array $data)
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return false;
        }

        $envContent = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            $value = self::escapeEnvValue($value);
            $pattern = "/^{$key}=.*/m";
            
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace(
                    $pattern,
                    "{$key}={$value}",
                    $envContent
                );
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        return file_put_contents($envPath, $envContent) !== false;
    }

    /**
     * Escape environment variable value
     *
     * @param string|null $value
     * @return string
     */
    private static function escapeEnvValue($value)
    {
        if ($value === null) {
            return '';
        }

        // If value contains spaces, quotes, or special characters, wrap in quotes
        if (preg_match('/\s|#|"/', $value)) {
            // Escape existing quotes
            $value = str_replace('"', '\"', $value);
            return "\"{$value}\"";
        }

        return $value;
    }

    /**
     * Get an environment variable value
     *
     * @param string $key
     * @return string|null
     */
    public static function getEnv($key)
    {
        return env($key);
    }
}
