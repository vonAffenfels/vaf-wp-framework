<?php

namespace VAF\WP\Framework;

use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use VAF\WP\Framework\Kernel\Kernel;
use VAF\WP\Framework\Kernel\ThemeKernel;

abstract class Theme extends BaseWordpress
{
    /**
     * @throws Exception
     */
    final public static function initTheme(bool $debug = false): Theme
    {
        $theme = wp_get_theme();
        $theme = new static(
            $theme->get('Name'),
            $theme->get_stylesheet_directory(),
            $theme->get_stylesheet_directory_uri(),
            $debug
        );
        $theme->kernel->boot();

        return $theme;
    }

    public function configureContainer(ContainerBuilder $builder, ContainerConfigurator $configurator): void
    {
        // Load parent theme configuration
        $theme = wp_get_theme();
        $parent = $theme->parent();
        if ($parent) {
            $configDir = trailingslashit($parent->get_stylesheet_directory()) . 'config';
            if (is_file($configDir . '/services.yaml')) {
                $configurator->import($configDir . '/services.yaml');
            } elseif (is_file($configDir . '/services.php')) {
                $configurator->import($configDir . '/services.php');
            }
        }
    }


    final protected function createKernel(): Kernel
    {
        $namespace = substr(static::class, 0, strrpos(static::class, '\\'));
        return new ThemeKernel($this->getPath(), $this->getDebug(), $namespace, $this);
    }
}
