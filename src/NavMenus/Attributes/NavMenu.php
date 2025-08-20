<?php

namespace VAF\WP\Framework\NavMenus\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class NavMenu
{
    public function __construct(
        public readonly string $slug,
        public readonly string $description
    ) {
    }
}
