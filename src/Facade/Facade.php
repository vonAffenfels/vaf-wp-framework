<?php

namespace VAF\WP\Framework\Facade;

use LogicException;
use VAF\WP\Framework\Kernel\WordpressKernel;

abstract class Facade
{
    protected static ?WordpressKernel $kernel = null;
    protected static array $resolvedInstances = [];

    public static function setKernel(?WordpressKernel $kernel): void
    {
        static::$kernel = $kernel;
    }

    public static function clearResolvedInstances(): void
    {
        static::$resolvedInstances = [];
    }

    public static function clearResolvedInstance(string $name): void
    {
        unset(static::$resolvedInstances[$name]);
    }

    protected static function getFacadeRoot()
    {
        $class = static::getFacadeAccessor();

        if (!isset(static::$resolvedInstances[$class])) {
            if (!static::$kernel) {
                throw new LogicException('Facade kernel has not been set.');
            }

            static::$resolvedInstances[$class] = static::$kernel->getContainer()->get($class);
        }

        return static::$resolvedInstances[$class];
    }

    protected static function getFacadeAccessor(): string
    {
        $reflection = new \ReflectionClass(static::class);
        $attributes = $reflection->getAttributes(Attribute\AsFacade::class);

        if (!empty($attributes)) {
            $attribute = $attributes[0]->newInstance();
            return $attribute->facadeAccessor;
        }

        throw new LogicException('Facade does not implement getFacadeAccessor method.');
    }

    public static function __callStatic(string $method, array $args)
    {
        $instance = static::getFacadeRoot();

        if (!$instance) {
            throw new LogicException('A facade root has not been set.');
        }

        return $instance->$method(...$args);
    }
}
