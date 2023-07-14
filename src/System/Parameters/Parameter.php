<?php

namespace VAF\WP\Framework\System\Parameters;

class Parameter
{
    public function __construct(
        private readonly string $name,
        private readonly string $type,
        private readonly bool $isOptional,
        private readonly mixed $default
    ) {
    }
}
