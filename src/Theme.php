<?php

namespace VAF\WP\Framework;

use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use VAF\WP\Framework\Kernel\Kernel;
use VAF\WP\Framework\Kernel\ThemeKernel;
use VAF\WP\Framework\Utils\ThemeSearchMode;

abstract class Theme extends BaseWordpress
{
    private bool $hasParent = false;
    private string $parentPath = '';
    private string $parentUrl = '';

    private array $pathCache = [];

    /**
     * @throws Exception
     */
    final public static function initTheme(bool $debug = false): Theme
    {
        $theme = new static($debug);
        $theme->kernel->boot();

        return $theme;
    }

    public function __construct(bool $debug = false)
    {
        $theme = wp_get_theme();

        parent::__construct(
            $theme->get('Name'),
            $theme->get_stylesheet_directory(),
            $theme->get_stylesheet_directory_uri(),
            $debug
        );

        $parent = $theme->parent();
        if (false !== $parent) {
            $this->hasParent = true;
            $this->parentPath = $parent->get_stylesheet_directory();
            $this->parentUrl = $parent->get_stylesheet_directory_uri();
        }
    }

    public function hasParent(): bool
    {
        return $this->hasParent;
    }

    public function getParentPath(): string
    {
        return $this->parentPath;
    }

    public function getParentUrl(): string
    {
        return $this->parentUrl;
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

    public function getUrlForFile(string $file): string|false
    {
        $path = $this->getPathForFile($file, ThemeSearchMode::CURRENT_ONLY);
        if (false !== $path) {
            return trailingslashit($this->getUrl()) . $file;
        }

        if ($this->hasParent()) {
            $path = $this->getPathForFile($file, ThemeSearchMode::PARENT_ONLY);
            if (false !== $path) {
                return trailingslashit($this->getParentUrl()) . $file;
            }
        }

        return false;
    }

    public function getAssetUrl(string $asset): string
    {
        return $this->getUrlForFile('public/' . $asset) ?: '';
    }

    public function getPathForFile(string $file, ThemeSearchMode $searchMode = ThemeSearchMode::ALL): string|false
    {
        if (str_starts_with($file, '/')) {
            // Absolute path
            return $file;
        }

        if (isset($this->pathCache[$searchMode->value . '_' . $file])) {
            return $this->pathCache[$file];
        }

        if ($searchMode === ThemeSearchMode::ALL && $this->hasParent()) {
            $pathsToCheck = [
                $this->getPath(),
                $this->getParentPath()
            ];
        } elseif ($searchMode === ThemeSearchMode::ALL) {
            $pathsToCheck = [
                $this->getPath()
            ];
        } elseif ($searchMode === ThemeSearchMode::CURRENT_ONLY) {
            $pathsToCheck = [
                $this->getPath()
            ];
        } elseif ($searchMode === ThemeSearchMode::PARENT_ONLY && $this->hasParent()) {
            $pathsToCheck = [
                $this->getParentPath()
            ];
        } else {
            return false;
        }

        $result = false;
        foreach ($pathsToCheck as $pathToCheck) {
            $path = trailingslashit($pathToCheck) . $file;
            if (is_readable($path)) {
                $result = realpath($path);
                break;
            }
        }

        $this->pathCache[$searchMode->value . '_' . $file] = $result;
        return $result;
    }
}
