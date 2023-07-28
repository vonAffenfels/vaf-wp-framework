<?php

namespace VAF\WP\Framework\Hook\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Hook
{
    public function __construct(
        public readonly string $hook,
        public readonly int $priority = 10
    ) {
    }
}
