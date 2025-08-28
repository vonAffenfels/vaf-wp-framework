<?php

namespace VAF\WP\Framework\Traits;

/**
 * Prevents automatic container cache creation during normal bootup.
 * Container cache will only be created via explicit composer build-container command.
 * This ensures development environments don't accidentally create cache directories.
 *
 * Usage:
 * ```php
 * use VAF\WP\Framework\Plugin;
 * use VAF\WP\Framework\Traits\OnlyCreateCacheExplicitlyOnBuild;
 *
 * class MyPlugin extends Plugin {
 *     use OnlyCreateCacheExplicitlyOnBuild;
 * }
 * ```
 */
trait OnlyCreateCacheExplicitlyOnBuild
{
    /**
     * Prevents automatic container cache creation during normal bootup.
     *
     * @return bool
     */
    protected static function preventAutomaticContainerCache(): bool
    {
        return true;
    }
}
