<?php

namespace VAF\WP\Framework\Setting\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AsSettingContainer
{
    public function __construct(
        public readonly string $name,
        public readonly mixed $default = null
    ) {
    }
}
