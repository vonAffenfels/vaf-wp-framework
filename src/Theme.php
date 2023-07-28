<?php

namespace VAF\WP\Framework;

use Exception;
use VAF\WP\Framework\Kernel\Kernel;
use VAF\WP\Framework\Kernel\ThemeKernel;

abstract class Theme extends BaseWordpress
{
    /**
     * @throws Exception
     */
    final public static function registerTheme(string $name, string $path, bool $debug = false): Theme
    {
        $theme = new static($name, $path, '', $debug);
        $theme->kernel->boot();

        return $theme;
    }

    final protected function createKernel(): Kernel
    {
        $namespace = substr(static::class, 0, strrpos(static::class, '\\'));
        return new ThemeKernel($this->getPath(), $this->getDebug(), $namespace, $this);
    }
}
