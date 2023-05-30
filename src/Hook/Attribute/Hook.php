<?php

namespace VAF\WP\Library\Hook\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Hook
{
    public function __construct(
        public readonly string $hook,
        public readonly int $priority = 10
    ) {
    }
}
