<?php

namespace VAF\WP\Framework;

use Exception;
use VAF\WP\Framework\Kernel\Kernel;
use VAF\WP\Framework\Kernel\PluginKernel;

abstract class Plugin extends BaseWordpress
{
    /**
     * Registers a plugin and boots it
     *
     * @param string $file Plugin file
     * @param bool $debug True if debug mode is enabled
     * @noinspection PhpUnused
     * @throws Exception
     */
    final public static function registerPlugin(string $file, bool $debug = false): Plugin
    {
        $pluginUrl = plugin_dir_url($file);
        $pluginPath = plugin_dir_path($file);
        $pluginName = dirname(plugin_basename($file));

        $plugin = new static($pluginName, $pluginPath, $pluginUrl, $debug);
        $plugin->kernel->boot();

        $plugin->registerPluginApi();

        return $plugin;
    }

    /**
     * @throws Exception
     */
    final public static function buildContainer(): void
    {
        if (!self::pluginVendorExists())
        {
            die('Please run "composer install" to ensure all dependencies are installed.');
        }

        // Set debug to true to always renew the container
        $obj = new static('__BUILD__', getcwd(), '__BUILD__', true);
        $obj->kernel->forceContainerCacheUpdate();
    }

    final protected function createKernel(): Kernel
    {
        $namespace = substr(static::class, 0, strrpos(static::class, '\\'));
        return new PluginKernel($this->getPath(), $this->getDebug(), $namespace, $this);
    }

    private function registerPluginApi(): void
    {
        add_action('get-plugin/' . $this->getName(), function () {
            return $this;
        }, 10, 0);
    }

    private static function pluginVendorExists(): bool
    {
        $relVendorLocation = __DIR__ . '/../../../../vendor';
        return file_exists($relVendorLocation) && is_dir($relVendorLocation);
    }
}
