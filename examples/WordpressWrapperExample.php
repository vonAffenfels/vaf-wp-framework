<?php

/**
 * Example demonstrating how to use the Wordpress wrapper class
 * for better testability
 */

use VAF\WP\Framework\Wordpress\Wordpress;

class PluginManager
{
    public function registerPlugin(string $file): array
    {
        // Before: Direct WordPress function calls (not testable)
        // $pluginUrl = plugin_dir_url($file);
        // $pluginPath = plugin_dir_path($file);
        // $pluginName = dirname(plugin_basename($file));
        
        // After: Using Wordpress wrapper (testable)
        $pluginUrl = Wordpress::plugin_dir_url($file);
        $pluginPath = Wordpress::plugin_dir_path($file);
        $pluginName = dirname(Wordpress::plugin_basename($file));

        return [
            'url' => $pluginUrl,
            'path' => $pluginPath,
            'name' => $pluginName
        ];
    }

    public function addCustomAction(string $pluginName): void
    {
        // Before: Direct WordPress function call (not testable)
        // add_action('init', function() use ($pluginName) {
        //     // Plugin initialization logic
        // });

        // After: Using Wordpress wrapper (testable)
        Wordpress::add_action('init', function() use ($pluginName) {
            // Plugin initialization logic
        });
    }

    public function isAdminPage(): bool
    {
        // Before: Direct WordPress function call (not testable)
        // return is_admin();

        // After: Using Wordpress wrapper (testable)
        return Wordpress::is_admin();
    }

    public function getCurrentUser()
    {
        // Before: Direct WordPress function call (not testable)
        // return wp_get_current_user();

        // After: Using Wordpress wrapper (testable)
        return Wordpress::wp_get_current_user();
    }
}