<?php

use VAF\WP\Framework\Plugin;
use VAF\WP\Framework\Theme;
use VAF\WP\Framework\Traits\OnlyCreateCacheExplicitlyOnBuild;

// Test Plugin without trait
class TestPluginWithoutTrait extends Plugin
{
    public static function testPreventAutomaticContainerCache(): bool
    {
        return static::preventAutomaticContainerCache();
    }
}

// Test Plugin with trait
class TestPluginWithTrait extends Plugin
{
    use OnlyCreateCacheExplicitlyOnBuild;

    public static function testPreventAutomaticContainerCache(): bool
    {
        return static::preventAutomaticContainerCache();
    }
}

// Test Theme without trait
class TestThemeWithoutTrait extends Theme
{
    public static function testPreventAutomaticContainerCache(): bool
    {
        return static::preventAutomaticContainerCache();
    }
}

// Test Theme with trait
class TestThemeWithTrait extends Theme
{
    use OnlyCreateCacheExplicitlyOnBuild;

    public static function testPreventAutomaticContainerCache(): bool
    {
        return static::preventAutomaticContainerCache();
    }
}

it('should return false by default for plugin without trait', function () {
    expect(TestPluginWithoutTrait::testPreventAutomaticContainerCache())->toBeFalse();
});

it('should return true for plugin with trait', function () {
    expect(TestPluginWithTrait::testPreventAutomaticContainerCache())->toBeTrue();
});

it('should return false by default for theme without trait', function () {
    expect(TestThemeWithoutTrait::testPreventAutomaticContainerCache())->toBeFalse();
});

it('should return true for theme with trait', function () {
    expect(TestThemeWithTrait::testPreventAutomaticContainerCache())->toBeTrue();
});
