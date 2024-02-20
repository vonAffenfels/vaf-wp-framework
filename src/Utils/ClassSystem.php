<?php

namespace VAF\WP\Framework\Utils;

use ReflectionClass;

class ClassSystem
{
    private function __construct()
    {
    }

    public static function isExtendsOrImplements(string $search, string $classname): bool
    {
        $class = new ReflectionClass($classname);
        do {
            $name = $class->getName();
            if ($search === $name) {
                return true;
            }

            $interfaces = $class->getInterfaceNames();
            if (in_array($search, $interfaces)) {
                return true;
            }

            $class = $class->getParentClass();
        } while (false !== $class);

        return false;
    }
}