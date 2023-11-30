<?php

namespace VAF\WP\Framework\Metabox\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Metabox
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string|array|null $screen = null,
        public readonly string $context = 'advanced',
        public readonly int $priority = 10
    ) {
    }
}
