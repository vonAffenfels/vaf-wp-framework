<?php

namespace VAF\WP\Framework\AdminPages\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class PageTab
{
    public function __construct(
        public readonly string $slug,
        public readonly string $title
    ) {
    }
}
