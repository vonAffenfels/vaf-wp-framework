<?php

namespace VAF\WP\Framework\Wordpress;

class Wordpress
{
    private static ?object $mock = null;

    public static function fake(?object $mock = null): void
    {
        if ($mock === null) {
            $mock = \Mockery::spy();
        }
        self::$mock = $mock;
    }

    public static function resetFake(): void
    {
        self::$mock = null;
    }

    public static function mock(): ?object
    {
        return self::$mock;
    }

    public static function __callStatic(string $name, array $arguments): mixed
    {
        if (self::$mock !== null) {
            return self::$mock->$name(...$arguments);
        }

        // When no mock is set, forward to global WordPress functions
        if (function_exists($name)) {
            return call_user_func_array($name, $arguments);
        }

        throw new \BadMethodCallException("WordPress function '$name' does not exist");
    }
}
