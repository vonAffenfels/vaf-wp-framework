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
        // Set debug to true to always renew the container
        $obj = new static('__BUILD__', getcwd(), '__BUILD__', true);
        $obj->getContainer();
    }

    final protected function createKernel(): Kernel
    {
        $namespace = substr(static::class, 0, strrpos(static::class, '\\'));
        return new PluginKernel($this->getPath(), $this->getDebug(), $namespace, $this);
    }

    private function registerPluginApi(): void
    {
        /**
         * Do NOT typehint return with this. Plugin can be of a scoped plugin type because of php-scoper
         * @var ?Plugin $return
         * @returns ?Plugin
         */
        add_action('vaf-get-plugin', function ($return, string $plugin) {
            if ($plugin === $this->getName()) {
                $return = $this;
            }

            return $return;
        }, 10, 2);
    }
}
