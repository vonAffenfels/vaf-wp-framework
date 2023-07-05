<?php

namespace VAF\WP\Framework\Menu\Attribute;

use Attribute;
use VAF\WP\Framework\Utils\Capabilities;
use VAF\WP\Framework\Utils\Dashicons;

#[Attribute(Attribute::TARGET_METHOD)]
class MenuItem
{
    public function __construct(
        public readonly string $menuTitle,
        public readonly Capabilities $capability,
        public readonly string $slug,
        public Dashicons|string $icon = '',
        public ?int $position = null,
        public string $parent = '',
        public string $subMenuTitle = ''
    ) {
    }
}
